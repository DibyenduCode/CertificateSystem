<?php

require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;

$cert = $_GET['cert'] ?? null;

if(!$cert){
die("Invalid Certificate");
}

$stmt = $pdo->prepare("
SELECT students.*,courses.name AS course,mentors.name AS mentor
FROM students
LEFT JOIN courses ON courses.id = students.course_id
LEFT JOIN mentors ON mentors.id = students.mentor_id
WHERE certificate_number=?
");

$stmt->execute([$cert]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$student){
die("Certificate Not Found");
}

/* FIX: provide variables for template */

$data = $student;
$title = genderTitle($student['gender']);

/* generate certificate */

ob_start();

include "../templates/certificate-template.php";

$html = ob_get_clean();

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper("A4","landscape");

$dompdf->render();

$dompdf->stream("certificate.pdf",[
"Attachment"=>1
]);