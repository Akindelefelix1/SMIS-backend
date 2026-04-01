<?php
$config = require __DIR__ . '/../config/database.php';
$db = $config['db'];

$host = $db['host'];
$name = $db['name'];
$user = $db['user'];
$pass = $db['pass'];
$charset = $db['charset'] ?? 'utf8mb4';

$dbDsn = "mysql:host={$host};dbname={$name};charset={$charset}";
$pdo = new PDO($dbDsn, $user, $pass, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$sql = file_get_contents(__DIR__ . '/../database/seed.sql');

$stripComments = function ($sql) {
  $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
  $sql = preg_replace('/--.*$/m', '', $sql);
  return $sql;
};

$splitSql = function ($sql) {
  $statements = [];
  $buffer = '';
  $inString = false;
  $stringChar = '';
  $len = strlen($sql);

  for ($i = 0; $i < $len; $i++) {
    $ch = $sql[$i];
    if ($inString) {
      if ($ch === $stringChar && ($i === 0 || $sql[$i - 1] !== '\\')) {
        $inString = false;
      }
      $buffer .= $ch;
      continue;
    }

    if ($ch === '\'' || $ch === '"') {
      $inString = true;
      $stringChar = $ch;
      $buffer .= $ch;
      continue;
    }

    if ($ch === ';') {
      $statement = trim($buffer);
      if ($statement !== '') {
        $statements[] = $statement;
      }
      $buffer = '';
      continue;
    }

    $buffer .= $ch;
  }

  $tail = trim($buffer);
  if ($tail !== '') {
    $statements[] = $tail;
  }

  return $statements;
};

$sql = $stripComments($sql);
$statements = $splitSql($sql);

foreach ($statements as $statement) {
  $pdo->exec($statement);
}

echo "Seed complete.\n";
