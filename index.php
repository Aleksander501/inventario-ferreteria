<?php

// ----- ENTORNO (cambia a false en producción) -----
define('APP_DEBUG', false); // true solo cuando depuras

date_default_timezone_set('America/El_Salvador');

// Sesión (solo si no está activa)
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

// Manejo de errores
if (APP_DEBUG) {
  error_reporting(E_ALL);
  ini_set('display_errors', '1');   // Muestra errores en pantalla SOLO en dev
} else {
  // En producción: no mostrar en pantalla, loguear a archivo
  error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
  ini_set('display_errors', '0');
  ini_set('log_errors', '1');
  ini_set('error_log', __DIR__ . '/logs/php_error.log'); // crea /logs si no existe
}

require_once "controlador/plantilla.controlador.php";
require_once "controlador/usuarios.controlador.php";
require_once "controlador/empresa.controlador.php";
require_once "controlador/categorias.controlador.php";
require_once "controlador/proveedores.controlador.php";
require_once "controlador/productos.controlador.php";
require_once "controlador/notificaciones.controlador.php";
require_once "controlador/recuperar.controlador.php";
require_once "controlador/auditoria.controlador.php";


require_once "modelos/usuarios.modelo.php";
require_once "modelos/empresa.modelo.php";
require_once "modelos/categorias.modelo.php";
require_once "modelos/proveedores.modelo.php";
require_once "modelos/productos.modelo.php";
require_once "modelos/notificaciones.modelo.php";
require_once "modelos/mailer.php";
require_once "modelos/password_resets.modelo.php";
require_once "modelos/auditoria.modelo.php";


// **IMPORTANTE**: asegúrate de cargar tu clase de conexión
require_once "modelos/conexion.php";                       // ⬅️

// **Helper global de Auditoría** (la ruta puede variar según tu árbol)
require_once __DIR__ . "/extensiones/helpers/auditoria.helper.php"; // ⬅️



    $plantilla = new ControladorPlantilla();
    $plantilla -> ctrPlantilla();
?>