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

SELECT students.*,
courses.name AS course,
mentors.name AS mentor,
organizations.name AS organization,
institutes.name AS institute

FROM students

LEFT JOIN courses ON courses.id = students.course_id
LEFT JOIN mentors ON mentors.id = students.mentor_id
LEFT JOIN organizations ON organizations.id = students.organization_id
LEFT JOIN institutes ON institutes.id = students.institute_id

WHERE certificate_number=?

");

$stmt->execute([$cert]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$student){
die("Certificate Not Found");
}

$data = $student;
$title = genderTitle($student['gender']);

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