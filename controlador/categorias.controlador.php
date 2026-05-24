<?php

class ControladorCategorias{

  /* ============================
     MOSTRAR
     ============================ */
  static public function ctrMostrarCategorias($item,$valor){
    $tabla = "categorias";
    $respuesta = ModeloCategorias::mdlMostrarCategorias($tabla,$item,$valor);
    return $respuesta;
  }

  /* ============================
     CREAR CATEGORÍA + AUDITORÍA
     ============================ */
  static public function ctrCrearCategorias() {

    if (!isset($_POST["nombreCategoria"])) return;

    $tabla  = "categorias";
    $nombre = $_POST["nombreCategoria"];

    // Normalizar el nombre ingresado
    $nombreNormalizado = self::normalizarTexto($nombre);

    // Verificar duplicados (comparación normalizada)
    $categorias = ModeloCategorias::mdlMostrarCategorias($tabla, null, null);
    if (is_array($categorias)) {
      foreach ($categorias as $cat) {
        $catNormalizado = self::normalizarTexto($cat["nombre_categoria"]);
        if ($nombreNormalizado === $catNormalizado) {
          echo '<script>
            swal({
              type:"error",
              title:"La categoría ya existe, verifíca mayúsculas y tildes!",
              showConfirmButton:true,
              confirmButtonText:"Cerrar"
            }).then(function(result){
              if(result.value){ window.location = "categorias"; }
            });
          </script>';
          return;
        }
      }
    }

    // Insertar
    $datos = array("nombre_categoria" => $nombre);
    $respuesta = ModeloCategorias::mdlIngresarCategorias($tabla, $datos);

    if ($respuesta == "ok") {

      /* ===== AUDITORÍA: CREATE ===== */
      // Intentamos recuperar el registro recién insertado por nombre
      $nueva = ModeloCategorias::mdlMostrarCategorias($tabla, "nombre_categoria", $nombre);
      if ($nueva && function_exists('audit_log')) {
        try {
          $after = [
            'id_categorias'   => isset($nueva['id_categorias']) ? (int)$nueva['id_categorias'] : null,
            'nombre_categoria'=> $nueva['nombre_categoria'] ?? ''
          ];
          audit_log(
            'Categorias',
            'CREATE',
            'categorias',
            $after['id_categorias'],
            "Creó categoría {$after['nombre_categoria']}",
            [],
            $after
          );
        } catch (Throwable $e) { error_log('AUDIT CATEGORIAS CREATE ERROR: '.$e->getMessage()); }
      }
      /* ============================ */

      echo '<script>
        swal({
          type:"success",
          title:"La categoría se ha guardado correctamente",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "categorias"; }
        });
      </script>';

    }
  }

  /* ============================
     EDITAR CATEGORÍA + AUDITORÍA
     ============================ */
  static public function ctrEditarCategorias(){

    if (!isset($_POST["editarNombreC"])) return;

    $tabla = "categorias";

    $idCategoria     = (int) $_POST['id_categorias'];
    $nombreNuevo     = trim($_POST['editarNombreC']);
    $nombreAnterior  = isset($_POST['nombre_categoria_anterior']) ? trim($_POST['nombre_categoria_anterior']) : '';

    // BEFORE para auditoría
    $beforeRow = ModeloCategorias::mdlMostrarCategorias($tabla, "id_categorias", $idCategoria);

    // 0) Si no hay cambios, terminar normal
    if ($nombreAnterior === $nombreNuevo) {
      echo '<script>
        swal({
          type:"info",
          title: "No se realizaron cambios en el nombre",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "categorias"; }
        });
      </script>';
      return;
    }

    // 1) Validar duplicado (usa tu modelo con exclusión por id)
    $existe = ModeloCategorias::mdlExisteNombreCategoria($tabla, $nombreNuevo, $idCategoria);
    if ($existe > 0) {
      echo '<script>
        swal({
          type:"error",
          title: "Ya existe una categoría con ese nombre, verifíca mayúsculas y tildes!",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "categorias"; }
        });
      </script>';
      return;
    }

    // 2) Actualiza categoría
    $datos = array(
      "id_categorias"    => $idCategoria,
      "nombre_categoria" => $nombreNuevo
    );
    $respuesta = ModeloCategorias::mdlEditarCategorias($tabla, $datos);

    if ($respuesta == "ok") {

      // 3) Propaga cambio a productos (relación implícita)
      if ($nombreAnterior !== '' && $nombreAnterior !== $nombreNuevo) {
        @ModeloCategorias::mdlPropagarCambioCategoriaEnProductos("productos", $nombreAnterior, $nombreNuevo);
      }

      /* ===== AUDITORÍA: UPDATE ===== */
      $afterRow = ModeloCategorias::mdlMostrarCategorias($tabla, "id_categorias", $idCategoria);
      if ($afterRow && function_exists('audit_log')) {
        try {
          $before = [
            'id_categorias'    => isset($beforeRow['id_categorias']) ? (int)$beforeRow['id_categorias'] : $idCategoria,
            'nombre_categoria' => $beforeRow['nombre_categoria'] ?? $nombreAnterior
          ];
          $after = [
            'id_categorias'    => isset($afterRow['id_categorias']) ? (int)$afterRow['id_categorias'] : $idCategoria,
            'nombre_categoria' => $afterRow['nombre_categoria'] ?? $nombreNuevo
          ];
          audit_log(
            'Categorias',
            'UPDATE',
            'categorias',
            $idCategoria,
            "Actualizó categoría de '{$before['nombre_categoria']}' a '{$after['nombre_categoria']}'",
            $before,
            $after
          );
        } catch (Throwable $e) { error_log('AUDIT CATEGORIAS UPDATE ERROR: '.$e->getMessage()); }
      }
      /* ============================ */

      echo '<script>
        swal({
          type:"success",
          title: "La categoría se actualizó y los productos asociados fueron actualizados",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "categorias"; }
        });
      </script>';

    } else {
      echo '<script>
        swal({
          type:"error",
          title: "La categoría no ha sido editada correctamente",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "categorias"; }
        });
      </script>';
    }
  }

  /* ============================
     BORRAR CATEGORÍA + AUDITORÍA
     ============================ */
  static public function ctrBorrarCategorias(){

    if (!isset($_GET["id_categorias"])) return;

    $tabla = "categorias";
    $id    = (int)$_GET["id_categorias"];

    // BEFORE para auditoría (traemos la fila antes de borrar)
    $beforeRow = ModeloCategorias::mdlMostrarCategorias($tabla, "id_categorias", $id);

    $respuesta = ModeloCategorias::mdlBorrarCategorias($tabla, $id);

    if ($respuesta == "ok") {

      /* ===== AUDITORÍA: DELETE ===== */
      if ($beforeRow && function_exists('audit_log')) {
        try {
          $before = [
            'id_categorias'    => isset($beforeRow['id_categorias']) ? (int)$beforeRow['id_categorias'] : $id,
            'nombre_categoria' => $beforeRow['nombre_categoria'] ?? ''
          ];
          audit_log(
            'Categorias',
            'DELETE',
            'categorias',
            $id,
            "Eliminó categoría {$before['nombre_categoria']}",
            $before,
            []
          );
        } catch (Throwable $e) { error_log('AUDIT CATEGORIAS DELETE ERROR: '.$e->getMessage()); }
      }
      /* ============================ */

      echo '<script>
        swal({
          type:"success",
          title: "La categoría se eliminó correctamente!",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "categorias"; }
        });
      </script>';

    } else if ($respuesta == "error_relacion") {

      echo '<script>
        swal({
          type:"error",
          title: "No se puede eliminar la categoría porque tiene productos asociados",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "categorias"; }
        });
      </script>';

    } else {

      echo '<script>
        swal({
          type:"error",
          title: "Error al intentar eliminar la categoría",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(result){
          if(result.value){ window.location = "categorias"; }
        });
      </script>';
    }
  }

  /* ============================
     UTILIDAD: normalizar texto
     ============================ */
  private static function normalizarTexto($texto) {
    $texto = mb_strtolower($texto, 'UTF-8');
    $texto = strtr($texto, [
      'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u',
      'Á'=>'a', 'É'=>'e', 'Í'=>'i', 'Ó'=>'o', 'Ú'=>'u',
      'ä'=>'a', 'ë'=>'e', 'ï'=>'i', 'ö'=>'o', 'ü'=>'u',
      'Ä'=>'a', 'Ë'=>'e', 'Ï'=>'i', 'Ö'=>'o', 'Ü'=>'u',
      'ñ'=>'n', 'Ñ'=>'n'
    ]);
    $texto = preg_replace('/\s+/', ' ', trim($texto));
    return $texto;
  }
}
