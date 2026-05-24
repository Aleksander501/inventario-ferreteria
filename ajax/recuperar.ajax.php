<?php
header('Content-Type: application/json; charset=utf-8');
require_once "../controlador/recuperar.controlador.php";

try {
  $correo = $_POST["email"] ?? "";
  $out = ControladorRecuperar::ctrSolicitarReset($correo);
  echo json_encode($out);
} catch (Throwable $e) {
  echo json_encode(["ok"=>true,"message"=>ControladorRecuperar::mensajeNeutro()]);
}
