<?php
$config = require __DIR__ . "/../config/database.php";
require __DIR__ . "/Db.php";

$db = new Db($config["db"]);
