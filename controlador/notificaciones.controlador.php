<?php

class ControladorNotificaciones{

	static public function ctrMostrarNotificaciones($item,$valor){

		$tabla = "notificacionesstock";

		$respuesta = ModeloNotificaciones::mdlMostrarNotificaciones($tabla,$item,$valor);

		return $respuesta;




	}



	static public function ctrSumaNotificaciones(){

		$tabla = "notificacionesstock";

		$respuesta = ModeloNotificaciones::mdlSumaNotificaciones($tabla);

		return $respuesta;




	}


}