<?php
class Conexion {
  private const DB_HOST = 'localhost';
  private const DB_NAME = '1nventario_santalucia';
  private const DB_USER = 'admin';
  private const DB_PASS = '16javiermusic';
  private const DB_CHAR = 'utf8mb4';
  private const DB_PORT = 3306;

  static public function Conectar() {
    $dsn = sprintf(
      "mysql:host=%s;port=%d;dbname=%s;charset=%s",
      self::DB_HOST, self::DB_PORT, self::DB_NAME, self::DB_CHAR
    );
    $options = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
      // PDO::ATTR_PERSISTENT         => true, // opcional
    ];
    return new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
  }
}
