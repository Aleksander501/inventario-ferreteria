<?php

require_once "conexion.php";

class ModeloEmpresa{



	static public function mdlMostrarEmpresa($tabla,$item,$valor){

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

	static public function mdlMostrarLogo($tabla){

		

			$stmt = Conexion::Conectar()->prepare("SELECT * FROM $tabla");

			$stmt->execute();

			return $stmt->fetch();


		$stmt->close();
		$stmt= null;



	}

	static public function mdlMostrarIcono($tabla){

		

			$stmt = Conexion::Conectar()->prepare("SELECT * FROM $tabla");

			$stmt->execute();

			return $stmt->fetch();


		$stmt->close();
		$stmt= null;



	}


	static public function mdlActualizarIcono($tabla,$id, $item, $valor){

		$stmt = Conexion::Conectar()->prepare("UPDATE  $tabla SET  $item = :$item  WHERE id_empresa = :id_empresa");


		$stmt->bindParam(":".$item, $valor,PDO::PARAM_STR);
		$stmt->bindParam(":id_empresa",$id,PDO::PARAM_INT);



	    if ($stmt->execute()) {


	    	return "ok";

	    }else{


	    	return "error";
	    }

	    $stmt->close();
	    $stmt = null;



	}


	
	static public function mdlActualizarLogo($tabla,$id, $item, $valor){

		$stmt = Conexion::Conectar()->prepare("UPDATE  $tabla SET  $item = :$item  WHERE id_empresa = :id_empresa");


		$stmt->bindParam(":".$item, $valor,PDO::PARAM_STR);
		$stmt->bindParam(":id_empresa",$id,PDO::PARAM_INT);



	    if ($stmt->execute()) {


	    	return "ok";

	    }else{


	    	return "error";
	    }

	    $stmt->close();
	    $stmt = null;



	} 


	static public function mdlEditarEmpresa($tabla,$datos){

		$stmt = Conexion::Conectar()->prepare("UPDATE  $tabla SET  nombre_empresa = :nombre_empresa, telefono =:telefono, sitioWeb= :sitioWeb, direccion = :direccion WHERE id_empresa = :id_empresa");


		$stmt->bindParam(":id_empresa",$datos["id_empresa"],PDO::PARAM_INT);

		$stmt->bindParam(":nombre_empresa",$datos["nombre_empresa"],PDO::PARAM_STR);
	    $stmt->bindParam(":telefono",$datos["telefono"],PDO::PARAM_STR);
	    $stmt->bindParam(":sitioWeb",$datos["sitioWeb"],PDO::PARAM_STR);
	    $stmt->bindParam(":direccion",$datos["direccion"],PDO::PARAM_STR);

	    if ($stmt->execute()) {


	    	return "ok";

	    }else{


	    	return "error";
	    }

	    $stmt->close();
	    $stmt = null;



	}



}