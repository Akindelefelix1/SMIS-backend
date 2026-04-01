<?php
require __DIR__ . '/_db.php';

$payload = read_json();
$studentNo = trim($payload['studentNo'] ?? '');
$regNo = trim($payload['regNo'] ?? '');
$semester = trim($payload['semester'] ?? '');
$academicYear = trim($payload['academicYear'] ?? '');
$courses = $payload['courses'] ?? [];

if ($studentNo === '' || $regNo === '' || $semester === '' || $academicYear === '') {
  json_response(['status' => 'error', 'message' => 'All registration fields are required.'], 400);
}

if (!is_array($courses) || count($courses) === 0) {
  json_response(['status' => 'error', 'message' => 'Select at least one course.'], 400);
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

try {
  $pdo->beginTransaction();
  $insert = $pdo->prepare('INSERT INTO registration (academic_year_id, sem_id, reg_no, student_no, course_id) VALUES (?, ?, ?, ?, ?)');
  $courseLookup = $pdo->prepare('SELECT id FROM course WHERE course_code = ? LIMIT 1');

  foreach ($courses as $courseLabel) {
    $courseCode = trim(explode(' - ', $courseLabel)[0]);
    $courseLookup->execute([$courseCode]);
    $courseId = $courseLookup->fetchColumn();
    if (!$courseId) {
      throw new Exception('Course not found: ' . $courseCode);
    }
    $insert->execute([$yearId, $semId, $regNo, $studentNo, $courseId]);
  }

  $pdo->commit();
} catch (Exception $e) {
  $pdo->rollBack();
  json_response(['status' => 'error', 'message' => $e->getMessage()], 400);
}

json_response([
  'status' => 'ok',
  'message' => 'Registration submitted.',
  'data' => [
    'studentNo' => $studentNo,
    'regNo' => $regNo,
    'semester' => $semester,
    'academicYear' => $academicYear,
    'courses' => $courses
  ]
]);
