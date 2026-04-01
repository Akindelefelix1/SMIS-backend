<?php
// Basic front controller placeholder
header("Content-Type: application/json");

echo json_encode([
  "status" => "ok",
  "message" => "SMIS backend is running",
  "timestamp" => date("c")
]);
