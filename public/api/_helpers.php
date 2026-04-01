<?php
function cors_headers() {
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
  header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

cors_headers();
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

function json_response($data, $status = 200) {
  http_response_code($status);
  header('Content-Type: application/json');
  cors_headers();
  echo json_encode($data);
  exit;
}

function read_json() {
  $raw = file_get_contents('php://input');
  if (!$raw) {
    return [];
  }
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
