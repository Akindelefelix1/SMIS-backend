<?php
require __DIR__ . '/_db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$payload = $method === 'GET' ? [] : read_json();

$studentNo = trim(($payload['studentNo'] ?? $_GET['studentNo'] ?? ''));
$academicYear = trim(($payload['academicYear'] ?? $_GET['academicYear'] ?? ''));
$semester = trim(($payload['semester'] ?? $_GET['semester'] ?? ''));
$courseId = $payload['course_id'] ?? $_GET['course_id'] ?? null;
$minTotal = $payload['min_total'] ?? $_GET['min_total'] ?? null;
$maxTotal = $payload['max_total'] ?? $_GET['max_total'] ?? null;

if ($studentNo === '' || $academicYear === '' || $semester === '') {
  json_response(['status' => 'error', 'message' => 'Student number, academic year, and semester are required.'], 400);
}

$yearStmt = $pdo->prepare('SELECT id FROM academic_year WHERE name = ? LIMIT 1');
$yearStmt->execute([$academicYear]);
$yearId = $yearStmt->fetchColumn();

$semStmt = $pdo->prepare('SELECT id FROM semester WHERE name = ? LIMIT 1');
$semStmt->execute([$semester]);
$semId = $semStmt->fetchColumn();

if (!$yearId || !$semId) {
  json_response(['status' => 'error', 'message' => 'Academic year or semester not found.'], 400);
}

$limit = (int)($_GET['limit'] ?? $payload['limit'] ?? 20);
$limit = $limit < 1 ? 20 : ($limit > 100 ? 100 : $limit);
$page = (int)($_GET['page'] ?? $payload['page'] ?? 1);
$page = $page < 1 ? 1 : $page;
$offset = ($page - 1) * $limit;

$sort = $_GET['sort'] ?? $payload['sort'] ?? 'course_code';
$order = strtolower($_GET['order'] ?? $payload['order'] ?? 'asc');
$allowedSort = [
  'course_code' => 'c.course_code',
  'course_unit' => 'c.course_unit',
  'course_work' => 'r.course_work',
  'exam' => 'r.exam',
  'total' => '(r.course_work + r.exam)'
];
if (!isset($allowedSort[$sort])) {
  $sort = 'course_code';
}
$order = $order === 'desc' ? 'desc' : 'asc';

$where = ['r.student_no = ?'];
$params = [$studentNo];
if ($courseId !== null && $courseId !== '') {
  $where[] = 'r.course_id = ?';
  $params[] = $courseId;
}
if ($minTotal !== null && $minTotal !== '') {
  $where[] = '(r.course_work + r.exam) >= ?';
  $params[] = $minTotal;
}
if ($maxTotal !== null && $maxTotal !== '') {
  $where[] = '(r.course_work + r.exam) <= ?';
  $params[] = $maxTotal;
}
$whereSql = 'WHERE ' . implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM result r {$whereSql}");
$countStmt->execute($params);
$totalCount = (int)$countStmt->fetchColumn();

$baseSelect = '
  SELECT r.course_id, c.course_code, c.course_unit, r.course_work, r.exam, (r.course_work + r.exam) AS total
  FROM result r
  INNER JOIN course c ON c.id = r.course_id
  ' . $whereSql;

$listSql = $baseSelect . " ORDER BY {$allowedSort[$sort]} {$order} LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($listSql);
$bindIndex = 1;
foreach ($params as $value) {
  $stmt->bindValue($bindIndex++, $value, PDO::PARAM_STR);
}
$stmt->bindValue($bindIndex++, $limit, PDO::PARAM_INT);
$stmt->bindValue($bindIndex++, $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

if ($totalCount === 0) {
  json_response(['status' => 'ok', 'message' => 'No results found.', 'data' => [
    'studentNo' => $studentNo,
    'academicYear' => $academicYear,
    'semester' => $semester,
    'results' => [],
    'gpa' => 0,
    'cgpa' => 0,
    'meta' => [
      'page' => $page,
      'limit' => $limit,
      'total' => 0,
      'pages' => 0
    ]
  ]]);
}

$gradeStmt = $pdo->prepare('SELECT grade, gp FROM grades WHERE ? BETWEEN lower_bound AND upper_bound LIMIT 1');
$results = [];
$totalUnits = 0;
$totalPoints = 0;

$allStmt = $pdo->prepare($baseSelect);
$allStmt->execute($params);
$allRows = $allStmt->fetchAll();

foreach ($allRows as $row) {
  $total = (int)$row['course_work'] + (int)$row['exam'];
  $gradeStmt->execute([$total]);
  $gradeRow = $gradeStmt->fetch();
  $gp = $gradeRow ? (float)$gradeRow['gp'] : 0;
  $unit = (int)$row['course_unit'];

  $totalUnits += $unit;
  $totalPoints += $gp * $unit;
}

foreach ($rows as $row) {
  $total = (int)$row['total'];
  $gradeStmt->execute([$total]);
  $gradeRow = $gradeStmt->fetch();
  $grade = $gradeRow ? $gradeRow['grade'] : 'N/A';

  $results[] = [
    'course' => $row['course_code'],
    'unit' => (int)$row['course_unit'],
    'ca' => (int)$row['course_work'],
    'exam' => (int)$row['exam'],
    'total' => $total,
    'grade' => $grade
  ];
}

$gpa = $totalUnits > 0 ? round($totalPoints / $totalUnits, 2) : 0;

json_response([
  'status' => 'ok',
  'message' => 'Results fetched.',
  'data' => [
    'studentNo' => $studentNo,
    'academicYear' => $academicYear,
    'semester' => $semester,
    'results' => $results,
    'gpa' => $gpa,
    'cgpa' => $gpa,
    'meta' => [
      'page' => $page,
      'limit' => $limit,
      'total' => $totalCount,
      'pages' => $limit > 0 ? (int)ceil($totalCount / $limit) : 0
    ]
  ]
]);
