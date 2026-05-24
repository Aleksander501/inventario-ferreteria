<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/../controlador/recuperar.controlador.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["ok"=>false, "msg"=>"Método no permitido"]);
  exit;
}

try {
  $selector = $_POST['selector'] ?? '';
  $token    = $_POST['token'] ?? '';
  $resetId  = isset($_POST['reset_id']) ? (int)$_POST['reset_id'] : 0;
  $pwd      = $_POST['pwd'] ?? '';
  $pwd2     = $_POST['pwd2'] ?? '';

  $out = ControladorRecuperar::ctrGuardarNueva($selector, $token, $resetId, $pwd, $pwd2);
  echo json_encode($out);
} catch (Throwable $e) {
  error_log("restablecer.ajax error: ".$e->getMessage());
  echo json_encode(["ok"=>false, "msg"=>"No se pudo completar la operación."]);
}
