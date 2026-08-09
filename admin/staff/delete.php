<?php
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

if (!is_admin() && empty($_SESSION['impersonated_by_admin'])) {
    set_flash('error', "Access Denied: Only Administrator can delete staff accounts.");
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Fetch staff name for flash notification
    $stmt = $pdo->prepare("SELECT name FROM staff WHERE id = ?");
    $stmt->execute([$id]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($staff) {
        // Disassociate staff ID in students table without deleting student entries
        try {
            $pdo->prepare("UPDATE students SET created_by_staff_id = NULL WHERE created_by_staff_id = ?")->execute([$id]);
        } catch (Exception $e) {
            // Ignore if column doesn't exist
        }

        // Delete staff account row
        $delete_stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
        $delete_stmt->execute([$id]);

        set_flash('success', "Staff account '{$staff['name']}' deleted successfully. (All student entry data added by this staff was preserved safely).");
    } else {
        set_flash('error', "Staff record not found.");
    }
}

header("Location: list.php");
exit;
