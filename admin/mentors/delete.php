<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM mentors WHERE id=?");

$stmt->execute([$id]);

header("Location: list.php");
exit;