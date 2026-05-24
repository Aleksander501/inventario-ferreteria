<?php

require_once "conexion.php";

class ModeloProveedores{

	static public function mdlIngresarProveedores($tabla,$datos){

		$stmt = Conexion::Conectar()->prepare("INSERT INTO $tabla (nombre_proveedor, tipo_proveedor, correo,telefono, direccion) VALUES (:nombre_proveedor, :tipo_proveedor,:correo, :telefono, :direccion)");

		$stmt->bindParam(":nombre_proveedor",$datos["nombre_proveedor"],PDO::PARAM_STR);
	    $stmt->bindParam(":tipo_proveedor",$datos["tipo_proveedor"],PDO::PARAM_STR);
	    $stmt->bindParam(":correo",$datos["correo"],PDO::PARAM_STR);
	    $stmt->bindParam(":telefono",$datos["telefono"],PDO::PARAM_STR);
	    $stmt->bindParam(":direccion",$datos["direccion"],PDO::PARAM_STR);


	    if ($stmt->execute()) {


	    	return "ok";

	    }else{


	    	return "error";
	    }

	    $stmt->close();
	    $stmt = null;



	}


	static public function mdlEditarProveedores($tabla,$datos){

		$stmt = Conexion::Conectar()->prepare("UPDATE  $tabla SET  nombre_proveedor = :nombre_proveedor, tipo_proveedor =:tipo_proveedor, correo= :correo, telefono = :telefono, direccion = :direccion WHERE id_proveedor = :id_proveedor");


		$stmt->bindParam(":id_proveedor",$datos["id_proveedor"],PDO::PARAM_INT);

		
		$stmt->bindParam(":nombre_proveedor",$datos["nombre_proveedor"],PDO::PARAM_STR);
	    $stmt->bindParam(":tipo_proveedor",$datos["tipo_proveedor"],PDO::PARAM_STR);
	    $stmt->bindParam(":correo",$datos["correo"],PDO::PARAM_STR);
	    $stmt->bindParam(":telefono",$datos["telefono"],PDO::PARAM_STR);
	    $stmt->bindParam(":direccion",$datos["direccion"],PDO::PARAM_STR);

	    if ($stmt->execute()) {


	    	return "ok";

	    }else{


	    	return "error";
	    }

	    $stmt->close();
	    $stmt = null;



	}





	static public function mdlMostrarProveedores($tabla,$item,$valor){

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

	static public function mdlBorrarProveedores($tabla,$datos){

		$stmt = Conexion::Conectar()->prepare("DELETE FROM $tabla WHERE id_proveedor = :id_proveedor");

		$stmt->bindParam(":id_proveedor", $datos, PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";

		}else{

		return "error";


		}

		$stmt->close();
		$stmt = null;


	}

	/* ===========================
   Validar duplicado (crear/editar) - sensible a mayúsculas y tildes
=========================== */
public static function mdlExisteNombreProveedor($tabla, $nombre, $excluirId = null){
    $sql = "SELECT COUNT(*) FROM $tabla WHERE BINARY nombre_proveedor = :nombre";
    if (!empty($excluirId)) {
        $sql .= " AND id_proveedor <> :id";
    }
    $stmt = Conexion::Conectar()->prepare($sql);
    $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
    if (!empty($excluirId)) {
        $stmt->bindParam(":id", $excluirId, PDO::PARAM_INT);
    }
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

/* ===========================
   Verificar si tiene entradas asociadas
=========================== */
public static function mdlProveedorTieneEntradas($tablaEntradas, $nombreProveedor){
    $sql = "SELECT COUNT(*) FROM $tablaEntradas WHERE BINARY nombre_proveedor = :nombre";
    $stmt = Conexion::Conectar()->prepare($sql);
    $stmt->bindParam(":nombre", $nombreProveedor, PDO::PARAM_STR);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

/* ===========================
   Propagar nombre a entradas_productos
=========================== */
public static function mdlPropagarNombreProveedorEnEntradas($tablaEntradas, $nombreAnterior, $nombreNuevo){
    $sql = "UPDATE $tablaEntradas
               SET nombre_proveedor = :nuevo
             WHERE BINARY nombre_proveedor = :anterior";
    $stmt = Conexion::Conectar()->prepare($sql);
    $stmt->bindParam(":nuevo", $nombreNuevo, PDO::PARAM_STR);
    $stmt->bindParam(":anterior", $nombreAnterior, PDO::PARAM_STR);
    $stmt->execute();
    return "ok";
}

public static function mdlPropagarProveedorEnEntradas($tablaEntradas, $nombreAnterior, $nombreNuevo, $tipoNuevo){
    $sql = "UPDATE $tablaEntradas
               SET nombre_proveedor = :nuevoNombre,
                   tipo_proveedor   = :nuevoTipo
             WHERE BINARY nombre_proveedor = :anteriorNombre";
    $stmt = Conexion::Conectar()->prepare($sql);
    $stmt->bindParam(":nuevoNombre",    $nombreNuevo,    PDO::PARAM_STR);
    $stmt->bindParam(":nuevoTipo",      $tipoNuevo,      PDO::PARAM_STR);
    $stmt->bindParam(":anteriorNombre", $nombreAnterior, PDO::PARAM_STR);
    $stmt->execute();
    return "ok";
}

public static function mdlPropagarSoloTipoProveedorEnEntradas($tablaEntradas, $nombreActual, $tipoNuevo){
    $sql = "UPDATE $tablaEntradas
               SET tipo_proveedor = :tipo
             WHERE BINARY nombre_proveedor = :nombre";
    $stmt = Conexion::Conectar()->prepare($sql);
    $stmt->bindParam(":tipo",   $tipoNuevo,   PDO::PARAM_STR);
    $stmt->bindParam(":nombre", $nombreActual, PDO::PARAM_STR);
    $stmt->execute();
    return "ok";
}





}