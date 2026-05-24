<?php

require_once "../controlador/empresa.controlador.php";
require_once "../modelos/empresa.modelo.php";


class AjaxLogo{

	public $imagen;


	
	public function ajaxCambiarLogo(){

		$item = "logo";
		$valor = $this->imagen;


		$respuesta = ControladorEmpresa::ctrActualizarLogo($item,$valor);

		echo ($respuesta);

		
	}
}


/**
 *   cambiar  logo
 */

if (isset($_FILES['imagen'])) {


	$editar = new AjaxLogo();
	$editar->imagen = $_FILES['imagen'];
	$editar->ajaxCambiarLogo();
}