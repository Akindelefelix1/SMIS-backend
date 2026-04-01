<?php
require __DIR__ . '/_db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
  $id = $_GET['id'] ?? null;
  $studentNo = $_GET['student_no'] ?? null;
  $q = trim($_GET['q'] ?? '');

  if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM admission WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    json_response(['status' => 'ok', 'data' => $row]);
  }

  if ($studentNo) {
    $stmt = $pdo->prepare('SELECT * FROM admission WHERE student_no = ?');
    $stmt->execute([$studentNo]);
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
  $allowedSort = ['id','student_no','reg_no','first_name','surname','admission_date'];
  if (!in_array($sort, $allowedSort, true)) {
    $sort = 'id';
  }
  $order = $order === 'asc' ? 'asc' : 'desc';

  $where = [];
  $params = [];
  if ($q !== '') {
    $where[] = '(first_name LIKE ? OR surname LIKE ? OR student_no LIKE ? OR reg_no LIKE ?)';
    $like = '%' . $q . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
  }

  $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

  $countStmt = $pdo->prepare("SELECT COUNT(*) FROM admission {$whereSql}");
  $countStmt->execute($params);
  $total = (int)$countStmt->fetchColumn();

  $sql = "SELECT * FROM admission {$whereSql} ORDER BY {$sort} {$order} LIMIT ? OFFSET ?";
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
  $required = ['first_name', 'surname', 'student_no', 'reg_no', 'academic_year_id'];
  foreach ($required as $key) {
    if (empty($payload[$key])) {
      json_response(['status' => 'error', 'message' => 'Missing field: ' . $key], 400);
    }
  }

  $stmt = $pdo->prepare(
    'INSERT INTO admission (institution_id, faculty_id, dept_id, title_id, first_name, surname, nationality, student_no, reg_no, academic_year_id, course_id, program_id, sponsor_id, `year`, sex, dob, pob, marital_status_id, admission_date, admission_time)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
  );

  $stmt->execute([
    $payload['institution_id'] ?? null,
    $payload['faculty_id'] ?? null,
    $payload['dept_id'] ?? null,
    $payload['title_id'] ?? null,
    $payload['first_name'],
    $payload['surname'],
    $payload['nationality'] ?? null,
    $payload['student_no'],
    $payload['reg_no'],
    $payload['academic_year_id'],
    $payload['course_id'] ?? null,
    $payload['program_id'] ?? null,
    $payload['sponsor_id'] ?? null,
    $payload['year'] ?? null,
    $payload['sex'] ?? null,
    $payload['dob'] ?? null,
    $payload['pob'] ?? null,
    $payload['marital_status_id'] ?? null,
    $payload['admission_date'] ?? null,
    $payload['admission_time'] ?? null
  ]);

  json_response(['status' => 'ok', 'message' => 'Student created.', 'id' => $pdo->lastInsertId()]);
}

if ($method === 'PUT') {
  $payload = read_json();
  $id = $payload['id'] ?? null;
  if (!$id) {
    json_response(['status' => 'error', 'message' => 'Missing id.'], 400);
  }

  $allowed = ['institution_id','faculty_id','dept_id','title_id','first_name','surname','nationality','student_no','reg_no','academic_year_id','course_id','program_id','sponsor_id','year','sex','dob','pob','marital_status_id','admission_date','admission_time'];
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
  $sql = 'UPDATE admission SET ' . implode(', ', $fields) . ' WHERE id = ?';
  $stmt = $pdo->prepare($sql);
  $stmt->execute($values);

  json_response(['status' => 'ok', 'message' => 'Student updated.']);
}

if ($method === 'DELETE') {
  $id = $_GET['id'] ?? null;
  if (!$id) {
    json_response(['status' => 'error', 'message' => 'Missing id.'], 400);
  }
  $stmt = $pdo->prepare('DELETE FROM admission WHERE id = ?');
  $stmt->execute([$id]);
  json_response(['status' => 'ok', 'message' => 'Student deleted.']);
}

json_response(['status' => 'error', 'message' => 'Method not allowed.'], 405);
