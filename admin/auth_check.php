<?php

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/functions.php";

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['staff_id'])) {
    header("Location: " . BASE_URL . "/admin/login.php");
    exit;
}

// Module permission enforcement for Staff users
if (is_staff()) {
    $script_path = $_SERVER['SCRIPT_NAME'];
    
    // Staff members cannot access Staff Management tools
    if (strpos($script_path, '/admin/staff/') !== false && empty($_SESSION['impersonated_by_admin'])) {
        set_flash('error', 'Access Denied: Staff members cannot access Staff Management.');
        header("Location: " . BASE_URL . "/admin/dashboard.php");
        exit;
    }

    // Check specific module permissions
    $modules = ['students', 'courses', 'mentors', 'institutes', 'api_keys'];
    foreach ($modules as $mod) {
        if (strpos($script_path, "/admin/{$mod}/") !== false) {
            if (!has_permission($mod)) {
                set_flash('error', "Access Denied: You do not have permission to access " . ucfirst($mod) . ".");
                header("Location: " . BASE_URL . "/admin/dashboard.php");
                exit;
            }
        }
    }
}