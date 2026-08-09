<?php
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT name FROM api_keys WHERE id = ?");
    $stmt->execute([$id]);
    $key = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($key) {
        $del_stmt = $pdo->prepare("DELETE FROM api_keys WHERE id = ?");
        $del_stmt->execute([$id]);

        set_flash('success', "API Key for '" . $key['name'] . "' has been revoked and deleted.");
    } else {
        set_flash('error', "API Key not found.");
    }
}

header("Location: list.php");
exit;