<?php
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

if (!isset($_SESSION['admin_id'])) {
    set_flash('error', "Access Denied: Only Administrator can use one-click staff access.");
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
$stmt->execute([$id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$staff) {
    set_flash('error', "Staff account not found.");
    header("Location: list.php");
    exit;
}

// 1-Click Access as Staff
$_SESSION['staff_id'] = $staff['id'];
$_SESSION['staff_name'] = $staff['name'];
$_SESSION['staff_username'] = $staff['username'];
$_SESSION['staff_permissions'] = json_decode($staff['permissions'] ?? '[]', true);
$_SESSION['impersonated_by_admin'] = true;

set_flash('info', "1-Click Access: Now viewing Dashboard as Staff '{$staff['name']}'. (Permissions: " . implode(', ', get_staff_permissions()) . ")");
header("Location: " . BASE_URL . "/admin/dashboard.php");
exit;
