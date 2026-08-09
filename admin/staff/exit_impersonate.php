<?php
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/functions.php";

if (isset($_SESSION['admin_id'])) {
    unset($_SESSION['staff_id']);
    unset($_SESSION['staff_name']);
    unset($_SESSION['staff_username']);
    unset($_SESSION['staff_permissions']);
    unset($_SESSION['impersonated_by_admin']);
    set_flash('success', "Exited staff view mode. Returned to Administrator mode.");
    header("Location: " . BASE_URL . "/admin/staff/list.php");
    exit;
}

header("Location: " . BASE_URL . "/admin/dashboard.php");
exit;
