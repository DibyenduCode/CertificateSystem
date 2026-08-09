<?php
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT name FROM mentors WHERE id = ?");
    $stmt->execute([$id]);
    $mentor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mentor) {
        $del_stmt = $pdo->prepare("DELETE FROM mentors WHERE id = ?");
        $del_stmt->execute([$id]);
        set_flash('success', "Mentor '" . $mentor['name'] . "' has been deleted.");
    } else {
        set_flash('error', "Mentor not found.");
    }
}

header("Location: list.php");
exit;