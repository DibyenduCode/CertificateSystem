<?php

require_once "../auth_check.php";
require_once "../../config/database.php";

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM students WHERE id=?");

$stmt->execute([$id]);

header("Location: list.php");
exit;