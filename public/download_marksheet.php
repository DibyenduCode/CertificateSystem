<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/functions.php";
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../vendor/autoload.php";

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
mentors.signature AS mentor_signature,
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

/* Base64 encode Examination Controller Signature */
$signature_base64 = null;
if (!empty($student['mentor_signature'])) {
    $sig_filepath = __DIR__ . "/../uploads/" . $student['mentor_signature'];
    if (file_exists($sig_filepath)) {
        $sig_type = pathinfo($sig_filepath, PATHINFO_EXTENSION);
        $sig_data = file_get_contents($sig_filepath);
        $signature_base64 = 'data:image/' . $sig_type . ';base64,' . base64_encode($sig_data);
    }
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

include __DIR__ . "/../templates/marksheet-template.php";

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

// Clear all active output buffers to prevent corruption or random filenames
while (ob_get_level()) {
    ob_end_clean();
}

$pdfContent = $dompdf->output();
$filename   = "marksheet-" . $certificate_number . ".pdf";

header("Content-Type: application/pdf");
header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
header("Content-Length: " . strlen($pdfContent));
header("Cache-Control: private, max-age=0, must-revalidate");
header("Pragma: public");

echo $pdfContent;
exit;
