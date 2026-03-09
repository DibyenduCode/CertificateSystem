<?php

require_once "../config/database.php";
require_once "../config/config.php";

header("Content-Type: application/json");

/* Read API Key */

$headers = getallheaders();
$api_key = $headers['X-API-KEY'] ?? '';

$stmt = $pdo->prepare(
"SELECT * FROM api_keys WHERE api_key=? AND status='active'"
);

$stmt->execute([$api_key]);

$key = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$key)
{
http_response_code(403);

echo json_encode([
"error"=>"Invalid API Key"
]);

exit;
}

/* Increase API usage counter */

$update = $pdo->prepare(
"UPDATE api_keys SET hit_count = hit_count + 1 WHERE id=?"
);

$update->execute([$key['id']]);

/* Read request body */

$data = json_decode(file_get_contents("php://input"), true);

$reg = $data['registration_number'] ?? '';
$dob = $data['dob'] ?? '';

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

echo json_encode([

"status"=>"verified",

"name"=>$student['name'],
"course"=>$student['course'],
"mentor"=>$student['mentor'],
"grade"=>$student['grade'],

"certificate_download_url" =>
BASE_URL."/public/download.php?cert=".$student['certificate_number']

]);

}
else
{

echo json_encode([
"status"=>"not_found"
]);

}