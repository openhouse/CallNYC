<?php
require_once __DIR__ . '/../library/dbinfo.php';

if (PHP_SAPI !== 'cli') {
  http_response_code(403);
  echo 'Forbidden';
  exit;
}

function connect_with_retry(array $config, int $maxAttempts = 30, int $sleepSeconds = 2): PDO
{
  $dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $config['host'],
    $config['port'],
    $config['name']
  );

  for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
    try {
      $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]);
      $pdo->query('SELECT 1');
      return $pdo;
    } catch (PDOException $e) {
      echo "Waiting for database... ({$attempt}/{$maxAttempts})\n";
      sleep($sleepSeconds);
    }
  }

  throw new RuntimeException('Database did not become ready in time.');
}

$config = db_config();
$pdo = connect_with_retry($config);

$check = $pdo->prepare(
  'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :schema AND table_name = :table'
);
$check->execute([
  ':schema' => $config['name'],
  ':table' => 'cases',
]);

$exists = (int) $check->fetchColumn() > 0;

if ($exists) {
  echo "Database already seeded.\n";
  exit(0);
}

echo "Seeding database...\n";
passthru('php bin/seed.php', $exitCode);

if ($exitCode !== 0) {
  throw new RuntimeException('Seeding failed with exit code ' . $exitCode);
}
?>
