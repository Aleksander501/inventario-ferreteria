
<?php

require_once "conexion.php";

class ModeloProductos
{

	static public function mdlIngresarProductos($tabla, $datos)
	{

		$stmt = Conexion::Conectar()->prepare(
			"INSERT INTO $tabla
    (upc_producto, nombre, marca, categoria, descripcion_producto, unidad_medida,
     precio_compra, precio_venta1, precio_venta2, precio_venta3, stock,
     nombre_proveedor, tipo_proveedor)
     VALUES
    (:upc_producto, :nombre, :marca, :categoria, :descripcion_producto, :unidad_medida,
     :precio_compra, :precio_venta1, :precio_venta2, :precio_venta3, :stock,
     :nombre_proveedor, :tipo_proveedor)"
		);

		$stmt->bindParam(":upc_producto", $datos["upc_producto"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
		$stmt->bindParam(":categoria", $datos["categoria"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion_producto", $datos["descripcion_producto"], PDO::PARAM_STR);
		$stmt->bindParam(":unidad_medida", $datos["unidad_medida"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta1", $datos["precio_venta1"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta2", $datos["precio_venta2"], PDO::PARAM_STR); // OJO: usa la clave correcta de tu array ($datos["precio_venta2"])
		$stmt->bindParam(":precio_venta3", $datos["precio_venta3"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre_proveedor", $datos["nombre_proveedor"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_proveedor", $datos["tipo_proveedor"], PDO::PARAM_STR);



		if ($stmt->execute()) {


			return "ok";
		} else {


			return "error";
		}

		$stmt->close();
		$stmt = null;
	}



	public static function mdlIngresarProductosEntrada($tabla, $datos)
{
    try {
        // Usa la misma conexión de tu proyecto
        $pdo = Conexion::Conectar();

        $sql = "INSERT INTO $tabla (nombre_proveedor, tipo_proveedor, nombreProducto, entradap, fecha)
                VALUES (:nombre_proveedor, :tipo_proveedor, :nombreProducto, :entradap, NOW())";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":nombre_proveedor", $datos["nombre_proveedor"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_proveedor",   $datos["tipo_proveedor"],   PDO::PARAM_STR);
        $stmt->bindParam(":nombreProducto",   $datos["nombreProducto"],   PDO::PARAM_STR);
        $stmt->bindParam(":entradap",         $datos["entradap"],         PDO::PARAM_INT);

        if (!$stmt->execute()) {
            return 0; // falla el insert
        }

        // ← ID autoincrement de la entrada (id_entradap)
        return (int)$pdo->lastInsertId();

    } catch (Throwable $e) {
        error_log('ERR ENTRADA: '.$e->getMessage());
        return 0;
    }
}




	static public function mdlIngresarProductosSalidas($tabla, $datos)
{
    try {
        // 1) Toma el manejador PDO
        $pdo = Conexion::Conectar(); // ojo a la mayúscula: usa la misma que en tu clase

        // 2) Prepara el INSERT (mismas columnas que ya usas)
        $sql = "INSERT INTO $tabla (categoriap, nombreProducto, salidap, tipo_salida, descripcion_salida)
                VALUES (:categoriap, :nombreProducto, :salidap, :tipo_salida, :descripcion_salida)";
        $stmt = $pdo->prepare($sql);

        // 3) Bindea parámetros
        $stmt->bindParam(":categoriap",         $datos["categoriap"],         PDO::PARAM_STR);
        $stmt->bindParam(":nombreProducto",     $datos["nombreProducto"],     PDO::PARAM_STR);
        $stmt->bindParam(":salidap",            $datos["salidap"],            PDO::PARAM_INT);
        $stmt->bindParam(":tipo_salida",        $datos["tipo_salida"],        PDO::PARAM_STR);
        $stmt->bindParam(":descripcion_salida", $datos["descripcion_salida"], PDO::PARAM_STR);

        // 4) Ejecuta
        if (!$stmt->execute()) {
            // Inserción fallida
            return 0;
        }

        // 5) Devuelve el ID autoincrement generado (id_salidap)
        return (int)$pdo->lastInsertId();

    } catch (Throwable $e) {
        error_log('ERR SALIDA: '.$e->getMessage());
        return 0;
    }
}


	static public function mdlEditarProductos($tabla, $datos)
	{

		$stmt = Conexion::Conectar()->prepare(
			"UPDATE  $tabla SET
        upc_producto = :upc_producto,
        nombre = :nombre,
        marca = :marca,
        categoria = :categoria,
        descripcion_producto = :descripcion_producto,
        unidad_medida = :unidad_medida,
        precio_compra = :precio_compra,
        precio_venta1 = :precio_venta1,
        precio_venta2 = :precio_venta2,
        precio_venta3 = :precio_venta3,
        stock = :stock,
        nombre_proveedor = :nombre_proveedor,
        tipo_proveedor = :tipo_proveedor
     WHERE id_producto = :id_producto"
		);

		$stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
		$stmt->bindParam(":upc_producto", $datos["upc_producto"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
		$stmt->bindParam(":categoria", $datos["categoria"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion_producto", $datos["descripcion_producto"], PDO::PARAM_STR);
		$stmt->bindParam(":unidad_medida", $datos["unidad_medida"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta1", $datos["precio_venta1"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta2", $datos["precio_venta2"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta3", $datos["precio_venta3"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre_proveedor", $datos["nombre_proveedor"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_proveedor", $datos["tipo_proveedor"], PDO::PARAM_STR);



		if ($stmt->execute()) {


			return "ok";
		} else {


			return "error";
		}

		$stmt->close();
		$stmt = null;
	}





	static public function mdlMostrarProductos($tabla, $item, $valor)
	{

		if ($item != null) {

			$stmt = Conexion::Conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();
		} else {


			$stmt = Conexion::Conectar()->prepare("SELECT * FROM $tabla");

			$stmt->execute();

			return $stmt->fetchAll();
		}

		$stmt->close();
		$stmt = null;
	}


	static public function mdlMostrarProductosEntradas($tabla, $item, $valor)
	{

		if ($item != null) {

			$stmt = Conexion::Conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();
		} else {


			$stmt = Conexion::Conectar()->prepare("SELECT * FROM $tabla");

			$stmt->execute();

			return $stmt->fetchAll();
		}

		$stmt->close();
		$stmt = null;
	}



	static public function mdlMostrarProductosSalidas($tabla, $item, $valor)
	{

		if ($item != null) {

			$stmt = Conexion::Conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();
		} else {


			$stmt = Conexion::Conectar()->prepare("SELECT * FROM $tabla");

			$stmt->execute();

			return $stmt->fetchAll();
		}

		$stmt->close();
		$stmt = null;
	}








	static public function mdlBorrarProductos($tabla, $datos)
	{

		$stmt = Conexion::Conectar()->prepare("DELETE FROM $tabla WHERE id_producto = :id_producto");

		$stmt->bindParam(":id_producto", $datos, PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";
		} else {

			return "error";
		}

		$stmt->close();
		$stmt = null;
	}



	static public function mdlActualizarProductosEntrada($tabla, $id_producto, $nuevoStock)
	{

		$stmt = Conexion::Conectar()->prepare("UPDATE $tabla SET stock = :stock WHERE id_producto = :id_producto");

		$stmt->bindParam(":stock", $nuevoStock, PDO::PARAM_INT);
		$stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;
	}




	static public function mdlActualizarProductosSalidas($tablaDos, $itemDos, $valor, $resultado)
	{

		$stmt = Conexion::Conectar()->prepare("UPDATE  $tablaDos SET $itemDos = :$itemDos WHERE id_producto = :id_producto");



		$stmt->bindParam(":" . $itemDos, $resultado, PDO::PARAM_STR);



		$stmt->bindParam(":id_producto", $valor, PDO::PARAM_INT);




		if ($stmt->execute()) {


			return "ok";
		} else {


			return "error";
		}
		$stmt->close();
		$stmt = null;
	}

	/* Verificar si un UPC existe (opcionalmente excluyendo un id) */
static public function mdlExisteUPC($tabla, $upc, $excludeId = null) {
    $sql = "SELECT id_producto FROM $tabla WHERE upc_producto = :upc";
    if (!empty($excludeId)) $sql .= " AND id_producto <> :id";

    $stmt = Conexion::Conectar()->prepare($sql);
    $stmt->bindParam(":upc", $upc, PDO::PARAM_STR);
    if (!empty($excludeId)) $stmt->bindParam(":id", $excludeId, PDO::PARAM_INT);

    $stmt->execute();
    $existe = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = null;
    return $existe ? true : false;
}

/* Verificar si el producto tiene movimientos (entradas o salidas) por NOMBRE */
static public function mdlTieneMovimientosPorNombre($nombreProducto) {
    // Tablas reales en tu proyecto:
    $tablaEntradas = "entradas_productos"; // columnas: nombre_proveedor, tipo_proveedor, nombreProducto, entradap
    $tablaSalidas  = "salidasp";           // columnas: categoriap, nombreProducto, salidap

    $db = Conexion::Conectar();

    // Entradas
    $stmtE = $db->prepare("SELECT COUNT(*) FROM $tablaEntradas WHERE nombreProducto = :nombre");
    $stmtE->bindParam(":nombre", $nombreProducto, PDO::PARAM_STR);
    $stmtE->execute();
    $countEntradas = (int)$stmtE->fetchColumn();
    $stmtE = null;

    // Salidas
    $stmtS = $db->prepare("SELECT COUNT(*) FROM $tablaSalidas WHERE nombreProducto = :nombre");
    $stmtS->bindParam(":nombre", $nombreProducto, PDO::PARAM_STR);
    $stmtS->execute();
    $countSalidas = (int)$stmtS->fetchColumn();
    $stmtS = null;

    return ($countEntradas + $countSalidas) > 0;
}

/* Propagar cambio de nombre a submódulos (sin transacción) */
static public function mdlPropagarNombreProducto($nombreAnterior, $nombreNuevo) {
    $db = Conexion::Conectar();

    // entradas_productos.nombreProducto
    $stmtE = $db->prepare("UPDATE entradas_productos SET nombreProducto = :nuevo WHERE nombreProducto = :anterior");
    $stmtE->bindParam(":nuevo", $nombreNuevo, PDO::PARAM_STR);
    $stmtE->bindParam(":anterior", $nombreAnterior, PDO::PARAM_STR);
    if (!$stmtE->execute()) { $stmtE = null; return "error"; }
    $stmtE = null;

    // salidasp.nombreProducto
    $stmtS = $db->prepare("UPDATE salidasp SET nombreProducto = :nuevo WHERE nombreProducto = :anterior");
    $stmtS->bindParam(":nuevo", $nombreNuevo, PDO::PARAM_STR);
    $stmtS->bindParam(":anterior", $nombreAnterior, PDO::PARAM_STR);
    if (!$stmtS->execute()) { $stmtS = null; return "error"; }
    $stmtS = null;

    return "ok";
}


}
