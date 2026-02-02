<?php
require_once __DIR__ . '/dbinfo.php';

function get_db_connection(): PDO
{
  static $pdo;

  if ($pdo instanceof PDO) {
    return $pdo;
  }

  $config = db_config();
  $dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $config['host'],
    $config['port'],
    $config['name']
  );

  $pdo = new PDO($dsn, $config['user'], $config['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);

  return $pdo;
}
?>
