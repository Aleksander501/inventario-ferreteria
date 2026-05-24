<?php

require_once "../modelos/notificaciones.modelo.php";


class AjaxNotificaciones{

		public $item;

	
	public function ajaxEditarNotificaciones(){

		$item = $this->item;
		$valor = 0;


		$respuesta = ModeloNotificaciones::mdlActualizarNotificaciones("notificacionesstock",$item,$valor);

		echo $respuesta;

		
	}
}



if (isset($_POST['item'])) {


	$editar = new AjaxNotificaciones();
	$editar->item = $_POST['item'];
	$editar->ajaxEditarNotificaciones();
}