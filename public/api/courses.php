<?php
require __DIR__ . '/_db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
  $id = $_GET['id'] ?? null;
  $q = trim($_GET['q'] ?? '');
  $facultyId = $_GET['faculty_id'] ?? null;
  $deptId = $_GET['dept_id'] ?? null;

  if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM course WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    json_response(['status' => 'ok', 'data' => $row]);
  }

  $limit = (int)($_GET['limit'] ?? 20);
  $limit = $limit < 1 ? 20 : ($limit > 100 ? 100 : $limit);
  $page = (int)($_GET['page'] ?? 1);
  $page = $page < 1 ? 1 : $page;
  $offset = ($page - 1) * $limit;

  $sort = $_GET['sort'] ?? 'course_code';
  $order = strtolower($_GET['order'] ?? 'asc');
  $allowedSort = ['id','course_code','course_name','tuition','course_unit'];
  if (!in_array($sort, $allowedSort, true)) {
    $sort = 'course_code';
  }
  $order = $order === 'desc' ? 'desc' : 'asc';

  $where = [];
  $params = [];
  if ($q !== '') {
    $where[] = '(course_code LIKE ? OR course_name LIKE ?)';
    $like = '%' . $q . '%';
    $params = array_merge($params, [$like, $like]);
  }
  if ($facultyId !== null && $facultyId !== '') {
    $where[] = 'faculty_id = ?';
    $params[] = $facultyId;
  }
  if ($deptId !== null && $deptId !== '') {
    $where[] = 'dept_id = ?';
    $params[] = $deptId;
  }

  $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

  $countStmt = $pdo->prepare("SELECT COUNT(*) FROM course {$whereSql}");
  $countStmt->execute($params);
  $total = (int)$countStmt->fetchColumn();

  $sql = "SELECT * FROM course {$whereSql} ORDER BY {$sort} {$order} LIMIT ? OFFSET ?";
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
  $required = ['course_code', 'course_name'];
  foreach ($required as $key) {
    if (empty($payload[$key])) {
      json_response(['status' => 'error', 'message' => 'Missing field: ' . $key], 400);
    }
  }

  $stmt = $pdo->prepare(
    'INSERT INTO course (faculty_id, dept_id, course_code, course_name, duration_id, tuition, course_unit)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
  );

  $stmt->execute([
    $payload['faculty_id'] ?? null,
    $payload['dept_id'] ?? null,
    $payload['course_code'],
    $payload['course_name'],
    $payload['duration_id'] ?? null,
    $payload['tuition'] ?? null,
    $payload['course_unit'] ?? 0
  ]);

  json_response(['status' => 'ok', 'message' => 'Course created.', 'id' => $pdo->lastInsertId()]);
}

if ($method === 'PUT') {
  $payload = read_json();
  $id = $payload['id'] ?? null;
  if (!$id) {
    json_response(['status' => 'error', 'message' => 'Missing id.'], 400);
  }

  $allowed = ['faculty_id','dept_id','course_code','course_name','duration_id','tuition','course_unit'];
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
  $sql = 'UPDATE course SET ' . implode(', ', $fields) . ' WHERE id = ?';
  $stmt = $pdo->prepare($sql);
  $stmt->execute($values);

  json_response(['status' => 'ok', 'message' => 'Course updated.']);
}

if ($method === 'DELETE') {
  $id = $_GET['id'] ?? null;
  if (!$id) {
    json_response(['status' => 'error', 'message' => 'Missing id.'], 400);
  }
  $stmt = $pdo->prepare('DELETE FROM course WHERE id = ?');
  $stmt->execute([$id]);
  json_response(['status' => 'ok', 'message' => 'Course deleted.']);
}

json_response(['status' => 'error', 'message' => 'Method not allowed.'], 405);
