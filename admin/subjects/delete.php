<?php
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT name, course_id FROM subjects WHERE id=?");
    $stmt->execute([$id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subject) {
        $del = $pdo->prepare("DELETE FROM subjects WHERE id=?");
        $del->execute([$id]);
        set_flash('success', "Subject '{$subject['name']}' was deleted successfully!");
        header("Location: list.php?course_id=" . (int)$subject['course_id']);
        exit;
    }
}

set_flash('error', "Subject not found or invalid ID.");
header("Location: list.php");
exit;
