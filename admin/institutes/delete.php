<?php
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT name FROM institutes WHERE id = ?");
    $stmt->execute([$id]);
    $inst = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($inst) {
        $del_stmt = $pdo->prepare("DELETE FROM institutes WHERE id = ?");
        $del_stmt->execute([$id]);
        set_flash('success', "Institute '" . $inst['name'] . "' has been deleted.");
    } else {
        set_flash('error', "Institute not found.");
    }
}

header("Location: list.php");
exit;