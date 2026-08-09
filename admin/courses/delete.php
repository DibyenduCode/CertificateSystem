<?php
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT name FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($course) {
        $del_stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $del_stmt->execute([$id]);
        set_flash('success', "Course '" . $course['name'] . "' has been deleted.");
    } else {
        set_flash('error', "Course not found.");
    }
}

header("Location: list.php");
exit;