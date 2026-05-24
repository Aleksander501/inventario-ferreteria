<?php
require_once __DIR__ . "/../modelos/usuarios.modelo.php";
require_once __DIR__ . "/../modelos/password_resets.modelo.php";
require_once __DIR__ . "/../modelos/mailer.php";
require_once __DIR__ . "/../modelos/conexion.php";

class ControladorRecuperar {

  /** Configuración */
  private const RESET_EXP_MINUTES = 30;   // vigencia del enlace
  private const RATELIMIT_SECONDS = 60;   // 1 solicitud por IP / minuto
  private const RATELIMIT_PREFIX  = 'pwdreset_';

  /** Mensaje neutro (no filtra existencia) */
  public static function mensajeNeutro(): string {
    return "Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.";
  }

  /** Base URL robusta (corrige si el entrypoint es /ajax/...) */
  private static function baseUrl(): string {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme  = $isHttps ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Posibles valores:
    //  - /inventario-ferreteria/index.php
    //  - /inventario-ferreteria/ajax/recuperar.ajax.php
    $dir = rtrim(dirname($_SERVER['PHP_SELF'] ?? ''), '/\\');

    // Si estamos dentro de /ajax, subir un nivel → /inventario-ferreteria
    if (preg_match('#/ajax$#', $dir)) {
      $dir = rtrim(dirname($dir), '/\\');
    }

    if ($dir === '/') $dir = '';
    return $scheme . '://' . $host . $dir;
  }

  /** Fecha UTC + minutos (Y-m-d H:i:s) */
  private static function utcPlusMinutes(int $m): string {
    $dt = new DateTime('now', new DateTimeZone('UTC'));
    $dt->modify("+{$m} minutes");
    return $dt->format('Y-m-d H:i:s');
  }

  /** Rate-limit básico por IP (silencioso hacia el cliente) */
  private static function passRateLimit(): bool {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::RATELIMIT_PREFIX . md5($ip);
    if (file_exists($key)) {
      $delta = time() - (int)@filemtime($key);
      if ($delta < self::RATELIMIT_SECONDS) return false;
    }
    @touch($key);
    return true;
  }

  /* =========================================================
   * 1) Solicitar enlace de recuperación
   * ========================================================= */
  public static function ctrSolicitarReset(string $correo): array {
    $correo = trim(strtolower($correo ?? ''));
    $msg    = self::mensajeNeutro();

    // Validación de email
    if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
      return ["ok" => true, "message" => $msg];
    }

    // Rate-limit silencioso
    if (!self::passRateLimit()) {
      return ["ok" => true, "message" => $msg];
    }

    // Limpia expirados/consumidos para reducir ruido
    @ModeloPasswordResets::mdlEliminarExpirados();

    // Buscar usuario por correo (case-insensitive en el modelo)
    $usr = ModeloUsuarios::mdlBuscarPorCorreo($correo);
    if ($usr) {
      $userId = (int)$usr['id_usuario'];
      $ip     = $_SERVER['REMOTE_ADDR'] ?? null;

      // Eliminar pendientes sin usar del mismo usuario
      @ModeloPasswordResets::mdlEliminarPendientesPorUsuario($userId);

      // Generar selector + token; guardar solo hash del token
      $selector  = bin2hex(random_bytes(8));     // 16 chars
      $tokenHex  = bin2hex(random_bytes(32));    // 64 chars
      $verifier  = hash('sha256', $tokenHex);    // guardar SOLO hash
      $expiresAt = self::utcPlusMinutes(self::RESET_EXP_MINUTES);

      // Insertar solicitud
      if (ModeloPasswordResets::mdlCrear($userId, $selector, $verifier, $expiresAt, $ip)) {
        $url = rtrim(self::baseUrl(), '/')
             . "/index.php?ruta=restablecer&selector={$selector}&token={$tokenHex}";

        // Email HTML
        $html = "
          <h3>Restablecer contraseña</h3>
          <p>Solicitaste restablecer tu contraseña para Ferretería Santa Lucía.</p>
          <p>Este enlace es válido por " . self::RESET_EXP_MINUTES . " minutos:</p>
          <p><a href='" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "'>Restablecer ahora</a></p>
          <p style='word-break:break-all'>" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "</p>
          <hr>
          <small>Si no fuiste tú, puedes ignorar este mensaje.</small>
        ";

        // Enviar (si falla, se registra en error_log)
        Mailer::enviar($correo, "Restablecer contraseña - Ferretería Santa Lucía", $html);
      }
    }

    // Siempre mensaje neutro
    return ["ok" => true, "message" => $msg];
  }

  /* =========================================================
   * 2) Validar enlace (para mostrar formulario)
   * ========================================================= */
  public static function ctrValidarEnlace(string $selector, string $tokenHex): array {
    $valido = false;
    $row    = null;

    $selector = strtolower(trim($selector ?? ''));
    $tokenHex = strtolower(trim($tokenHex ?? ''));

    if (preg_match('/^[a-f0-9]{16}$/', $selector) && preg_match('/^[a-f0-9]{64}$/', $tokenHex)) {
      $row = ModeloPasswordResets::mdlBuscarPorSelector($selector);
      if ($row && empty($row['used_at'])) {
        $now  = new DateTime('now', new DateTimeZone('UTC'));
        $exp  = new DateTime($row['expires_at'], new DateTimeZone('UTC'));
        $calc = hash('sha256', $tokenHex);

        if ($exp > $now && hash_equals($row['verifier_hash'], $calc)) {
          $valido = true;
        }
      }
    }

    return ["valido" => $valido, "reset" => $row];
  }

  /* =========================================================
   * 3) Guardar nueva contraseña
   * ========================================================= */
  public static function ctrGuardarNueva(string $selector, string $tokenHex, int $resetId, string $pwd, string $pwd2): array {
    // Validaciones básicas
    if ($pwd !== $pwd2) {
      return ["ok" => false, "msg" => "Las contraseñas no coinciden."];
    }
    if (strlen($pwd) < 8 || !preg_match('/[A-Z]/', $pwd) || !preg_match('/[a-z]/', $pwd) || !preg_match('/\d/', $pwd) || !preg_match('/[@#$%&*!?_\-.]/', $pwd)) {
      return ["ok" => false, "msg" => "La contraseña debe tener 8+ caracteres, con al menos: una mayúscula, una minúscula, un número y un carácter especial (@ # $ % & * ! ? _ - .)."];
    }

    $selector = strtolower(trim($selector ?? ''));
    $tokenHex = strtolower(trim($tokenHex ?? ''));

    // Debe existir esa solicitud exacta
    $row = ModeloPasswordResets::mdlBuscarPorIdYSelector($resetId, $selector);
    if (!$row) {
      return ["ok" => false, "msg" => "Enlace inválido."];
    }
    if (!empty($row['used_at'])) {
      return ["ok" => false, "msg" => "El enlace ya fue utilizado."];
    }

    // Expiración
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $exp = new DateTime($row['expires_at'], new DateTimeZone('UTC'));
    if ($exp <= $now) {
      return ["ok" => false, "msg" => "El enlace ha expirado."];
    }

    // Verificación del token
    if (!preg_match('/^[a-f0-9]{64}$/', $tokenHex) ||
        !hash_equals($row['verifier_hash'], hash('sha256', $tokenHex))) {
      @ModeloPasswordResets::mdlEliminarPorSelector($selector); // invalidación defensiva
      return ["ok" => false, "msg" => "Token inválido."];
    }

    // Transacción: actualizar pass + marcar usado + limpiar pendientes
    $pdo = Conexion::Conectar();
    try {
      $pdo->beginTransaction();

      $hash = password_hash($pwd, PASSWORD_DEFAULT);

      // 1) Actualiza password del usuario
      $upd = $pdo->prepare("UPDATE usuarios SET password = :hash WHERE id_usuario = :id LIMIT 1");
      $upd->bindParam(":hash", $hash, PDO::PARAM_STR);
      $upd->bindParam(":id",   $row['user_id'], PDO::PARAM_INT);
      $upd->execute();

      // 2) Marca este reset como usado (UTC)
      $mark = $pdo->prepare("UPDATE password_resets SET used_at = UTC_TIMESTAMP() WHERE id = :id LIMIT 1");
      $mark->bindParam(":id", $row['id'], PDO::PARAM_INT);
      $mark->execute();

      // 3) Limpia otros pendientes del mismo usuario
      $clean = $pdo->prepare("DELETE FROM password_resets WHERE user_id = :u AND used_at IS NULL");
      $clean->bindParam(":u", $row['user_id'], PDO::PARAM_INT);
      $clean->execute();

      $pdo->commit();
    } catch (Throwable $e) {
      $pdo->rollBack();
      return ["ok" => false, "msg" => "No se pudo actualizar la contraseña."];
    }

    return ["ok" => true, "msg" => "Contraseña actualizada. Ahora puedes iniciar sesión."];
  }
}
