<?php
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

$search = $_GET['search'] ?? '';
$course_id = $_GET['course_id'] ?? '';
$institute_id = $_GET['institute_id'] ?? '';

$where_clauses = [];
$params = [];

if ($search) {
    $where_clauses[] = "(students.name LIKE ? OR students.registration_number LIKE ? OR students.certificate_number LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($course_id) {
    $where_clauses[] = "students.course_id = ?";
    $params[] = $course_id;
}

if ($institute_id) {
    $where_clauses[] = "students.institute_id = ?";
    $params[] = $institute_id;
}

$where = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$sql = "
    SELECT 
        students.registration_number,
        students.certificate_number,
        students.name AS student_name,
        students.father_name,
        students.gender,
        students.dob,
        courses.name AS course_name,
        institutes.name AS institute_name,
        mentors.name AS mentor_name,
        students.grade,
        students.theory_marks,
        students.practical_marks,
        students.start_date,
        students.end_date,
        students.issue_date
    FROM students
    LEFT JOIN courses ON courses.id = students.course_id
    LEFT JOIN institutes ON institutes.id = students.institute_id
    LEFT JOIN mentors ON mentors.id = students.mentor_id
    $where
    ORDER BY students.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "students_export_" . date("Y-m-d_H-i") . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Header Row
fputcsv($output, [
    'Registration Number',
    'Certificate Number',
    'Student Name',
    'Father Name',
    'Gender',
    'DOB',
    'Course',
    'Institute',
    'Mentor',
    'Grade',
    'Theory Marks',
    'Practical Marks',
    'Start Date',
    'End Date',
    'Issue Date'
]);

foreach ($rows as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
