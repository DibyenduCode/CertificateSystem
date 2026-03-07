<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

$id = $_GET['id'];

$stmt = $pdo->prepare("
UPDATE api_keys
SET status = IF(status='active','inactive','active')
WHERE id=?
");

$stmt->execute([$id]);

header("Location: list.php");
exit;