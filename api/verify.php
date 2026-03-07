<?php

require_once "../config/database.php";

$headers = getallheaders();

$api_key = $headers['X-API-KEY'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key=? AND status='active'");
$stmt->execute([$api_key]);

if(!$stmt->fetch()){
    http_response_code(403);
    echo json_encode(["error"=>"Invalid API Key"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"),true);

$reg = $data['registration_number'];
$dob = $data['dob'];

$stmt = $pdo->prepare("
SELECT students.*,courses.name AS course,mentors.name AS mentor
FROM students
LEFT JOIN courses ON courses.id = students.course_id
LEFT JOIN mentors ON mentors.id = students.mentor_id
WHERE registration_number=? AND dob=?
");

$stmt->execute([$reg,$dob]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if($student){

echo json_encode([
"status"=>"verified",
"name"=>$student['name'],
"course"=>$student['course'],
"mentor"=>$student['mentor'],
"grade"=>$student['grade'],
"certificate_download_url"=>"https://domain.com/public/download.php?reg=".$student['registration_number']
]);

}else{

echo json_encode(["status"=>"not_found"]);

}