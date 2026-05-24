<?php

class ControladorProveedores{

  /* ============================
     MOSTRAR
     ============================ */
  static public function ctrMostrarProveedores($item,$valor){
    $tabla = "proveedores";
    $respuesta = ModeloProveedores::mdlMostrarProveedores($tabla,$item,$valor);
    return $respuesta;
  }

  /* ============================
     CREAR PROVEEDOR + AUDITORÍA
     ============================ */
  static public function ctrCrearProveedores(){

    if (!isset($_POST["nuevoEmpresa"])) return;

    $tabla  = "proveedores";
    $nombre = trim($_POST['nuevoEmpresa']);

    // 1) Validación de duplicado insensible (mayúsculas/tildes)
    $todos = ModeloProveedores::mdlMostrarProveedores($tabla, null, null);
    $nuevoNorm = self::normalizarTexto($nombre);

    foreach ($todos as $p) {
      $existenteNorm = self::normalizarTexto($p["nombre_proveedor"]);
      if ($existenteNorm === $nuevoNorm) {
        echo '<script>
          swal({
            type:"error",
            title: "El proveedor ya existe, verifíca mayúsculas y tildes!",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){
            if(result.value){ window.location = "proveedores"; }
          });
        </script>';
        return;
      }
    }

    // 2) Insertar
    $datos = array(
      "nombre_proveedor" => $nombre,
      "tipo_proveedor"   => $_POST['TipoProveedor'],
      "correo"           => $_POST['nuevoCorreo'],
      "telefono"         => $_POST['nuevoTelefono'],
      "direccion"        => $_POST['nuevoDireccion']
    );

    $respuesta = ModeloProveedores::mdlIngresarProveedores($tabla,$datos);

    if ($respuesta == "ok") {

      /* ===== AUDITORÍA: CREATE ===== */
      // Recuperar registro recién creado por nombre (suficiente en este flujo)
      $nuevo = ModeloProveedores::mdlMostrarProveedores($tabla, "nombre_proveedor", $nombre);
      if ($nuevo && function_exists('audit_log')) {
        try {
          $after = [
            'id_proveedor'     => isset($nuevo['id_proveedor']) ? (int)$nuevo['id_proveedor'] : null,
            'nombre_proveedor' => $nuevo['nombre_proveedor'] ?? '',
            'tipo_proveedor'   => $nuevo['tipo_proveedor'] ?? '',
            'correo'           => $nuevo['correo'] ?? '',
            'telefono'         => $nuevo['telefono'] ?? '',
            'direccion'        => $nuevo['direccion'] ?? '',
          ];
          audit_log(
            'Proveedores',
            'CREATE',
            'proveedores',
            $after['id_proveedor'],
            "Creó proveedor {$after['nombre_proveedor']}",
            [],
            $after
          );
        } catch (Throwable $e) { error_log('AUDIT PROVEEDORES CREATE ERROR: '.$e->getMessage()); }
      }
      /* ============================ */

      echo '<script>
        swal({
          type:"success",
          title: "El proveedor ha sido guardado correctamente",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "proveedores"; }
        });
      </script>';

    } else {
      echo '<script>
        swal({
          type:"error",
          title: "El proveedor no ha sido guardado correctamente",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "proveedores"; }
        });
      </script>';
    }
  }

  /* ============================
     EDITAR PROVEEDOR + AUDITORÍA
     ============================ */
  static public function ctrEditarProveedores(){

    if (!isset($_POST["editarEmpresa"])) return;

    $tabla = "proveedores";

    $idProveedor        = (int) $_POST['id_proveedor'];
    $nombreNuevo        = trim($_POST['editarEmpresa']);
    $tipoNuevo          = $_POST['editarTipoEmpresa'];
    $correoNuevo        = $_POST['editarCorreo'];
    $telNuevo           = $_POST['editarTelefono'];
    $dirNuevo           = $_POST['editarDireccion'];

    $nombreAnterior     = isset($_POST['nombre_proveedor_anterior']) ? trim($_POST['nombre_proveedor_anterior']) : "";
    $tipoAnterior       = isset($_POST['tipo_proveedor_anterior']) ? trim($_POST['tipo_proveedor_anterior']) : "";

    // BEFORE para auditoría
    $beforeRow = ModeloProveedores::mdlMostrarProveedores($tabla, "id_proveedor", $idProveedor);
    $before = $beforeRow ? [
      'id_proveedor'     => (int)$beforeRow['id_proveedor'],
      'nombre_proveedor' => $beforeRow['nombre_proveedor'] ?? '',
      'tipo_proveedor'   => $beforeRow['tipo_proveedor'] ?? '',
      'correo'           => $beforeRow['correo'] ?? '',
      'telefono'         => $beforeRow['telefono'] ?? '',
      'direccion'        => $beforeRow['direccion'] ?? '',
    ] : [];

    /* 1) Validación de duplicado INSENSIBLE (mayúsculas/tildes) excluyendo este mismo id */
    $todos = ModeloProveedores::mdlMostrarProveedores($tabla, null, null);
    $nuevoNorm = self::normalizarTexto($nombreNuevo);

    foreach ($todos as $p) {
      if ((int)$p["id_proveedor"] === $idProveedor) continue; // excluir el actual
      $existenteNorm = self::normalizarTexto($p["nombre_proveedor"]);
      if ($existenteNorm === $nuevoNorm) {
        echo '<script>
          swal({
            type:"error",
            title: "Ya existe otro proveedor con ese nombre (no se permiten duplicados aunque cambien mayúsculas o tildes)",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){
            if(result.value){ window.location = "proveedores"; }
          });
        </script>';
        return;
      }
    }

    /* 2) Actualizar proveedor */
    $datos = array(
      "id_proveedor"     => $idProveedor,
      "nombre_proveedor" => $nombreNuevo,
      "tipo_proveedor"   => $tipoNuevo,
      "correo"           => $correoNuevo,
      "telefono"         => $telNuevo,
      "direccion"        => $dirNuevo
    );

    $respuesta = ModeloProveedores::mdlEditarProveedores($tabla, $datos);

    if ($respuesta == "ok") {

      /* 3) Propagación a entradas_productos (relación implícita por texto) */
      if ($nombreAnterior !== "" && $nombreAnterior !== $nombreNuevo) {
        // Cambió nombre (y quizá tipo)
        @ModeloProveedores::mdlPropagarProveedorEnEntradas(
          "entradas_productos",
          $nombreAnterior,  // WHERE
          $nombreNuevo,     // SET nombre_proveedor
          $tipoNuevo        // SET tipo_proveedor
        );
      } else if ($tipoAnterior !== "" && $tipoAnterior !== $tipoNuevo) {
        // Solo cambió tipo
        @ModeloProveedores::mdlPropagarSoloTipoProveedorEnEntradas(
          "entradas_productos",
          $nombreNuevo,     // nombre actual
          $tipoNuevo
        );
      }

      // AFTER para auditoría
      $afterRow = ModeloProveedores::mdlMostrarProveedores($tabla, "id_proveedor", $idProveedor);
      $after = $afterRow ? [
        'id_proveedor'     => (int)$afterRow['id_proveedor'],
        'nombre_proveedor' => $afterRow['nombre_proveedor'] ?? '',
        'tipo_proveedor'   => $afterRow['tipo_proveedor'] ?? '',
        'correo'           => $afterRow['correo'] ?? '',
        'telefono'         => $afterRow['telefono'] ?? '',
        'direccion'        => $afterRow['direccion'] ?? '',
      ] : [];

      /* ===== AUDITORÍA: UPDATE ===== */
      if ($after && function_exists('audit_log')) {
        try {
          audit_log(
            'Proveedores',
            'UPDATE',
            'proveedores',
            $idProveedor,
            "Editó proveedor {$after['nombre_proveedor']}",
            $before,
            $after
          );
        } catch (Throwable $e) { error_log('AUDIT PROVEEDORES UPDATE ERROR: '.$e->getMessage()); }
      }
      /* ============================ */

      echo '<script>
        swal({
          type:"success",
          title: "El proveedor y las entradas asociadas fueron actualizados",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "proveedores"; }
        });
      </script>';

    } else {

      echo '<script>
        swal({
          type:"error",
          title: "El proveedor no ha sido editado correctamente",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "proveedores"; }
        });
      </script>';
    }
  }

  /* ============================
     BORRAR PROVEEDOR + AUDITORÍA
     ============================ */
  static public function ctrBorrarProveedores(){

    if (!isset($_GET['idProveedores'])) return;

    $tabla = "proveedores";
    $id    = (int)$_GET['idProveedores'];

    // BEFORE para auditoría
    $proveedor = ModeloProveedores::mdlMostrarProveedores($tabla, "id_proveedor", $id);
    $before = $proveedor ? [
      'id_proveedor'     => (int)$proveedor['id_proveedor'],
      'nombre_proveedor' => $proveedor['nombre_proveedor'] ?? '',
      'tipo_proveedor'   => $proveedor['tipo_proveedor'] ?? '',
      'correo'           => $proveedor['correo'] ?? '',
      'telefono'         => $proveedor['telefono'] ?? '',
      'direccion'        => $proveedor['direccion'] ?? '',
    ] : [];

    $nombreProveedor = $proveedor ? $proveedor["nombre_proveedor"] : "";

    // Validar si tiene entradas asociadas
    if ($nombreProveedor !== "") {
      $tiene = ModeloProveedores::mdlProveedorTieneEntradas("entradas_productos", $nombreProveedor);
      if ($tiene > 0) {
        echo '<script>
          swal({
            type:"error",
            title: "No se puede eliminar: el proveedor tiene entradas de productos asociadas",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){
            if(result.value){ window.location = "proveedores"; }
          });
        </script>';
        return;
      }
    }

    // Eliminar
    $respuesta = ModeloProveedores::mdlBorrarProveedores($tabla, $id);

    if ($respuesta == "ok") {

      /* ===== AUDITORÍA: DELETE ===== */
      if ($before && function_exists('audit_log')) {
        try {
          audit_log(
            'Proveedores',
            'DELETE',
            'proveedores',
            $id,
            "Eliminó proveedor {$before['nombre_proveedor']}",
            $before,
            []
          );
        } catch (Throwable $e) { error_log('AUDIT PROVEEDORES DELETE ERROR: '.$e->getMessage()); }
      }
      /* ============================ */

      echo '<script>
        swal({
          type:"success",
          title: "El proveedor ha sido borrado correctamente",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "proveedores"; }
        });
      </script>';

    } else {
      echo '<script>
        swal({
          type:"error",
          title: "Error al eliminar el proveedor",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "proveedores"; }
        });
      </script>';
    }
  }

  /* ============================
     UTILIDAD: normalizar texto
     ============================ */
  private static function normalizarTexto($texto) {
    // a minúsculas
    $texto = mb_strtolower($texto, 'UTF-8');

    // quitar tildes/diacríticos
    $texto = strtr($texto, [
      'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
      'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u',
      'ä'=>'a','ë'=>'e','ï'=>'i','ö'=>'o','ü'=>'u',
      'Ä'=>'a','Ë'=>'e','Ï'=>'i','Ö'=>'o','Ü'=>'u',
      'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u',
      'À'=>'a','È'=>'e','Ì'=>'i','Ò'=>'o','Ù'=>'u',
      'ñ'=>'n','Ñ'=>'n'
    ]);

    // colapsar espacios
    $texto = preg_replace('/\s+/', ' ', trim($texto));

    return $texto;
  }

}
