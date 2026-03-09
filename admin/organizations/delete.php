<?php

require_once "../../config/database.php";
require_once "../auth_check.php";

$id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("DELETE FROM organizations WHERE id=?");
$stmt->execute([$id]);

header("Location: list.php");
exit;