<?php

require_once "conexion.php";

class ModeloNotificaciones{

	static public function mdlIngresarNotificaciones($tablaDos,$datosDos){

		$stmt = Conexion::Conectar()->prepare("INSERT INTO $tablaDos (idproducto,stock,valorStock) VALUES (:idproducto, :stock,:valorStock)");

		$stmt->bindParam(":idproducto",$datosDos["idproducto"],PDO::PARAM_INT);
	    $stmt->bindParam(":stock",$datosDos["stock"],PDO::PARAM_INT);
	    $stmt->bindParam(":valorStock",$datosDos["valorStock"],PDO::PARAM_INT);

	   

	    if ($stmt->execute()) {


	    	return "ok";

	    }else{


	    	return "error";
	    }

	    $stmt->close();
	    $stmt = null;



	}


	



	static public function mdlMostrarNotificaciones($tabla,$item,$valor){

		if ($item != null ) {

			$stmt = Conexion::Conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt->bindParam(":".$item,$valor,PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();



			
		}else{


			$stmt = Conexion::Conectar()->prepare("SELECT * FROM $tabla");

			$stmt->execute();

			return $stmt->fetchAll();

		}

		$stmt->close();
		$stmt= null;



	}



	static public function mdlSumaNotificaciones($tabla){

	
$stmt = Conexion::Conectar()->prepare("SELECT SUM(valorStock) as totalvalor FROM $tabla");

		

		$stmt->execute();

		return $stmt->fetch();



	

		$stmt->close();
		$stmt= null;



	}



	static public function mdlActualizarNotificaciones($tabla,$item,$valor){

		$stmt = Conexion::Conectar()->prepare("UPDATE  $tabla SET  $item = :$item ");


		$stmt->bindParam(":".$item,$valor,PDO::PARAM_INT);

	   

	    if ($stmt->execute()) {


	    	return "ok";

	    }else{


	    	return "error";
	    }

	    $stmt->close();
	    $stmt = null;



	}

}