<?php

require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../config/config.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

/* ---------------------------
   GET CERTIFICATE NUMBER
--------------------------- */

$cert = $_GET['cert'] ?? null;

if(!$cert){
    die("Invalid Certificate Number");
}

/* ---------------------------
   FETCH STUDENT DATA
--------------------------- */

$stmt = $pdo->prepare("
SELECT
students.*,
courses.name AS course,
mentors.name AS mentor,
institutes.name AS institute

FROM students

LEFT JOIN courses
ON courses.id = students.course_id

LEFT JOIN mentors
ON mentors.id = students.mentor_id

LEFT JOIN institutes
ON institutes.id = students.institute_id

WHERE students.certificate_number = ?
");

$stmt->execute([$cert]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$student){
    die("Student Record Not Found");
}

/* ---------------------------
   PREPARE TEMPLATE DATA
--------------------------- */

$data = $student;

$title = genderTitle($student['gender']);

$student_name = $student['name'];
$father_name = $student['father_name'];

$course = $student['course'];
$mentor = $student['mentor'];

$institute = $student['institute'];

$registration_number = $student['registration_number'];
$certificate_number = $student['certificate_number'];

$verify_url = BASE_URL . "/public/verify.php?cert=" . urlencode($certificate_number);
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verify_url);

$start_date = $student['start_date'];
$end_date = $student['end_date'];

$issue_date = $student['issue_date'];
$grade = $student['grade'];
$theory_marks = (int)($student['theory_marks'] ?? 0);
$practical_marks = (int)($student['practical_marks'] ?? 0);

$photo_filepath = __DIR__ . "/../uploads/" . $student['student_photo'];
if (!empty($student['student_photo']) && file_exists($photo_filepath)) {
    $student_photo = $photo_filepath;
} else {
    $student_photo = null;
}

/* training period */

$training_period =
date("d M Y",strtotime($start_date))
." – ".
date("d M Y",strtotime($end_date));


/* ---------------------------
   FETCH COURSE SUBJECTS
--------------------------- */

$subjects = [];
if (!empty($student['course_id'])) {
    $sub_stmt = $pdo->prepare("SELECT name FROM subjects WHERE course_id = ? ORDER BY id ASC");
    $sub_stmt->execute([$student['course_id']]);
    $subjects = $sub_stmt->fetchAll(PDO::FETCH_COLUMN);
}


/* ---------------------------
   GENERATE MARKSHEET HTML
--------------------------- */

ob_start();

include "../templates/marksheet-template.php";

$html = ob_get_clean();

/* ---------------------------
   DOMPDF SETTINGS
--------------------------- */

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper("A4","landscape");

$dompdf->render();

/* ---------------------------
   DOWNLOAD PDF
--------------------------- */

$dompdf->stream("marksheet-".$certificate_number.".pdf",[
    "Attachment" => 1
]);
