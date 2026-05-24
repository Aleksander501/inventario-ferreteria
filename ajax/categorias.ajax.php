<?php
require_once "../controlador/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

class AjaxCategorias{
	public $idCategoria;

	public function ajaxEditarCategorias(){
		$item  = "id_categorias";
		$valor = $this->idCategoria;
		$respuesta = ControladorCategorias::ctrMostrarCategorias($item,$valor);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($respuesta ?: []);
		exit;
	}
}

/* Editar categoria */
if (isset($_POST['id_categorias'])) {
	$editar = new AjaxCategorias();
	$editar->idCategoria = $_POST['id_categorias'];
	$editar->ajaxEditarCategorias();
}
