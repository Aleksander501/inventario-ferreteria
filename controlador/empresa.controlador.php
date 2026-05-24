<?php

class ControladorEmpresa
{

  /* ============================
     HELPERS AUDITORÍA
     ============================ */

  /** Devuelve la fila única de empresa (asumiendo 1 registro) */
  private static function traerEmpresaUnica() : ?array {
    $tabla = "empresa";
    $fila = ModeloEmpresa::mdlMostrarEmpresa($tabla, null, null);
    // Algunos modelos devuelven array de 1 fila; normalizamos
    if (is_array($fila)) {
      // si es lista, toma la primera
      if (isset($fila[0]) && is_array($fila[0])) return $fila[0];
      // si ya es fila asociativa
      return $fila;
    }
    return null;
  }

  /** Snapshot completo con todos los campos solicitados */
  private static function empresaSnapshot(?array $row) : array {
    if (!$row) return [];
    return [
      'id_empresa'     => isset($row['id_empresa']) ? (int)$row['id_empresa'] : null,
      'nombre_empresa' => $row['nombre_empresa'] ?? '',
      'telefono'       => $row['telefono'] ?? '',
      'sitioWeb'       => $row['sitioWeb'] ?? '',
      'direccion'      => $row['direccion'] ?? '',
      'logo'           => $row['logo'] ?? '',
      'icono'          => $row['icono'] ?? '',
    ];
  }

  /* ============================
     MOSTRAR
     ============================ */

  static public function ctrMostrarEmpresa($item, $valor)
  {
    $tabla = "empresa";
    $respuesta = ModeloEmpresa::mdlMostrarEmpresa($tabla, $item, $valor);
    return $respuesta;
  }

  static public function ctrMostrarLogo()
  {
    $tabla = "empresa";
    $respuesta = ModeloEmpresa::mdlMostrarLogo($tabla);
    return $respuesta;
  }

  static public function ctrMostrarIcono()
  {
    $tabla = "empresa";
    $respuesta = ModeloEmpresa::mdlMostrarIcono($tabla);
    return $respuesta;
  }

  /* ============================
     ACTUALIZAR LOGO
     ============================ */
  static public function ctrActualizarLogo($item, $valor)
  {
    $tabla = "empresa";
    $id = 1;

    // BEFORE para auditoría (snapshot completo)
    $beforeRow  = self::traerEmpresaUnica();
    $beforeSnap = self::empresaSnapshot($beforeRow);

    $plantilla = ModeloEmpresa::mdlMostrarLogo($tabla);
    $valorNuevo = $valor;

    if (isset($valor["tmp_name"])) {
      list($ancho, $alto) = getimagesize($valor["tmp_name"]);

      if ($item == "logo") {

        // Borrar anterior si existe
        if (!empty($plantilla["logo"]) && file_exists("../" . $plantilla["logo"])) {
          @unlink("../" . $plantilla["logo"]);
        }

        $nuevoAncho = 1024;
        $nuevoAlto  = 576;
        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

        $aleatorio = mt_rand(100, 999);

        if ($valor["type"] == "image/jpeg") {
          $ruta = "../vistas/img/plantilla/logo" . $aleatorio . ".jpg";
          $origen = imagecreatefromjpeg($valor["tmp_name"]);
          imagecopyresized($destino,$origen,0,0,0,0,$nuevoAncho,$nuevoAlto,$ancho,$alto);
          imagejpeg($destino, $ruta);
        }

        if ($valor["type"] == "image/png") {
          $ruta = "../vistas/img/plantilla/logo" . $aleatorio . ".png";
          $origen = imagecreatefrompng($valor["tmp_name"]);
          imagealphablending($destino, FALSE);
          imagesavealpha($destino, TRUE);
          imagecopyresized($destino,$origen,0,0,0,0,$nuevoAncho,$nuevoAlto,$ancho,$alto);
          imagepng($destino, $ruta);
        }

        // Guardar ruta relativa
        $valorNuevo = substr($ruta, 3);
      }
    }

    $respuesta = ModeloEmpresa::mdlActualizarLogo($tabla, $id, $item, $valorNuevo);

    // AFTER + auditoría
    if ($respuesta === "ok") {
      $afterRow  = self::traerEmpresaUnica();
      $afterSnap = self::empresaSnapshot($afterRow);

      if (function_exists('audit_log')) {
        try {
          audit_log(
            'Empresa',
            'UPDATE_LOGO',
            'empresa',
            $afterSnap['id_empresa'] ?? $id,
            'Actualizó el logo de la empresa',
            $beforeSnap,
            $afterSnap
          );
        } catch (Throwable $e) { error_log('AUDIT EMPRESA LOGO ERROR: '.$e->getMessage()); }
      }
    }

    return $respuesta;
  }

  /* ============================
     ACTUALIZAR ÍCONO
     ============================ */
  static public function ctrActualizarIcono($item, $valor)
  {
    $tabla = "empresa";
    $id = 1;

    // BEFORE para auditoría
    $beforeRow  = self::traerEmpresaUnica();
    $beforeSnap = self::empresaSnapshot($beforeRow);

    $plantilla = ModeloEmpresa::mdlMostrarIcono($tabla);
    $valorNuevo = $valor;

    if (isset($valor["tmp_name"])) {
      list($ancho, $alto) = getimagesize($valor["tmp_name"]);

      if ($item == "icono") {

        if (!empty($plantilla["icono"]) && file_exists("../" . $plantilla["icono"])) {
          @unlink("../" . $plantilla["icono"]);
        }

        $nuevoAncho = 200;
        $nuevoAlto  = 200;
        $destino    = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

        if ($valor["type"] == "image/jpeg") {
          $ruta   = "../vistas/img/icono/icono.jpg";
          $origen = imagecreatefromjpeg($valor["tmp_name"]);
          imagecopyresized($destino,$origen,0,0,0,0,$nuevoAncho,$nuevoAlto,$ancho,$alto);
          imagejpeg($destino, $ruta);
        }

        if ($valor["type"] == "image/png") {
          $ruta   = "../vistas/img/icono/icono.png";
          $origen = imagecreatefrompng($valor["tmp_name"]);
          imagealphablending($destino, FALSE);
          imagesavealpha($destino, TRUE);
          imagecopyresized($destino,$origen,0,0,0,0,$nuevoAncho,$nuevoAlto,$ancho,$alto);
          imagepng($destino, $ruta);
        }

        $valorNuevo = substr($ruta, 3);
      }
    }

    $respuesta = ModeloEmpresa::mdlActualizarIcono($tabla, $id, $item, $valorNuevo);

    // AFTER + auditoría
    if ($respuesta === "ok") {
      $afterRow  = self::traerEmpresaUnica();
      $afterSnap = self::empresaSnapshot($afterRow);

      if (function_exists('audit_log')) {
        try {
          audit_log(
            'Empresa',
            'UPDATE_ICONO',
            'empresa',
            $afterSnap['id_empresa'] ?? $id,
            'Actualizó el ícono de la empresa',
            $beforeSnap,
            $afterSnap
          );
        } catch (Throwable $e) { error_log('AUDIT EMPRESA ICONO ERROR: '.$e->getMessage()); }
      }
    }

    return $respuesta;
  }

  /* ============================
     EDITAR DATOS DE EMPRESA
     ============================ */
  static public function ctrEditarEmpresa()
  {
    if (isset($_POST["editarEmpresa"])) {

      $tabla = "empresa";
      $idEmp = (int)($_POST['id_empresa'] ?? 1);

      // BEFORE para auditoría
      $beforeRow  = self::traerEmpresaUnica();
      $beforeSnap = self::empresaSnapshot($beforeRow);

      $datos = array(
        "id_empresa"     => $idEmp,
        "nombre_empresa" => $_POST['editarEmpresa'],
        "telefono"       => $_POST['editarTelefono'],
        "sitioWeb"       => $_POST['editarSitioWeb'],
        "direccion"      => $_POST['editarDireccion']
      );

      $respuesta = ModeloEmpresa::mdlEditarEmpresa($tabla, $datos);

      if ($respuesta == "ok") {

        // AFTER + auditoría
        $afterRow  = self::traerEmpresaUnica();
        $afterSnap = self::empresaSnapshot($afterRow);

        if (function_exists('audit_log')) {
          try {
            audit_log(
              'Empresa',
              'UPDATE',
              'empresa',
              $afterSnap['id_empresa'] ?? $idEmp,
              'Actualizó datos de la empresa',
              $beforeSnap,
              $afterSnap
            );
          } catch (Throwable $e) { error_log('AUDIT EMPRESA UPDATE ERROR: '.$e->getMessage()); }
        }

        echo '<script>
          swal({
            type:"success",
            title: "La empresa ha sido editada correctamente",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){
            if(result.value){ window.location = "empresa"; }
          });
        </script>';

      } else {

        echo '<script>
          swal({
            type:"error",
            title: "La Empresa no ha sido editada correctamente",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){
            if(result.value){ window.location = "empresa"; }
          });
        </script>';

      }
    }
  }
}
