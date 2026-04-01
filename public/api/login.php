<?php
require __DIR__ . '/_db.php';

$payload = read_json();
$username = trim($payload['username'] ?? '');
$password = trim($payload['password'] ?? '');

if ($username === '' || $password === '') {
  json_response(['status' => 'error', 'message' => 'Username and password are required.'], 400);
}

$stmt = $pdo->prepare('SELECT id, username, password, user_group, status FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
  json_response(['status' => 'error', 'message' => 'Invalid username or password.'], 401);
}

if (strtolower($user['status']) !== 'active') {
  json_response(['status' => 'error', 'message' => 'Account is inactive.'], 403);
}

$stored = $user['password'];
$info = password_get_info($stored);
$valid = ($info['algo'] ?? 0) !== 0 ? password_verify($password, $stored) : hash_equals($stored, $password);

if (!$valid) {
  json_response(['status' => 'error', 'message' => 'Invalid username or password.'], 401);
}

json_response([
  'status' => 'ok',
  'message' => 'Login successful.',
  'data' => [
    'user' => [
      'id' => $user['id'],
      'username' => $user['username'],
      'role' => $user['user_group']
    ]
  ]
]);
