<?php
class ControladorProductos{

  /* ============================
     MOSTRAR LISTADOS
     ============================ */
  static public function ctrMostrarProductos($item,$valor){
    $tabla = "productos";
    $respuesta = ModeloProductos::mdlMostrarProductos($tabla,$item,$valor);
    return $respuesta;
  }

  static public function ctrMostrarProductosEntradas($item,$valor){
    $tabla = "entradas_productos";
    $respuesta = ModeloProductos::mdlMostrarProductosEntradas($tabla,$item,$valor);
    return $respuesta;
  }

  static public function ctrMostrarProductosSalidas($item,$valor){
    $tabla = "salidasp";
    $respuesta = ModeloProductos::mdlMostrarProductosSalidas($tabla,$item,$valor);
    return $respuesta;
  }

  /* ============================
     CREAR PRODUCTO
     ============================ */
  static public function ctrCrearProductos(){

    if (isset($_POST["nuevoNombre"])) {

      $tabla = "productos";
      $upc   = trim($_POST['nuevoUpc']);

      // Validar UPC duplicado
      if (ModeloProductos::mdlExisteUPC($tabla, $upc)) {
        echo '<script>
          swal({
            type:"error",
            title:"UPC duplicado",
            text:"El código UPC ingresado ya existe en el sistema.",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){ if(result.value){ window.location = "productos"; }});
        </script>';
        return;
      }

      $datos = array(
        "upc_producto"         => $upc,
        "nombre"               => $_POST['nuevoNombre'],
        "marca"                => $_POST['nuevoMarca'],
        "categoria"            => $_POST['categoriaP'],
        "descripcion_producto" => $_POST['nuevoDescripcion'],
        "unidad_medida"        => $_POST['unidadMedida'],
        "precio_compra"        => $_POST['nuevoPrecioCompra'],
        "precio_venta1"        => $_POST['nuevoPrecioVenta1'],
        "precio_venta2"        => $_POST['nuevoPrecioVenta2'],
        "precio_venta3"        => $_POST['nuevoPrecioVenta3'],
        "nombre_proveedor"     => $_POST['nuevoProveedor'],
        "tipo_proveedor"       => $_POST['nuevoTipoProveedor'],
        "stock"                => $_POST['nuevoStock']
      );

      $respuesta = ModeloProductos::mdlIngresarProductos($tabla,$datos);

      if ($respuesta == "ok") {

        /* ===== AUDITORÍA: CREATE (todos los campos excepto 'fecha') ===== */
        $recienCreado = ModeloProductos::mdlMostrarProductos($tabla, "upc_producto", $upc);
        if ($recienCreado && function_exists('audit_log')) {
          try {
            $afterFull = [
              // 'id_producto' INT -> si NO quieres incluirlo, lo omitimos
              'upc_producto'         => $recienCreado['upc_producto'] ?? '',
              'nombre'               => $recienCreado['nombre'] ?? '',
              'marca'                => $recienCreado['marca'] ?? '',
              'nombre_proveedor'     => $recienCreado['nombre_proveedor'] ?? '',
              'tipo_proveedor'       => $recienCreado['tipo_proveedor'] ?? '',
              'categoria'            => $recienCreado['categoria'] ?? '',
              'descripcion_producto' => $recienCreado['descripcion_producto'] ?? '',
              'unidad_medida'        => $recienCreado['unidad_medida'] ?? '',
              'precio_compra'        => isset($recienCreado['precio_compra']) ? (float)$recienCreado['precio_compra'] : null,
              'precio_venta1'        => isset($recienCreado['precio_venta1']) ? (int)$recienCreado['precio_venta1'] : null,
              'precio_venta2'        => isset($recienCreado['precio_venta2']) ? (float)$recienCreado['precio_venta2'] : null,
              'precio_venta3'        => isset($recienCreado['precio_venta3']) ? (float)$recienCreado['precio_venta3'] : null,
              'stock'                => isset($recienCreado['stock']) ? (int)$recienCreado['stock'] : null,
              // 'fecha' => (omitida como pediste)
            ];
            audit_log(
              'Inventario',
              'CREATE',
              'productos',
              $recienCreado['id_producto'] ?? null, // registro_id sí puede ir aquí
              "Creó producto {$afterFull['nombre']}",
              [],
              $afterFull
            );
          } catch (Throwable $e) { error_log('AUDIT CREATE ERROR: '.$e->getMessage()); }
        }
        /* ================================================================= */

        echo '<script>
          swal({
            type:"success",
            title: "El producto ha sido guardado correctamente",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){ if(result.value){ window.location = "productos"; }});
        </script>';
      } else {
        echo '<script>
          swal({
            type:"error",
            title: "El producto no ha sido guardado correctamente",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){ if(result.value){ window.location = "productos"; }});
        </script>';
      }
    }
  }

  /* ============================
     EDITAR PRODUCTO
     ============================ */
  static public function ctrEditarProductos(){

    if (isset($_POST["editarNombre"])) {

      $tabla = "productos";
      $id    = (int)$_POST['id_producto'];
      $upc   = trim($_POST['editarUpc']); // readonly en tu UI

      // === A N T E S para auditoría
      $before = ModeloProductos::mdlMostrarProductos($tabla, "id_producto", $id);

      // Validación de UPC duplicado (excluye el propio ID)
      if (self::upcDuplicadoSimple($upc, $id)) {
        echo '<script>
          swal({
            type:"error",
            title:"UPC duplicado",
            text:"El código UPC ingresado ya existe en otro producto.",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){ if(result.value){ window.location = "productos"; }});
        </script>';
        return;
      }

      // Capturar nombre nuevo y anterior
      $nombreNuevo    = trim($_POST['editarNombre']);
      $nombreAnterior = isset($_POST['nombre_anterior']) ? trim($_POST['nombre_anterior']) : $nombreNuevo;

      // Datos para actualizar producto
      $datos = array(
        "id_producto"          => $id,
        "upc_producto"         => $upc,
        "nombre"               => $nombreNuevo,
        "marca"                => $_POST['editarMarca'],
        "categoria"            => $_POST['editarCategoriaP'],
        "descripcion_producto" => $_POST['editarDescripcion'],
        "unidad_medida"        => $_POST['editarUnidadMedida'],
        "precio_compra"        => $_POST['editarPrecioCompra'],
        "precio_venta1"        => $_POST['editarPrecioVenta1'],
        "precio_venta2"        => $_POST['editarPrecioVenta2'],
        "precio_venta3"        => $_POST['editarPrecioVenta3'],
        "nombre_proveedor"     => $_POST['editarProveedor'],
        "tipo_proveedor"       => $_POST['editarTipoProveedor'],
        "stock"                => $_POST['editarStock'] // readonly
      );

      // 1) Actualizar producto
      $respuesta = ModeloProductos::mdlEditarProductos($tabla,$datos);

      if ($respuesta != "ok") {
        echo '<script>
          swal({
            type:"error",
            title: "El producto no ha sido editado correctamente",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){ if(result.value){ window.location = "productos"; }});
        </script>';
        return;
      }

      // 2) Si el nombre cambió, propagar a submódulos
      $warningPropagacion = false;
      if ($nombreAnterior !== $nombreNuevo) {
        $prop = ModeloProductos::mdlPropagarNombreProducto($nombreAnterior, $nombreNuevo);
        if ($prop != "ok") {
          $warningPropagacion = true; // NO hacemos return
          error_log('ADVERTENCIA: no se pudo propagar el nombre del producto.');
        }
      }

      // === D E S P U É S para auditoría
      $after = ModeloProductos::mdlMostrarProductos($tabla, "id_producto", $id);

      /* ===== AUDITORÍA: UPDATE (todos los campos excepto 'fecha') ===== */
      if ($before && $after && function_exists('audit_log')) {
        try {
          $beforeFull = [
            // 'id_producto' -> omitido a petición
            'upc_producto'         => $before['upc_producto'] ?? '',
            'nombre'               => $before['nombre'] ?? '',
            'marca'                => $before['marca'] ?? '',
            'nombre_proveedor'     => $before['nombre_proveedor'] ?? '',
            'tipo_proveedor'       => $before['tipo_proveedor'] ?? '',
            'categoria'            => $before['categoria'] ?? '',
            'descripcion_producto' => $before['descripcion_producto'] ?? '',
            'unidad_medida'        => $before['unidad_medida'] ?? '',
            'precio_compra'        => isset($before['precio_compra']) ? (float)$before['precio_compra'] : null,
            'precio_venta1'        => isset($before['precio_venta1']) ? (int)$before['precio_venta1'] : null,
            'precio_venta2'        => isset($before['precio_venta2']) ? (float)$before['precio_venta2'] : null,
            'precio_venta3'        => isset($before['precio_venta3']) ? (float)$before['precio_venta3'] : null,
            'stock'                => isset($before['stock']) ? (int)$before['stock'] : null,
            // 'fecha' omitida
          ];
          $afterFull = [
            'upc_producto'         => $after['upc_producto'] ?? '',
            'nombre'               => $after['nombre'] ?? '',
            'marca'                => $after['marca'] ?? '',
            'nombre_proveedor'     => $after['nombre_proveedor'] ?? '',
            'tipo_proveedor'       => $after['tipo_proveedor'] ?? '',
            'categoria'            => $after['categoria'] ?? '',
            'descripcion_producto' => $after['descripcion_producto'] ?? '',
            'unidad_medida'        => $after['unidad_medida'] ?? '',
            'precio_compra'        => isset($after['precio_compra']) ? (float)$after['precio_compra'] : null,
            'precio_venta1'        => isset($after['precio_venta1']) ? (int)$after['precio_venta1'] : null,
            'precio_venta2'        => isset($after['precio_venta2']) ? (float)$after['precio_venta2'] : null,
            'precio_venta3'        => isset($after['precio_venta3']) ? (float)$after['precio_venta3'] : null,
            'stock'                => isset($after['stock']) ? (int)$after['stock'] : null,
            // 'fecha' omitida
          ];

          audit_log(
            'Inventario',
            'UPDATE',
            'productos',
            (int)$id,
            "Actualizó producto {$after['nombre']}",
            $beforeFull,
            $afterFull
          );
        } catch (Throwable $e) { error_log('AUDIT UPDATE ERROR: '.$e->getMessage()); }
      }
      /* =================================================================== */

      // 3) OK final
      if ($warningPropagacion) {
        echo '<script>
          swal({
            type:"warning",
            title:"Producto editado",
            text:"Pero no se pudo propagar el nombre a entradas/salidas.",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){ if(result.value){ window.location = "productos"; }});
        </script>';
      } else {
        echo '<script>
          swal({
            type:"success",
            title: "El producto ha sido editado y propagado correctamente",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){ if(result.value){ window.location = "productos"; }});
        </script>';
      }
    }
  }

  /* ============================
     BORRAR PRODUCTO
     ============================ */
  static public function ctrBorrarProductos(){

    if (isset($_GET['idProductos'])) {

      $tabla = "productos";
      $id    = (int)$_GET['idProductos'];

      // Traer producto por id (antes)
      $producto = ModeloProductos::mdlMostrarProductos($tabla, "id_producto", $id);
      if (!$producto) {
        echo '<script>
          swal({ type:"error", title:"Producto no encontrado", showConfirmButton:true, confirmButtonText:"Cerrar" });
        </script>';
        return;
      }

      // Validar movimientos (entradas/salidas) por NOMBRE
      if (ModeloProductos::mdlTieneMovimientosPorNombre($producto["nombre"])) {
        echo '<script>
          swal({
            type:"error",
            title:"No se puede eliminar",
            text:"El producto tiene entradas o salidas registradas.",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){ if(result.value){ window.location = "productos"; }});
        </script>';
        return;
      }

      // Eliminar (ya validado)
      $respuesta = ModeloProductos::mdlBorrarProductos($tabla,$id);

      if ($respuesta == "ok") {

        /* ===== AUDITORÍA: DELETE (todos los campos excepto 'fecha') ===== */
        if (function_exists('audit_log')) {
          try {
            $beforeFull = [
              // 'id_producto' -> omitido a petición
              'upc_producto'         => $producto['upc_producto'] ?? '',
              'nombre'               => $producto['nombre'] ?? '',
              'marca'                => $producto['marca'] ?? '',
              'nombre_proveedor'     => $producto['nombre_proveedor'] ?? '',
              'tipo_proveedor'       => $producto['tipo_proveedor'] ?? '',
              'categoria'            => $producto['categoria'] ?? '',
              'descripcion_producto' => $producto['descripcion_producto'] ?? '',
              'unidad_medida'        => $producto['unidad_medida'] ?? '',
              'precio_compra'        => isset($producto['precio_compra']) ? (float)$producto['precio_compra'] : null,
              'precio_venta1'        => isset($producto['precio_venta1']) ? (int)$producto['precio_venta1'] : null,
              'precio_venta2'        => isset($producto['precio_venta2']) ? (float)$producto['precio_venta2'] : null,
              'precio_venta3'        => isset($producto['precio_venta3']) ? (float)$producto['precio_venta3'] : null,
              'stock'                => isset($producto['stock']) ? (int)$producto['stock'] : null,
              // 'fecha' omitida
            ];

            audit_log(
              'Inventario',
              'DELETE',
              'productos',
              $id,
              "Eliminó producto {$producto['nombre']}",
              $beforeFull,
              []
            );
          } catch (Throwable $e) { error_log('AUDIT DELETE ERROR: '.$e->getMessage()); }
        }
        /* ================================================================= */

        echo '<script>
          swal({
            type:"success",
            title: "El producto ha sido borrado correctamente",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          }).then(function(result){ if(result.value){ window.location = "productos"; }});
        </script>';
      } else {
        echo '<script>
          swal({
            type:"error",
            title: "Error al eliminar el producto",
            showConfirmButton:true,
            confirmButtonText:"Cerrar"
          });
        </script>';
      }
    }
  }

  /* ============================
     ENTRADA DE PRODUCTOS
     ============================ */
  static public function ctrCrearEntradaProductos(){

    if (isset($_POST["idEntrada"])) {

      $tablaEntradas  = "entradas_productos";
      $tablaProductos = "productos";

      $idProducto     = (int)$_POST['idEntrada'];
      $nombreProducto = $_POST['nombreProducto'];
      $entradaStock   = (int)$_POST['entradaStock'];

      // 1. Traer producto por id
      $productoActual = ControladorProductos::ctrMostrarProductos("id_producto", $idProducto);
      if(!$productoActual){
        echo '<script>
          swal({type:"error",title:"Producto no encontrado",showConfirmButton:true,confirmButtonText:"Cerrar"});
        </script>';
        return;
      }

      $id_producto = $productoActual["id_producto"];
      $stockOld    = (int)$productoActual["stock"];
      $stockNew    = $stockOld + $entradaStock;

      // 2. Actualizar stock
      $actualizarStock = ModeloProductos::mdlActualizarProductosEntrada($tablaProductos, $id_producto, $stockNew);
      if($actualizarStock != "ok"){
        echo '<script>
          swal({type:"error",title:"No se pudo actualizar el stock",showConfirmButton:true,confirmButtonText:"Cerrar"});
        </script>';
        return;
      }

      // 3. Registrar la entrada usando proveedor/tipo del producto
      $datosEntrada = array(
        "nombre_proveedor" => $productoActual['nombre_proveedor'],
        "tipo_proveedor"   => $productoActual['tipo_proveedor'],
        "nombreProducto"   => $nombreProducto,
        "entradap"         => $entradaStock
      );

      // Obtener ID de entrada
      $idEntrada = ModeloProductos::mdlIngresarProductosEntrada($tablaEntradas, $datosEntrada);

      if($idEntrada > 0){

        /* ===== AUDITORÍA: ENTRADA ===== */
        if (function_exists('audit_log')) {
          try {
            audit_log(
              'Entradas',
              'ENTRADA',
              'entradas_productos',
              $idEntrada,
              "Entrada de {$entradaStock} al producto {$productoActual['nombre']}",
              ['stock' => $stockOld],
              ['stock' => $stockNew]
            );
          } catch (Throwable $e) { error_log('AUDIT ENTRADA ERROR: '.$e->getMessage()); }
        }
        /* ============================== */

        echo '<script>
          swal({type:"success",title:"La entrada ha sido registrada correctamente",showConfirmButton:true,confirmButtonText:"Cerrar"})
          .then(function(result){if(result.value){window.location="productos"}});
        </script>';

      } else {
        echo '<script>
          swal({type:"error",title:"No se pudo registrar la entrada",showConfirmButton:true,confirmButtonText:"Cerrar"});
        </script>';
      }
    }
  }

  /* ============================
     SALIDA DE PRODUCTOS
     ============================ */
  static public function ctrCrearSalidasProductos(){

    if (!isset($_POST["nombreProductoSalida"])) {
      return;
    }

    $idProducto   = (int)$_POST['idSalida'];          // id_producto del modal
    $cantSalida   = (int)$_POST['salidaStock'];
    $categoria    = $_POST['categoriap'] ?? '';
    $nombreProd   = $_POST['nombreProductoSalida'] ?? '';

    /* Validación: tipo de salida */
    $tipoSalida = isset($_POST['tipo_salida']) ? strtolower(trim($_POST['tipo_salida'])) : '';
    $tiposPermitidos = array(
      'venta','reposicion','garantia','caducidad',
      'muestra','averia','consumo_interno','devolucion_proveedor'
    );
    if (!in_array($tipoSalida, $tiposPermitidos, true)) {
      echo '<script>
        swal({
          type:"error",
          title:"Tipo de salida inválido",
          text:"Seleccione un tipo válido.",
          showConfirmButton:true,
          confirmButtonText:"Cerrar"
        }).then(function(res){ if(res.value){ window.location="productos"; }});
      </script>';
      return;
    }

    $descripcionSalida = isset($_POST['descripcion_salida']) ? trim($_POST['descripcion_salida']) : '';

    // 1) Traer producto por su PK correcta
    $producto = ControladorProductos::ctrMostrarProductos("id_producto", $idProducto);
    if (!$producto) {
      echo '<script>
        swal({ type:"error", title:"Producto no encontrado", showConfirmButton:true, confirmButtonText:"Cerrar" })
        .then(function(res){ if(res.value){ window.location="productos"; }});
      </script>';
      return;
    }

    // 2) Validar stock suficiente
    $stockActual = (int)$producto["stock"];
    if ($cantSalida <= 0) {
      echo '<script>
        swal({ type:"error", title:"Cantidad de salida inválida", showConfirmButton:true, confirmButtonText:"Cerrar" })
        .then(function(res){ if(res.value){ window.location="productos"; }});
      </script>';
      return;
    }
    if ($cantSalida > $stockActual) {
      echo '<script>
        swal({ type:"error", title:"La salida no puede ser mayor que el stock", showConfirmButton:true, confirmButtonText:"Cerrar" })
        .then(function(res){ if(res.value){ window.location="productos"; }});
      </script>';
      return;
    }

    // 3) Registrar la salida en salidasp (incluye tipo y descripción)
    $tablaSalidas = "salidasp";
    $datosSalida = array(
      "categoriap"         => $categoria,
      "nombreProducto"     => $nombreProd,
      "salidap"            => $cantSalida,
      "tipo_salida"        => $tipoSalida,
      "descripcion_salida" => $descripcionSalida
    );
    $idSalida = ModeloProductos::mdlIngresarProductosSalidas($tablaSalidas, $datosSalida);
    if ($idSalida <= 0) {
      echo '<script>
        swal({ type:"error", title:"No se pudo registrar la salida", showConfirmButton:true, confirmButtonText:"Cerrar" });
      </script>';
      return;
    }

    // 4) Descontar stock del producto
    $stockOld  = $stockActual;
    $stockNew  = $stockActual - $cantSalida;
    $respStock = ModeloProductos::mdlActualizarProductosSalidas("productos", "stock", $idProducto, $stockNew);
    if ($respStock !== "ok") {
      echo '<script>
        swal({ type:"error", title:"No se pudo actualizar el stock", showConfirmButton:true, confirmButtonText:"Cerrar" });
      </script>';
      return;
    }

    // 5) Notificación si el stock quedó bajo
    if ($stockNew <= 10) {
      $tablaNotif = "notificacionesstock";
      $datosNotif = array(
        "idproducto" => $idProducto,
        "stock"      => $stockNew,
        "valorStock" => 1
      );
      if (class_exists('ModeloNotificaciones') && method_exists('ModeloNotificaciones','mdlIngresarNotificaciones')) {
        ModeloNotificaciones::mdlIngresarNotificaciones($tablaNotif, $datosNotif);
      }
    }

    /* ===== AUDITORÍA: SALIDA ===== */
    if (function_exists('audit_log')) {
      try {
        audit_log(
          'Salidas',
          'SALIDA',
          'salidasp',
          $idSalida,
          "Salida de {$cantSalida} del producto {$producto['nombre']} (tipo: {$tipoSalida})",
          ['stock' => $stockOld],
          ['stock' => $stockNew]
        );
      } catch (Throwable $e) { error_log('AUDIT SALIDA ERROR: '.$e->getMessage()); }
    }
    /* ============================= */

    // 6) OK UX
    echo '<script>
      swal({ type:"success", title:"La salida ha sido guardada", showConfirmButton:true, confirmButtonText:"Cerrar" })
      .then(function(res){ if(res.value){ window.location="productos"; }});
    </script>';
  }

  /* ======================================================
     Helper interno: verifica duplicidad de UPC (sin mdlExisteUPC)
     Usa mdlMostrarProductos y, si se edita, excluye el propio ID.
     ====================================================== */
  private static function upcDuplicadoSimple($upc, $excludeId = null) {
    $tabla = "productos";
    // Busca algún producto con ese UPC
    $dup = ModeloProductos::mdlMostrarProductos($tabla, "upc_producto", $upc);
    if (!$dup) return false; // no existe => no hay duplicado

    // Si no estamos en edición, cualquier registro coincide -> duplicado
    if ($excludeId === null) return true;

    // En edición, permitir si es el mismo ID; duplicado si es otro
    return ((int)$dup["id_producto"] !== (int)$excludeId);
  }

}
