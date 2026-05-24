<?php
require_once __DIR__ . "/../extensiones/PHPMailer/PHPMailer.php";
require_once __DIR__ . "/../extensiones/PHPMailer/SMTP.php";
require_once __DIR__ . "/../extensiones/PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
  private const SMTP_HOST = 'smtp.gmail.com';
  private const SMTP_USER = 'alefrman17@gmail.com';
  private const SMTP_PASS = 'fqwnaopopgvxfzfq';
  private const SMTP_PORT = 587;

  static public function enviar(string $to, string $subject, string $html): void {
    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host       = self::SMTP_HOST;
      $mail->SMTPAuth   = true;
      $mail->Username   = self::SMTP_USER;
      $mail->Password   = self::SMTP_PASS;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = self::SMTP_PORT;
      $mail->Timeout    = 15; // segs

      // (dev) permitir cert self-signed:
      $mail->SMTPOptions = [
        'ssl' => [
          'verify_peer'       => false,
          'verify_peer_name'  => false,
          'allow_self_signed' => true
        ]
      ];

      $mail->CharSet  = 'UTF-8';
      $mail->Encoding = 'base64';

      $mail->setFrom(self::SMTP_USER, 'Ferretería Santa Lucía');
      $mail->addAddress($to);
      // $mail->addReplyTo('soporte@tu-dominio.com', 'Soporte');

      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body    = $html;
      $mail->AltBody = strip_tags($html);

      $mail->send();
    } catch (Exception $e) {
      error_log("Error al enviar correo a $to: " . $mail->ErrorInfo);
    }
  }
}
