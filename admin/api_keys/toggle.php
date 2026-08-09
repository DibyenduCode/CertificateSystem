<?php
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT name, status FROM api_keys WHERE id = ?");
    $stmt->execute([$id]);
    $key = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($key) {
        $new_status = ($key['status'] === 'active') ? 'inactive' : 'active';
        $update_stmt = $pdo->prepare("UPDATE api_keys SET status = ? WHERE id = ?");
        $update_stmt->execute([$new_status, $id]);

        set_flash('info', "API Key for '" . $key['name'] . "' is now " . strtoupper($new_status) . ".");
    } else {
        set_flash('error', "API Key not found.");
    }
}

header("Location: list.php");
exit;