<?php
require_once "conexion.php";

class ModeloUsuarios{

  /* =========================
   *  Crear usuario
   * ========================= */
  static public function mdlIngresarUsuarios($tabla, $datos){
    $stmt = Conexion::Conectar()->prepare(
      "INSERT INTO $tabla (nombre, usuario, correo, password, perfil)
       VALUES (:nombre, :usuario, :correo, :password, :perfil)"
    );

    $stmt->bindParam(":nombre",   $datos["nombre"],   PDO::PARAM_STR);
    $stmt->bindParam(":usuario",  $datos["usuario"],  PDO::PARAM_STR);
    $stmt->bindParam(":correo",   $datos["correo"],   PDO::PARAM_STR);
    $stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR); // debe venir ya con password_hash()
    $stmt->bindParam(":perfil",   $datos["perfil"],   PDO::PARAM_STR);

    $ok = $stmt->execute() ? "ok" : "error";
    $stmt = null;
    return $ok;
  }

  /* =========================
   *  Editar usuario
   * ========================= */
  static public function mdlEditarUsuarios($tabla, $datos){
    $stmt = Conexion::Conectar()->prepare(
      "UPDATE $tabla
         SET nombre = :nombre,
             usuario = :usuario,
             correo = :correo,
             password = :password,
             perfil = :perfil
       WHERE id_usuario = :id_usuario"
    );

    $stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
    $stmt->bindParam(":nombre",     $datos["nombre"],     PDO::PARAM_STR);
    $stmt->bindParam(":usuario",    $datos["usuario"],    PDO::PARAM_STR);
    $stmt->bindParam(":correo",     $datos["correo"],     PDO::PARAM_STR);
    $stmt->bindParam(":password",   $datos["password"],   PDO::PARAM_STR);
    $stmt->bindParam(":perfil",     $datos["perfil"],     PDO::PARAM_STR);

    $ok = $stmt->execute() ? "ok" : "error";
    $stmt = null;
    return $ok;
  }

  /* =========================
   *  Mostrar usuarios
   * ========================= */
  static public function mdlMostrarUsuarios($tabla, $item, $valor){
    if ($item != null){
      $stmt = Conexion::Conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
      $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
      $stmt->execute();
      $r = $stmt->fetch();
      $stmt = null;
      return $r;
    } else {
      $stmt = Conexion::Conectar()->prepare("SELECT * FROM $tabla");
      $stmt->execute();
      $r = $stmt->fetchAll();
      $stmt = null;
      return $r;
    }
  }

  /* =========================
   *  Borrar usuario
   * ========================= */
  static public function mdlBorrarUsuarios($tabla, $idUsuario){
    $stmt = Conexion::Conectar()->prepare("DELETE FROM $tabla WHERE id_usuario = :id_usuario");
    $stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
    $ok = $stmt->execute() ? "ok" : "error";
    $stmt = null;
    return $ok;
  }

  /* =========================================================
   *  NUEVOS MÉTODOS para Recuperación de Contraseña
   * ========================================================= */

  /* Buscar por correo (columna `correo`) */
  static public function mdlBuscarPorCorreo($correo){
  $stmt = Conexion::Conectar()->prepare(
    // comparación insensible a mayúsculas
    "SELECT id_usuario, correo
       FROM usuarios
      WHERE LOWER(correo) = LOWER(:correo)
      LIMIT 1"
  );
  $stmt->bindParam(":correo", $correo, PDO::PARAM_STR);
  $stmt->execute();
  $r = $stmt->fetch();
  $stmt = null;
  return $r ?: null;
}

  /* Actualizar hash de contraseña (columna `password`) */
  static public function mdlActualizarPasswordHash($idUsuario, $hash){
    $stmt = Conexion::Conectar()->prepare(
      "UPDATE usuarios
          SET password = :hash
        WHERE id_usuario = :id
        LIMIT 1"
    );
    $stmt->bindParam(":hash", $hash, PDO::PARAM_STR);
    $stmt->bindParam(":id",   $idUsuario, PDO::PARAM_INT);
    $ok = $stmt->execute();
    $stmt = null;
    return $ok;
  }

  /* Buscar por ID */
static public function mdlBuscarPorId($idUsuario){
  $stmt = Conexion::Conectar()->prepare(
    "SELECT id_usuario, correo FROM usuarios WHERE id_usuario = :id LIMIT 1"
  );
  $stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
  $stmt->execute();
  $r = $stmt->fetch();
  $stmt = null;
  return $r ?: null;
}

  
}
