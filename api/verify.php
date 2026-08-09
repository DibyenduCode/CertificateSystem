<?php

require_once "../config/database.php";
require_once "../config/config.php";

header("Content-Type: application/json");

/* Read Request Body */
$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true) ?? [];

/* Read API Key (case-insensitive headers, $_SERVER, JSON body, or GET) */
$api_key = '';
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    foreach ($headers as $h_name => $h_val) {
        if (strtolower($h_name) === 'x-api-key') {
            $api_key = trim($h_val);
            break;
        }
    }
}
if (!$api_key && !empty($_SERVER['HTTP_X_API_KEY'])) {
    $api_key = trim($_SERVER['HTTP_X_API_KEY']);
}
if (!$api_key && !empty($data['api_key'])) {
    $api_key = trim($data['api_key']);
}
if (!$api_key && !empty($_GET['api_key'])) {
    $api_key = trim($_GET['api_key']);
}

$stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key=? AND status='active'");
$stmt->execute([$api_key]);
$key = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$key)
{
    http_response_code(403);
    echo json_encode([
        "error" => "Invalid API Key"
    ]);
    exit;
}

/* Domain Specific Verification Check */
$incoming_origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
$request_domain = '';
if ($incoming_origin !== '') {
    $parsed_host = parse_url($incoming_origin, PHP_URL_HOST);
    if ($parsed_host) {
        $request_domain = strtolower(trim($parsed_host));
    } else {
        $clean_orig = preg_replace('/^https?:\/\//i', '', $incoming_origin);
        $request_domain = strtolower(trim(explode('/', explode(':', $clean_orig)[0])[0]));
    }
}

if (!empty($key['allowed_domain'])) {
    $allowed = strtolower(trim($key['allowed_domain']));
    $allowed_clean = preg_replace('/^www\./i', '', $allowed);
    $request_clean = preg_replace('/^www\./i', '', $request_domain);

    // If request has origin/referer and domain does not match allowed domain
    if ($request_domain !== '' && $request_clean !== $allowed_clean && !str_ends_with($request_clean, '.' . $allowed_clean)) {
        http_response_code(403);
        echo json_encode([
            "error" => "Domain Access Denied: This API Key is restricted to approved domain: " . $key['allowed_domain'],
            "request_domain" => $request_domain
        ]);
        exit;
    }

    header("Access-Control-Allow-Origin: " . ($incoming_origin ?: "*"));
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-API-KEY, Authorization");

/* Handle OPTIONS Preflight Request */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/* Increase API usage counter */
$update = $pdo->prepare("UPDATE api_keys SET hit_count = hit_count + 1 WHERE id=?");
$update->execute([$key['id']]);

$reg = trim($data['registration_number'] ?? $_POST['registration_number'] ?? $_GET['registration_number'] ?? '');
$dob = trim($data['dob'] ?? $_POST['dob'] ?? $_GET['dob'] ?? '');

/* Find student */

$stmt = $pdo->prepare("
SELECT students.*, courses.name AS course, mentors.name AS mentor
FROM students
LEFT JOIN courses ON courses.id = students.course_id
LEFT JOIN mentors ON mentors.id = students.mentor_id
WHERE registration_number=? AND dob=?
");

$stmt->execute([$reg,$dob]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if($student)
{
    $photo_url = null;
    if (!empty($student['student_photo']) && file_exists(__DIR__ . '/../uploads/' . $student['student_photo'])) {
        $photo_url = BASE_URL . "/uploads/" . $student['student_photo'];
    }

    $verify_page_url = BASE_URL . "/public/verify.php?cert=" . urlencode($student['certificate_number']);
    $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($verify_page_url);

    $theory_marks = (int)($student['theory_marks'] ?? 0);
    $practical_marks = (int)($student['practical_marks'] ?? 0);

    echo json_encode([
        "status"                   => "verified",
        "name"                     => $student['name'],
        "father_name"              => $student['father_name'] ?? '',
        "registration_number"      => $student['registration_number'],
        "certificate_number"       => $student['certificate_number'],
        "course"                   => $student['course'] ?? 'N/A',
        "mentor"                   => $student['mentor'] ?? 'N/A',
        "grade"                    => $student['grade'] ?? 'Pass',
        "theory_marks"             => $theory_marks,
        "practical_marks"          => $practical_marks,
        "total_marks"              => ($theory_marks + $practical_marks),
        "issue_date"               => $student['issue_date'] ?? '',
        "student_photo_url"        => $photo_url,
        "qr_code_url"              => $qr_code_url,
        "certificate_download_url" => BASE_URL . "/public/download.php?cert=" . urlencode($student['certificate_number']),
        "marksheet_download_url"   => BASE_URL . "/public/download_marksheet.php?cert=" . urlencode($student['certificate_number'])
    ]);
}
else
{
    echo json_encode([
        "status" => "not_found"
    ]);
}