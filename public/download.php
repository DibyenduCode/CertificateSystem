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
die("Invalid Certificate");
}

/* ---------------------------
   FETCH STUDENT DATA
--------------------------- */

$stmt = $pdo->prepare("
SELECT
students.*,
courses.name AS course,
mentors.name AS mentor,
organizations.name AS organization,
institutes.name AS institute

FROM students

LEFT JOIN courses
ON courses.id = students.course_id

LEFT JOIN mentors
ON mentors.id = students.mentor_id

LEFT JOIN organizations
ON organizations.id = students.organization_id

LEFT JOIN institutes
ON institutes.id = students.institute_id

WHERE students.certificate_number = ?
");

$stmt->execute([$cert]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$student){
die("Certificate Not Found");
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

$organization = $student['organization'];
$institute = $student['institute'];

$registration_number = $student['registration_number'];
$certificate_number = $student['certificate_number'];

$verify_url = BASE_URL . "/public/verify.php?cert=" . urlencode($certificate_number);
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verify_url);

$start_date = $student['start_date'];
$end_date = $student['end_date'];

$issue_date = $student['issue_date'];
$grade = $student['grade'];

$student_photo = $student['student_photo']
? "../uploads/students/".$student['student_photo']
: "../uploads/students/default.png";

/* course duration text */

$duration = date("M Y",strtotime($start_date))
." - ".
date("M Y",strtotime($end_date));

/* training period */

$training_period =
date("d M Y",strtotime($start_date))
." – ".
date("d M Y",strtotime($end_date));


/* ---------------------------
   GENERATE CERTIFICATE HTML
--------------------------- */

ob_start();

include "../templates/certificate-template.php";

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

$dompdf->stream("certificate-".$certificate_number.".pdf",[
"Attachment" => 1
]);