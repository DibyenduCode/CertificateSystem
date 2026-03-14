<style>

@page{
size:A4 landscape;
margin:0;
}

body{
margin:0;
font-family: DejaVu Sans, sans-serif;
}

/* main certificate container */

.certificate{

position:relative;

width:297mm;
height:210mm;

background-image:url('https://dibyendu.in/certificate-bg.png');
background-size:cover;
background-position:center;

}


/* registration line */

.regline{

position:absolute;

top:103mm;
left:20mm;
right:20mm;

font-size:15px;
font-weight:bold;

}


/* paragraph text */

.text{

position:absolute;

top:120mm;
left:40mm;
right:90mm;

font-size:15px;
line-height:1.7;

}


/* student photo */

.photo{

position:absolute;

top:115mm;
right:40mm;

width:35mm;
height:40mm;

border:1px solid #aaa;
overflow:hidden;

}

.photo img{

width:100%;
height:100%;
object-fit:cover;

}

</style>



<div class="certificate">

<!-- Registration + Certificate Number -->

<div class="regline">

Registration No: <?= htmlspecialchars($registration_number) ?>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



Certificate No: <?= htmlspecialchars($certificate_number) ?>

</div>



<!-- Certificate Text -->

<div class="text">

<?= $title ?> <?= htmlspecialchars($student_name) ?>

<?= $data['gender']=="Male" ? "S/O" : "D/O" ?>

<?= htmlspecialchars($father_name) ?>

has successfully completed the course

<b><?= htmlspecialchars($course) ?></b>

from <?= htmlspecialchars($institute) ?>

under the training program of

<b><?= htmlspecialchars($organization) ?></b>. Duration: <?= htmlspecialchars($duration) ?> Performance: <b><?= htmlspecialchars($grade) ?></b> Training Period: <?= htmlspecialchars($training_period) ?> This certificate is awarded on <b><?= date("d M Y",strtotime($issue_date)) ?></b>
</div>



<!-- Student Photo -->

<div class="photo">

<?php

$photo_file = __DIR__ . "/../uploads/students/" . $data['student_photo'];

if(!empty($data['student_photo']) && file_exists($photo_file))
{

$type = pathinfo($photo_file, PATHINFO_EXTENSION);

$image_data = file_get_contents($photo_file);

$student_photo = "data:image/".$type.";base64," . base64_encode($image_data);

?>

<img src="<?= $student_photo ?>">

<?php } ?>

</div>


</div>