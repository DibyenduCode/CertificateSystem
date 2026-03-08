<?php

require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

$reg = $_GET['reg'];

$stmt = $pdo->prepare("
SELECT students.*,courses.name AS course,mentors.name AS mentor
FROM students
LEFT JOIN courses ON courses.id = students.course_id
LEFT JOIN mentors ON mentors.id = students.mentor_id
WHERE registration_number=?
");

$stmt->execute([$reg]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$data){
    die("Invalid Certificate");
}

$title = genderTitle($data['gender']);

ob_start();

include "../templates/certificate-template.php";

$html = ob_get_clean();



/* ---------- DOMPDF OPTIONS ---------- */

$options = new Options();

$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Serif');

$dompdf = new Dompdf($options);


/* ---------- LOAD HTML ---------- */

$dompdf->loadHtml($html);


/* ---------- A4 LANDSCAPE ---------- */

$dompdf->setPaper('A4', 'landscape');



/* ---------- RENDER PDF ---------- */

$dompdf->render();


/* ---------- DOWNLOAD ---------- */

$dompdf->stream("certificate.pdf", [
    "Attachment" => true
]);