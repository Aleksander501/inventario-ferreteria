<?php

class ControladorUsuarios
{
    /* =========================
     *  Compatibilidad con hash legacy (crypt)
     * ========================= */
    private const LEGACY_CRYPT_SALT = '$2a$07$amsr54ahjppf45sd87a5a4$';

    /* =========================
     *  Mostrar usuarios
     * ========================= */
    static public function ctrMostrarUsuarios($item, $valor)
    {
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);
        return $respuesta;
    }

    /* =========================
     *  Ingreso (LOGIN)
     *  - Primero intenta password_verify() (hash moderno)
     *  - Si no, intenta legacy crypt() con tu SALT
     *  - Si valida con legacy, migra a password_hash()
     * ========================= */
    static public function ctrIngresoUsuarios()
    {
        if (isset($_POST['IngCorreo'])) {

            $tabla   = "usuarios";
            $item    = "correo";             // login por correo
            $valor   = $_POST['IngCorreo'];
            $passInp = $_POST['IngPassword'];

            $respuesta = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);

            if ($respuesta && isset($respuesta["password"])) {

                $stored = $respuesta["password"];
                $ok = false;

                // 1) Intento moderno: password_hash()
                if (password_get_info($stored)['algo'] !== 0) {
                    $ok = password_verify($passInp, $stored);

                    // (Opcional) rehash si el coste cambió
                    if ($ok && password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                        $nuevo = password_hash($passInp, PASSWORD_DEFAULT);
                        if (method_exists('ModeloUsuarios','mdlActualizarPasswordHash')) {
                            ModeloUsuarios::mdlActualizarPasswordHash((int)$respuesta["id_usuario"], $nuevo);
                        }
                    }

                } else {
                    // 2) Compatibilidad legacy con tu SALT y crypt()
                    $encriptarLegacy = crypt($passInp, self::LEGACY_CRYPT_SALT);
                    if (hash_equals($encriptarLegacy, $stored)) {
                        $ok = true;
                        // Migra a hash moderno para futuros logins
                        $nuevo = password_hash($passInp, PASSWORD_DEFAULT);
                        if (method_exists('ModeloUsuarios','mdlActualizarPasswordHash')) {
                            ModeloUsuarios::mdlActualizarPasswordHash((int)$respuesta["id_usuario"], $nuevo);
                        }
                    }
                }

                if ($ok) {
                    // Sesión estándar
                    $_SESSION['iniciarSesion'] = "ok";
                    $_SESSION["id"]     = $respuesta["id_usuario"];
                    $_SESSION["nombre"] = $respuesta["nombre"];
                    $_SESSION["correo"] = $respuesta["correo"];
                    $_SESSION["perfil"] = $respuesta["perfil"];

                    echo '<script> window.location = "inicio"; </script>';
                    return;
                }
            }

            echo '<br><div class="alert alert-danger">Error, intente de nuevo</div>';
        }
    }

    /* ============================================
       CREAR USUARIOS
       Buenas prácticas de contraseña:
       - Validación fuerte (8+, mayúscula, número, especial)
       - Hash moderno: password_hash() (NO guardar plaintext ni crypt nuevo)
       ============================================ */
    static public function ctrCrearUsuarios()
    {
        if (isset($_POST["nuevoUsuario"])) {

            // 1) Validar formato de correo
            if (!filter_var($_POST["nuevoCorreo"], FILTER_VALIDATE_EMAIL)) {
                echo '<script>
                    swal({
                        type:"error",
                        title: "El correo ingresado no es válido",
                        showConfirmButton:true,
                        confirmButtonText:"Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "usuarios";
                        }
                    });
                </script>';
                return;
            }

            // 2) Validar política de contraseña
            $password = $_POST["nuevoPassword"];
            if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&*!?_.\-]).{8,}$/', $password)) {
                echo '<script>
                    swal({
                        type:"error",
                        title: "La contraseña no cumple los requisitos de seguridad",
                        text: "Debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial (@#$%^&*!?_.-).",
                        showConfirmButton:true,
                        confirmButtonText:"Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "usuarios";
                        }
                    });
                </script>';
                return;
            }

            // 3) Validar unicidad de correo
            $tabla = "usuarios";
            $item  = "correo";
            $valor = $_POST["nuevoCorreo"];
            $usuarioExistente = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);

            if ($usuarioExistente) {
                echo '<script>
                    swal({
                        type:"error",
                        title: "El correo ya está registrado en el sistema",
                        showConfirmButton:true,
                        confirmButtonText:"Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "usuarios";
                        }
                    });
                </script>';
                return;
            }

            // 4) Hash seguro de contraseña
            $hashSeguro = password_hash($password, PASSWORD_DEFAULT);

            $datos = array(
                "nombre"   => $_POST['nuevoNombre'],
                "usuario"  => $_POST['nuevoUsuario'],
                "correo"   => $_POST['nuevoCorreo'],
                "password" => $hashSeguro,
                "perfil"   => $_POST['nuevoPerfil']
            );

            // 5) Insertar
            $respuesta = ModeloUsuarios::mdlIngresarUsuarios($tabla, $datos);

            if ($respuesta == "ok") {

                // Buscar recien creado para auditar (por correo)
                $nuevo = ModeloUsuarios::mdlMostrarUsuarios($tabla, "correo", $_POST['nuevoCorreo']);

                /* ===== AUDITORÍA: CREATE (sin exponer password) ===== */
                if ($nuevo && function_exists('audit_log')) {
                    try {
                        $after = [
                            'id_usuario' => (int)$nuevo['id_usuario'],
                            'nombre'     => $nuevo['nombre'] ?? '',
                            'usuario'    => $nuevo['usuario'] ?? '',
                            'correo'     => $nuevo['correo'] ?? '',
                            'perfil'     => $nuevo['perfil'] ?? '',
                            // Para indicar cambio de password sin exponerla:
                            'password'   => '[set]'
                        ];
                        audit_log(
                            'Usuarios',
                            'CREATE',
                            'usuarios',
                            $after['id_usuario'],
                            "Creó usuario {$after['nombre']} ({$after['usuario']})",
                            [],
                            $after
                        );
                    } catch (Throwable $e) { error_log('AUDIT USUARIOS CREATE ERROR: '.$e->getMessage()); }
                }
                /* ==================================================== */

                echo '<script>
                    swal({
                        type:"success",
                        title: "El usuario ha sido guardado correctamente",
                        showConfirmButton:true,
                        confirmButtonText:"Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "usuarios";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    swal({
                        type:"error",
                        title: "Error al guardar el usuario",
                        showConfirmButton:true,
                        confirmButtonText:"Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "usuarios";
                        }
                    });
                </script>';
            }
        }
    }

    /* ============================================
       EDITAR USUARIOS
       - Si viene nueva contraseña: validar y hashear con password_hash()
       - Nunca guardar ni mostrar contraseñas en auditoría
       ============================================ */
    static public function ctrEditarUsuarios()
    {
        if (isset($_POST["editarUsuario"])) {

            $tabla = "usuarios";
            $idUsu = (int)$_POST["idUsuario"];

            // BEFORE para auditoría (sin password)
            $beforeRow = ModeloUsuarios::mdlMostrarUsuarios($tabla, "id_usuario", $idUsu);
            $before = $beforeRow ? [
                'id_usuario' => (int)$beforeRow['id_usuario'],
                'nombre'     => $beforeRow['nombre'] ?? '',
                'usuario'    => $beforeRow['usuario'] ?? '',
                'correo'     => $beforeRow['correo'] ?? '',
                'perfil'     => $beforeRow['perfil'] ?? '',
            ] : [];

            // 1) Contraseña: si viene nueva, validar y hashear; si no, conservar actual
            $passwordChanged = false;
            if (!empty($_POST["editarPassword"])) {
                $password = $_POST["editarPassword"];

                // Validar política de contraseña
                if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&*!?_.\-]).{8,}$/', $password)) {
                    echo '<script>
                        swal({
                            type:"error",
                            title: "La contraseña no cumple los requisitos de seguridad",
                            text: "Debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial (@#$%^&*!?_.-).",
                            showConfirmButton:true,
                            confirmButtonText:"Cerrar"
                        });
                    </script>';
                    return;
                }

                $hashSeguro = password_hash($password, PASSWORD_DEFAULT);
                $passwordChanged = true;
            } else {
                // Mantener la contraseña actual (viene en hidden)
                $hashSeguro = $_POST["passwordActual"];
            }

            // 2) Validar unicidad de correo (no permitir duplicar en otro usuario)
            $item = "correo";
            $valor = $_POST["editarCorreo"];
            $usuarioExistente = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

            if ($usuarioExistente && $usuarioExistente["id_usuario"] != $idUsu) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "El correo ya está registrado en otro usuario",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            // 3) Preparar datos a actualizar
            $datos = array(
                "id_usuario" => $idUsu,
                "nombre"     => $_POST['editarNombre'],
                "usuario"    => $_POST['editarUsuario'],
                "correo"     => $_POST['editarCorreo'],
                "password"   => $hashSeguro,
                "perfil"     => $_POST['editarPerfil']
            );

            // 4) Actualizar
            $respuesta = ModeloUsuarios::mdlEditarUsuarios($tabla, $datos);

            if ($respuesta == "ok") {

                // AFTER para auditoría (sin password)
                $afterRow = ModeloUsuarios::mdlMostrarUsuarios($tabla, "id_usuario", $idUsu);
                $after = $afterRow ? [
                    'id_usuario' => (int)$afterRow['id_usuario'],
                    'nombre'     => $afterRow['nombre'] ?? '',
                    'usuario'    => $afterRow['usuario'] ?? '',
                    'correo'     => $afterRow['correo'] ?? '',
                    'perfil'     => $afterRow['perfil'] ?? '',
                ] : [];

                /* ===== AUDITORÍA: UPDATE (sin exponer password) ===== */
                if (function_exists('audit_log')) {
                    try {
                        // Si cambió password, añadimos una marca no sensible
                        if ($passwordChanged) {
                            $before['password'] = '[hidden]';
                            $after['password']  = '[updated]';
                        }
                        audit_log(
                            'Usuarios',
                            'UPDATE',
                            'usuarios',
                            $idUsu,
                            "Editó usuario {$after['nombre']} ({$after['usuario']})",
                            $before,
                            $after
                        );
                    } catch (Throwable $e) { error_log('AUDIT USUARIOS UPDATE ERROR: '.$e->getMessage()); }
                }
                /* ==================================================== */

                echo '<script>
                    swal({
                        type: "success",
                        title: "El usuario ha sido editado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "usuarios";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "El usuario no ha sido editado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "usuarios";
                        }
                    });
                </script>';
            }
        }
    }

    /* =========================
     *  Borrar usuarios
     * ========================= */
    static public function ctrBorrarUsuarios()
    {
        if (isset($_GET['idUsuario'])) {

            $tabla = "usuarios";
            $idAEliminar = (int)$_GET['idUsuario'];
            $idLogueado  = $_SESSION['id'] ?? null;

            if ($idLogueado !== null && $idAEliminar == (int)$idLogueado) {
                echo '<script>
                swal({
                    type:"error",
                    title: "No puede eliminarse a sí mismo",
                    showConfirmButton:true,
                    confirmButtonText:"Cerrar"
                }).then(function(result){
                    if(result.value){
                        window.location = "usuarios";
                    }
                });
                </script>';
                return;
            }

            // BEFORE para auditoría
            $beforeRow = ModeloUsuarios::mdlMostrarUsuarios($tabla, "id_usuario", $idAEliminar);
            $before = $beforeRow ? [
                'id_usuario' => (int)$beforeRow['id_usuario'],
                'nombre'     => $beforeRow['nombre'] ?? '',
                'usuario'    => $beforeRow['usuario'] ?? '',
                'correo'     => $beforeRow['correo'] ?? '',
                'perfil'     => $beforeRow['perfil'] ?? '',
            ] : [];

            $respuesta = ModeloUsuarios::mdlBorrarUsuarios($tabla, $idAEliminar);

            if ($respuesta == "ok") {

                /* ===== AUDITORÍA: DELETE (sin password) ===== */
                if ($before && function_exists('audit_log')) {
                    try {
                        audit_log(
                            'Usuarios',
                            'DELETE',
                            'usuarios',
                            $idAEliminar,
                            "Eliminó usuario {$before['nombre']} ({$before['usuario']})",
                            $before,
                            []
                        );
                    } catch (Throwable $e) { error_log('AUDIT USUARIOS DELETE ERROR: '.$e->getMessage()); }
                }
                /* ========================================== */

                echo '<script>
                swal({
                    type:"success",
                    title: "El usuario ha sido borrado correctamente",
                    showConfirmButton:true,
                    confirmButtonText:"Cerrar"
                }).then(function(result){
                    if(result.value){
                        window.location = "usuarios";
                    }
                });
                </script>';
            } else {
                echo '<script>
                swal({
                    type:"error",
                    title: "Error al eliminar el usuario",
                    showConfirmButton:true,
                    confirmButtonText:"Cerrar"
                });
                </script>';
            }
        }
    }
}
