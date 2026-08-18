<?php
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

ensure_smtp_and_email_tables($pdo);

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$redirect = $_GET['redirect'] ?? 'edit';

if ($id <= 0) {
    set_flash('error', "Invalid student ID.");
    header("Location: list.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    set_flash('error', "Student record not found.");
    header("Location: list.php");
    exit;
}

if (empty($student['email'])) {
    set_flash('error', "Cannot send email: Student has no email address on record. Please update the student email first.");
    if ($redirect === 'list') {
        header("Location: list.php");
    } else {
        header("Location: edit.php?id=" . $id);
    }
    exit;
}

$mailResult = sendStudentCongratulationEmail($id, $pdo, true);

if ($mailResult['success']) {
    set_flash('success', "Congratulation email successfully sent to {$student['email']}!");
} else {
    set_flash('error', "Failed to send email to {$student['email']}: " . $mailResult['message']);
}

if ($redirect === 'list') {
    header("Location: list.php");
} else {
    header("Location: edit.php?id=" . $id);
}
exit;
