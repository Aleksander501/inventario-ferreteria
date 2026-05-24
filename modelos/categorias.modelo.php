<?php
require_once "conexion.php";

class ModeloCategorias{

	/* =============================================
       INGRESAR CATEGORIA
    ============================================= */
	static public function mdlIngresarCategorias($tabla,$datos){
		$stmt = Conexion::Conectar()->prepare("INSERT INTO $tabla (nombre_categoria) VALUES (:nombre_categoria)");
		$stmt->bindParam(":nombre_categoria",$datos["nombre_categoria"],PDO::PARAM_STR);
	    if ($stmt->execute()) { return "ok"; }
	    else { return "error"; }
	    $stmt->close();
	    $stmt = null;
	}

	/* =============================================
       EDITAR CATEGORIA
    ============================================= */
	static public function mdlEditarCategorias($tabla,$datos){
		$stmt = Conexion::Conectar()->prepare("UPDATE $tabla SET nombre_categoria = :nombre_categoria WHERE id_categorias = :id_categorias");
		$stmt->bindParam(":id_categorias",$datos["id_categorias"],PDO::PARAM_INT);
		$stmt->bindParam(":nombre_categoria",$datos["nombre_categoria"],PDO::PARAM_STR);
	    if ($stmt->execute()) { return "ok"; }
	    else { return "error"; }
	    $stmt->close();
	    $stmt = null;
	}

	/* =============================================
       MOSTRAR CATEGORIAS
    ============================================= */
	static public function mdlMostrarCategorias($tabla,$item,$valor){
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

	/* =============================================
       BORRAR CATEGORIA CON VALIDACION
    ============================================= */
	static public function mdlBorrarCategorias($tabla, $datos){
	    // Verificar si existe producto con esa categoría
	    $stmt = Conexion::Conectar()->prepare("SELECT COUNT(*) FROM productos WHERE categoria = (
	        SELECT nombre_categoria FROM categorias WHERE id_categorias = :id_categorias
	    )");
	    $stmt->bindParam(":id_categorias", $datos, PDO::PARAM_INT);
	    $stmt->execute();
	    $existe = $stmt->fetchColumn();

	    if ($existe > 0) {
	        return "error_relacion";
	    }

	    // Eliminar
	    $stmt = Conexion::Conectar()->prepare("DELETE FROM $tabla WHERE id_categorias = :id_categorias");
	    $stmt->bindParam(":id_categorias", $datos, PDO::PARAM_INT);
	    if ($stmt->execute()) { return "ok"; }
	    else { return "error"; }
	    $stmt->close();
	    $stmt = null;
	}

	/* =============================================
       NUEVO: PROPAGAR CAMBIO A PRODUCTOS
       (relación implícita por texto, match estricto: mayúsculas/tildes)
    ============================================= */
	public static function mdlPropagarCambioCategoriaEnProductos($tablaProductos, $nombreAnterior, $nombreNuevo){
    // Comparación sensible usando BINARY (independiente del collation de la columna)
    $sql = "UPDATE $tablaProductos
               SET categoria = :nuevo
             WHERE BINARY categoria = BINARY :anterior";

    $stmt = Conexion::Conectar()->prepare($sql);
    $stmt->bindParam(":nuevo",    $nombreNuevo,    PDO::PARAM_STR);
    $stmt->bindParam(":anterior", $nombreAnterior, PDO::PARAM_STR);
    $stmt->execute();

    return "ok";
}

/* =============================================
       NORMALIZAR TEXTO (mayúsculas, minúsculas, tildes)
    ============================================= */
    public static function normalizarTexto($texto) {
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = strtr($texto, [
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u',
            'Á'=>'a', 'É'=>'e', 'Í'=>'i', 'Ó'=>'o', 'Ú'=>'u',
            'ä'=>'a', 'ë'=>'e', 'ï'=>'i', 'ö'=>'o', 'ü'=>'u',
            'Ä'=>'a', 'Ë'=>'e', 'Ï'=>'i', 'Ö'=>'o', 'Ü'=>'u',
            'ñ'=>'n', 'Ñ'=>'n'
        ]);
        $texto = preg_replace('/\s+/', ' ', trim($texto));
        return $texto;
    }

 /* =============================================
       VALIDAR DUPLICADO (EDITAR/CREAR) 
    ============================================= */
    public static function mdlExisteNombreCategoria($tabla, $nombre, $excluirId = null){
        // Traer todas las categorías (opcionalmente excluyendo la actual)
        $sql = "SELECT id_categorias, nombre_categoria FROM $tabla";
        if (!empty($excluirId)) {
            $sql .= " WHERE id_categorias <> :id";
        }

        $stmt = Conexion::Conectar()->prepare($sql);

        if (!empty($excluirId)) {
            $stmt->bindParam(":id", $excluirId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalizar el nombre a comparar
        $nombreNormalizado = self::normalizarTexto($nombre);

        // Comparar con cada categoría existente
        foreach ($categorias as $cat) {
            if (self::normalizarTexto($cat['nombre_categoria']) === $nombreNormalizado) {
                return true; // duplicado encontrado
            }
        }

        return false; // no hay duplicado
    }

}
