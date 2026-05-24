<?php

require_once "../controlador/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";


class Ajaxusuarios{

		public $idUsuario;

	
	public function ajaxEditarUsuarios(){

		$item = "id_usuario";
		$valor = $this->idUsuario;


		$respuesta = ControladorUsuarios::ctrMostrarUsuarios($item,$valor);

		echo json_encode($respuesta);

		
	}
}


/**
 *   Editar Usuarios
 */

if (isset($_POST['idUsuario'])) {


	$editar = new Ajaxusuarios();
	$editar->idUsuario = $_POST['idUsuario'];
	$editar->ajaxEditarUsuarios();
}