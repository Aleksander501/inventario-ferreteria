<?php
require_once "conexion.php";

class ModeloPasswordResets {

  /* Crear solicitud */
  static public function mdlCrear($userId, $selector, $verifierHash, $expiresAt, $ip){
  $sql = "INSERT INTO password_resets
          (user_id, selector, verifier_hash, expires_at, requested_ip)
          VALUES (:uid, :sel, :vh, :exp, :ip)";
  $stmt = Conexion::Conectar()->prepare($sql);
  $stmt->bindParam(":uid", $userId, PDO::PARAM_INT);
  $stmt->bindParam(":sel", $selector, PDO::PARAM_STR);
  $stmt->bindParam(":vh",  $verifierHash, PDO::PARAM_STR);
  $stmt->bindParam(":exp", $expiresAt, PDO::PARAM_STR);
  $stmt->bindParam(":ip",  $ip, PDO::PARAM_STR);
  $ok = $stmt->execute();
  $stmt = null;
  return $ok;
}

  /* Buscar por selector */
  static public function mdlBuscarPorSelector($selector){
    $stmt = Conexion::Conectar()->prepare("SELECT * FROM password_resets WHERE selector = :s LIMIT 1");
    $stmt->bindParam(":s", $selector, PDO::PARAM_STR);
    $stmt->execute();
    $r = $stmt->fetch();
    $stmt = null;
    return $r ?: null;
  }

  /* Buscar por id + selector */
  static public function mdlBuscarPorIdYSelector($id, $selector){
    $stmt = Conexion::Conectar()->prepare("SELECT * FROM password_resets WHERE id = :id AND selector = :s LIMIT 1");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->bindParam(":s",  $selector, PDO::PARAM_STR);
    $stmt->execute();
    $r = $stmt->fetch();
    $stmt = null;
    return $r ?: null;
  }

  /* Marcar como usado */
  static public function mdlMarcarUsado($id){
    $stmt = Conexion::Conectar()->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = :id LIMIT 1");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt = null;
  }

  /* Limpiar pendientes del usuario */
  static public function mdlEliminarPendientesPorUsuario($userId){
    $stmt = Conexion::Conectar()->prepare("DELETE FROM password_resets WHERE user_id = :u AND used_at IS NULL");
    $stmt->bindParam(":u", $userId, PDO::PARAM_INT);
    $stmt->execute();
    $stmt = null;
  }

  static public function mdlEliminarExpirados(){
  $stmt = Conexion::Conectar()->prepare(
    "DELETE FROM password_resets WHERE expires_at < UTC_TIMESTAMP() OR used_at IS NOT NULL"
  );
  $ok = $stmt->execute();
  $stmt = null;
  return $ok;
}

static public function mdlEliminarPorSelector($selector){
  $stmt = Conexion::Conectar()->prepare("DELETE FROM password_resets WHERE selector = :s");
  $stmt->bindParam(":s", $selector, PDO::PARAM_STR);
  $ok = $stmt->execute();
  $stmt = null;
  return $ok;
}
}
