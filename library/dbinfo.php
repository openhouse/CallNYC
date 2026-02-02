<?php
function db_config(): array
{
  $config = [
    'host' => getenv('DB_HOST') ?: 'db',
    'name' => getenv('DB_NAME') ?: 'callnyc',
    'user' => getenv('DB_USER') ?: 'callnyc',
    'pass' => getenv('DB_PASS') ?: 'callnyc',
    'port' => getenv('DB_PORT') ?: '3306',
  ];

  $databaseUrl = getenv('DATABASE_URL');
  if ($databaseUrl) {
    $parts = parse_url($databaseUrl);
    if (!empty($parts['host'])) {
      $config['host'] = $parts['host'];
    }
    if (!empty($parts['user'])) {
      $config['user'] = $parts['user'];
    }
    if (!empty($parts['pass'])) {
      $config['pass'] = $parts['pass'];
    }
    if (!empty($parts['port'])) {
      $config['port'] = (string) $parts['port'];
    }
    if (!empty($parts['path'])) {
      $config['name'] = ltrim($parts['path'], '/');
    }
  }

  return $config;
}
?>
