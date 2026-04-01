<?php
require __DIR__ . '/_db.php';

$activeStudents = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_group = 'student' AND status = 'active'")->fetchColumn();
$departments = (int)$pdo->query('SELECT COUNT(*) FROM department')->fetchColumn();
$pendingResults = (int)$pdo->query('SELECT COUNT(*) FROM result')->fetchColumn();
$registrationHolds = 0;

$recentLimit = (int)($_GET['recent_limit'] ?? 3);
$recentLimit = $recentLimit < 1 ? 3 : ($recentLimit > 50 ? 50 : $recentLimit);
$recentPage = (int)($_GET['recent_page'] ?? 1);
$recentPage = $recentPage < 1 ? 1 : $recentPage;
$recentOffset = ($recentPage - 1) * $recentLimit;

$recentStudentNo = $_GET['recent_student_no'] ?? null;
$recentCourseCode = $_GET['recent_course_code'] ?? null;

$recentWhere = [];
$recentParams = [];
if ($recentStudentNo !== null && $recentStudentNo !== '') {
  $recentWhere[] = 'r.student_no = ?';
  $recentParams[] = $recentStudentNo;
}
if ($recentCourseCode !== null && $recentCourseCode !== '') {
  $recentWhere[] = 'c.course_code = ?';
  $recentParams[] = $recentCourseCode;
}
$recentWhereSql = $recentWhere ? ('WHERE ' . implode(' AND ', $recentWhere)) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM registration r INNER JOIN course c ON c.id = r.course_id {$recentWhereSql}");
$countStmt->execute($recentParams);
$recentTotal = (int)$countStmt->fetchColumn();

$recentSql = '
  SELECT r.student_no, c.course_code
  FROM registration r
  INNER JOIN course c ON c.id = r.course_id
  ' . $recentWhereSql . '
  ORDER BY r.id DESC
  LIMIT ? OFFSET ?
';
$recentStmt = $pdo->prepare($recentSql);
$bindIndex = 1;
foreach ($recentParams as $value) {
  $recentStmt->bindValue($bindIndex++, $value, PDO::PARAM_STR);
}
$recentStmt->bindValue($bindIndex++, $recentLimit, PDO::PARAM_INT);
$recentStmt->bindValue($bindIndex++, $recentOffset, PDO::PARAM_INT);
$recentStmt->execute();

$recentRegistrations = [];
foreach ($recentStmt->fetchAll() as $row) {
  $recentRegistrations[] = $row['student_no'] . ' - ' . $row['course_code'];
}

$tasks = [
  'Approve course add/drop requests',
  'Publish semester results',
  'Update academic year settings'
];

json_response([
  'status' => 'ok',
  'message' => 'Dashboard stats.',
  'data' => [
    'activeStudents' => $activeStudents,
    'departments' => $departments,
    'pendingResults' => $pendingResults,
    'registrationHolds' => $registrationHolds,
    'recentRegistrations' => $recentRegistrations,
    'recentMeta' => [
      'page' => $recentPage,
      'limit' => $recentLimit,
      'total' => $recentTotal,
      'pages' => $recentLimit > 0 ? (int)ceil($recentTotal / $recentLimit) : 0
    ],
    'tasks' => $tasks
  ]
]);
