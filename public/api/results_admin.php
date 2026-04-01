<?php
require __DIR__ . '/_db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
  $id = $_GET['id'] ?? null;
  $studentNo = $_GET['student_no'] ?? null;
  $courseId = $_GET['course_id'] ?? null;
  $staffId = $_GET['staff_id'] ?? null;

  if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM result WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    json_response(['status' => 'ok', 'data' => $row]);
  }

  $limit = (int)($_GET['limit'] ?? 20);
  $limit = $limit < 1 ? 20 : ($limit > 100 ? 100 : $limit);
  $page = (int)($_GET['page'] ?? 1);
  $page = $page < 1 ? 1 : $page;
  $offset = ($page - 1) * $limit;

  $sort = $_GET['sort'] ?? 'id';
  $order = strtolower($_GET['order'] ?? 'desc');
  $allowedSort = ['id','student_no','course_id','result_date'];
  if (!in_array($sort, $allowedSort, true)) {
    $sort = 'id';
  }
  $order = $order === 'asc' ? 'asc' : 'desc';

  $where = [];
  $params = [];
  if ($studentNo !== null && $studentNo !== '') {
    $where[] = 'student_no = ?';
    $params[] = $studentNo;
  }
  if ($courseId !== null && $courseId !== '') {
    $where[] = 'course_id = ?';
    $params[] = $courseId;
  }
  if ($staffId !== null && $staffId !== '') {
    $where[] = 'staff_id = ?';
    $params[] = $staffId;
  }

  $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

  $countStmt = $pdo->prepare("SELECT COUNT(*) FROM result {$whereSql}");
  $countStmt->execute($params);
  $total = (int)$countStmt->fetchColumn();

  $sql = "SELECT * FROM result {$whereSql} ORDER BY {$sort} {$order} LIMIT ? OFFSET ?";
  $stmt = $pdo->prepare($sql);
  $bindIndex = 1;
  foreach ($params as $value) {
    $stmt->bindValue($bindIndex++, $value, PDO::PARAM_STR);
  }
  $stmt->bindValue($bindIndex++, $limit, PDO::PARAM_INT);
  $stmt->bindValue($bindIndex++, $offset, PDO::PARAM_INT);
  $stmt->execute();
  $rows = $stmt->fetchAll();

  json_response([
    'status' => 'ok',
    'data' => $rows,
    'meta' => [
      'page' => $page,
      'limit' => $limit,
      'total' => $total,
      'pages' => $limit > 0 ? (int)ceil($total / $limit) : 0
    ]
  ]);
}

if ($method === 'POST') {
  $payload = read_json();
  $required = ['course_id', 'course_work', 'exam', 'student_no'];
  foreach ($required as $key) {
    if (empty($payload[$key]) && $payload[$key] !== 0) {
      json_response(['status' => 'error', 'message' => 'Missing field: ' . $key], 400);
    }
  }

  $stmt = $pdo->prepare(
    'INSERT INTO result (staff_id, course_id, course_unit_id, course_work, exam, student_no)
     VALUES (?, ?, ?, ?, ?, ?)'
  );

  $stmt->execute([
    $payload['staff_id'] ?? null,
    $payload['course_id'],
    $payload['course_unit_id'] ?? null,
    $payload['course_work'],
    $payload['exam'],
    $payload['student_no']
  ]);

  json_response(['status' => 'ok', 'message' => 'Result created.', 'id' => $pdo->lastInsertId()]);
}

if ($method === 'PUT') {
  $payload = read_json();
  $id = $payload['id'] ?? null;
  if (!$id) {
    json_response(['status' => 'error', 'message' => 'Missing id.'], 400);
  }

  $allowed = ['staff_id','course_id','course_unit_id','course_work','exam','student_no'];
  $fields = [];
  $values = [];
  foreach ($allowed as $key) {
    if (array_key_exists($key, $payload)) {
      $fields[] = "`$key` = ?";
      $values[] = $payload[$key];
    }
  }

  if (!$fields) {
    json_response(['status' => 'error', 'message' => 'No fields to update.'], 400);
  }

  $values[] = $id;
  $sql = 'UPDATE result SET ' . implode(', ', $fields) . ' WHERE id = ?';
  $stmt = $pdo->prepare($sql);
  $stmt->execute($values);

  json_response(['status' => 'ok', 'message' => 'Result updated.']);
}

if ($method === 'DELETE') {
  $id = $_GET['id'] ?? null;
  if (!$id) {
    json_response(['status' => 'error', 'message' => 'Missing id.'], 400);
  }
  $stmt = $pdo->prepare('DELETE FROM result WHERE id = ?');
  $stmt->execute([$id]);
  json_response(['status' => 'ok', 'message' => 'Result deleted.']);
}

json_response(['status' => 'error', 'message' => 'Method not allowed.'], 405);
