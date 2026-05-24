<?php

require_once "../controlador/empresa.controlador.php";
require_once "../modelos/empresa.modelo.php";


class AjaxIcono{

	public $imagen;


	
	public function ajaxCambiarIcono(){

		$item = "icono";
		$valor = $this->imagen;


		$respuesta = ControladorEmpresa::ctrActualizarIcono($item,$valor);

		echo ($respuesta);

		
	}
}


/**
 *   cambiar  logo
 */

if (isset($_FILES['imagen'])) {


	$editar = new AjaxIcono();
	$editar->imagen = $_FILES['imagen'];
	$editar->ajaxCambiarIcono();
}