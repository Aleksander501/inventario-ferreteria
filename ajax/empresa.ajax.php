<?php

require_once "../controlador/empresa.controlador.php";
require_once "../modelos/empresa.modelo.php";


class AjaxEmpresa{

		public $idEmpresa;

	
	public function ajaxEditarEmpresa(){

		$item = "id_empresa";
		$valor = $this->idEmpresa;


		$respuesta = ControladorEmpresa::ctrMostrarEmpresa($item,$valor);

		echo json_encode($respuesta);

		
	}
}


/**
 *   Editar Usuarios
 */

if (isset($_POST['idEmpresa'])) {


	$editar = new AjaxEmpresa();
	$editar->idEmpresa = $_POST['idEmpresa'];
	$editar->ajaxEditarEmpresa();
}