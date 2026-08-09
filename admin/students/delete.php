<?php
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Fetch student photo to clean up file system
    $stmt = $pdo->prepare("SELECT name, student_photo FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        if (!empty($student['student_photo'])) {
            $photo_path = __DIR__ . "/../../uploads/" . $student['student_photo'];
            if (file_exists($photo_path)) {
                @unlink($photo_path);
            }
        }

        $del_stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $del_stmt->execute([$id]);

        set_flash('success', "Student record for '" . $student['name'] . "' has been deleted.");
    } else {
        set_flash('error', "Student record not found.");
    }
}

header("Location: list.php");
exit;