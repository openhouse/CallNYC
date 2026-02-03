<?php
require_once __DIR__ . '/../library/db.php';

if (PHP_SAPI !== 'cli') {
  http_response_code(403);
  echo 'Forbidden';
  exit;
}

$pdo = get_db_connection();
$schemaPath = __DIR__ . '/schema.sql';
$csvPath = __DIR__ . '/../data/sample.csv';

if (!file_exists($schemaPath)) {
  throw new RuntimeException('Schema file not found: ' . $schemaPath);
}

if (!file_exists($csvPath)) {
  throw new RuntimeException('CSV file not found: ' . $csvPath);
}

$schemaSql = file_get_contents($schemaPath);
$pdo->exec($schemaSql);
$pdo->exec('TRUNCATE TABLE `cases`');

$handle = fopen($csvPath, 'r');
if ($handle === false) {
  throw new RuntimeException('Unable to open CSV: ' . $csvPath);
}

$headers = fgetcsv($handle, 0, ',');
if ($headers === false) {
  throw new RuntimeException('CSV header row missing.');
}

$headers = array_map('trim', $headers);
$expected = ['UNIQUE_KEY', 'ACCOUNT', 'OPENDATE', 'COMPLAINT_TYPE', 'DESCRIPTOR', 'ZIP', 'BOROUGH', 'CITY', 'COUNCIL_DIST', 'COMMUNITY_BOARD', 'CLOSEDATE'];
foreach ($expected as $header) {
  if (!in_array($header, $headers, true)) {
    throw new RuntimeException('CSV header missing: ' . $header);
  }
}

$insert = $pdo->prepare(
  "INSERT INTO `cases` (`UNIQUE_KEY`, `ACCOUNT`, `OPENDATE`, `COMPLAINT_TYPE`, `DESCRIPTOR`, `ZIP`, `BOROUGH`, `CITY`, `COUNCIL_DIST`, `COMMUNITY_BOARD`, `CLOSEDATE`, `OPENDATE_INT`, `CLOSEDATE_INT`)
   VALUES (:unique_key, :account, :opendate, :complaint_type, :descriptor, :zip, :borough, :city, :council_dist, :community_board, :closedate, :opendate_int, :closedate_int)
   ON DUPLICATE KEY UPDATE
     `ACCOUNT` = VALUES(`ACCOUNT`),
     `OPENDATE` = VALUES(`OPENDATE`),
     `COMPLAINT_TYPE` = VALUES(`COMPLAINT_TYPE`),
     `DESCRIPTOR` = VALUES(`DESCRIPTOR`),
     `ZIP` = VALUES(`ZIP`),
     `BOROUGH` = VALUES(`BOROUGH`),
     `CITY` = VALUES(`CITY`),
     `COUNCIL_DIST` = VALUES(`COUNCIL_DIST`),
     `COMMUNITY_BOARD` = VALUES(`COMMUNITY_BOARD`),
     `CLOSEDATE` = VALUES(`CLOSEDATE`),
     `OPENDATE_INT` = VALUES(`OPENDATE_INT`),
     `CLOSEDATE_INT` = VALUES(`CLOSEDATE_INT`)"
);

$count = 0;
$duplicateCount = 0;
while (($row = fgetcsv($handle, 0, ',')) !== false) {
  $record = array_combine($headers, $row);
  if ($record === false) {
    continue;
  }

  $openDate = trim((string) ($record['OPENDATE'] ?? ''));
  $closeDate = trim((string) ($record['CLOSEDATE'] ?? ''));

  $insert->execute([
    ':unique_key' => trim((string) ($record['UNIQUE_KEY'] ?? '')),
    ':account' => trim((string) ($record['ACCOUNT'] ?? '')),
    ':opendate' => $openDate !== '' ? $openDate : null,
    ':complaint_type' => trim((string) ($record['COMPLAINT_TYPE'] ?? '')) ?: null,
    ':descriptor' => trim((string) ($record['DESCRIPTOR'] ?? '')) ?: null,
    ':zip' => trim((string) ($record['ZIP'] ?? '')) ?: null,
    ':borough' => trim((string) ($record['BOROUGH'] ?? '')) ?: null,
    ':city' => trim((string) ($record['CITY'] ?? '')) ?: null,
    ':council_dist' => trim((string) ($record['COUNCIL_DIST'] ?? '')) ?: null,
    ':community_board' => trim((string) ($record['COMMUNITY_BOARD'] ?? '')) ?: null,
    ':closedate' => $closeDate !== '' ? $closeDate : null,
    ':opendate_int' => $openDate !== '' ? strtotime($openDate) : null,
    ':closedate_int' => $closeDate !== '' ? strtotime($closeDate) : null,
  ]);

  if ($insert->rowCount() > 1) {
    $duplicateCount++;
  }

  $count++;
}

fclose($handle);

echo "Seeded {$count} rows.\n";
if ($duplicateCount > 0) {
  echo "Skipped {$duplicateCount} duplicate rows based on UNIQUE_KEY.\n";
}
?>
