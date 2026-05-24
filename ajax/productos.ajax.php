<?php
require_once "../controlador/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

class AjaxProductos{
    public $idProducto;

    public function ajaxEditarProductos(){
        $item  = "id_producto";
        $valor = $this->idProducto;

        $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($respuesta ?: []);
        exit;
    }
}

/**
 * Editar Productos
 * Acepta tanto 'idProducto' (singular) como 'idProductos' (plural) por compatibilidad.
 */
if (isset($_POST['idProducto']) || isset($_POST['idProductos'])) {
    $editar = new AjaxProductos();
    $editar->idProducto = $_POST['idProducto'] ?? $_POST['idProductos'];
    $editar->ajaxEditarProductos();
}
class AjaxProductosPlus {

    public $idCheckMov;
    public $upc;
    public $idProducto; // para edición

    public function ajaxTieneMovimientos() {
        // Buscar el nombre del producto por id para chequear movimientos
        $prod = ModeloProductos::mdlMostrarProductos("productos", "id_producto", $this->idCheckMov);
        $tiene = false;
        if ($prod && isset($prod["nombre"])) {
            $tiene = ModeloProductos::mdlTieneMovimientosPorNombre($prod["nombre"]);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array("tieneMovimientos" => $tiene));
        exit;
    }

    public function ajaxExisteUPC() {
        $existe = ModeloProductos::mdlExisteUPC("productos", $this->upc, $this->idProducto ?: null);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array("exists" => $existe));
        exit;
    }
}

/* Chequeo de movimientos antes de eliminar (llamado desde JS) */
if (isset($_POST["idCheckMov"])) {
    $ajax = new AjaxProductosPlus();
    $ajax->idCheckMov = (int)$_POST["idCheckMov"];
    $ajax->ajaxTieneMovimientos();
}

/* Validación de UPC (opcional para validar en vivo desde el form) */
if (isset($_POST["upc"])) {
    $ajax = new AjaxProductosPlus();
    $ajax->upc = trim($_POST["upc"]);
    if (isset($_POST["idProducto"])) $ajax->idProducto = (int)$_POST["idProducto"];
    $ajax->ajaxExisteUPC();
}
