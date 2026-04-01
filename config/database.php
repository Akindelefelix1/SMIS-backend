<?php
return [
  "db" => [
    "host" => getenv("DB_HOST") ?: "localhost",
    "name" => getenv("DB_NAME") ?: "smis",
    "user" => getenv("DB_USER") ?: "root",
    "pass" => getenv("DB_PASS") ?: "",
    "charset" => getenv("DB_CHARSET") ?: "utf8mb4"
  ]
];
