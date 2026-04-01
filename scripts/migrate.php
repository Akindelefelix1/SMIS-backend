<?php
$config = require __DIR__ . '/../config/database.php';
$db = $config['db'];

$host = $db['host'];
$name = $db['name'];
$user = $db['user'];
$pass = $db['pass'];
$charset = $db['charset'] ?? 'utf8mb4';

$rootDsn = "mysql:host={$host};charset={$charset}";
$rootPdo = new PDO($rootDsn, $user, $pass, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
$rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` DEFAULT CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");

$dbDsn = "mysql:host={$host};dbname={$name};charset={$charset}";
$pdo = new PDO($dbDsn, $user, $pass, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$pdo->exec(
  'CREATE TABLE IF NOT EXISTS schema_migrations (' .
  'id INT AUTO_INCREMENT PRIMARY KEY,' .
  'filename VARCHAR(255) NOT NULL UNIQUE,' .
  'applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP' .
  ') ENGINE=InnoDB'
);

$applied = $pdo->query('SELECT filename FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
$appliedSet = array_flip($applied ?: []);

$migrations = glob(__DIR__ . '/../database/migrations/*.sql');
if (!$migrations) {
  echo "No migrations found.\n";
  exit(0);
}

sort($migrations);

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

foreach ($migrations as $file) {
  $nameOnly = basename($file);
  if (isset($appliedSet[$nameOnly])) {
    continue;
  }

  $sql = file_get_contents($file);
  $sql = $stripComments($sql);
  $statements = $splitSql($sql);

  try {
    foreach ($statements as $idx => $statement) {
      $pdo->exec($statement);
    }
    $stmt = $pdo->prepare('INSERT INTO schema_migrations (filename) VALUES (?)');
    $stmt->execute([$nameOnly]);
    echo "Applied: {$nameOnly}\n";
  } catch (Exception $e) {
    $message = $e->getMessage();
    echo "Failed: {$nameOnly} - {$message}\n";
    if (isset($statement)) {
      echo "Statement: {$statement}\n";
    }
    exit(1);
  }
}

echo "Migrations complete.\n";
