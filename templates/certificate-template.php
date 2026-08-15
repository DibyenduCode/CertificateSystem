<?php
// Base64 encoding for background image to ensure Dompdf renders it reliably
$bg_base64 = '';
if (!empty($bg_image_base64)) {
    $bg_base64 = $bg_image_base64;
} elseif (!empty($data['bg_image_path']) && file_exists($data['bg_image_path'])) {
    $type = pathinfo($data['bg_image_path'], PATHINFO_EXTENSION);
    $imgData = file_get_contents($data['bg_image_path']);
    $bg_base64 = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
} else {
    $default_bg = __DIR__ . '/../assets/certificate-bg.png';
    if (file_exists($default_bg)) {
        $type = pathinfo($default_bg, PATHINFO_EXTENSION);
        $imgData = file_get_contents($default_bg);
        $bg_base64 = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
    }
}

$registration_number = $registration_number ?? ($data['registration_number'] ?? '');
$certificate_number  = $certificate_number ?? ($data['certificate_number'] ?? '');
$issue_date          = $issue_date ?? ($data['issue_date'] ?? date('Y-m-d'));
$gender              = $data['gender'] ?? 'Male';

$raw_title = $title ?? (function_exists('genderTitle') ? genderTitle($gender) : ($gender === 'Female' ? 'Ms.' : 'Mr.'));
$title = trim($raw_title);
if ($title !== '' && !str_ends_with($title, '.')) {
    $title .= '.';
}

$student_name        = strtoupper($student_name ?? ($data['name'] ?? ''));
$father_name         = strtoupper($father_name ?? ($data['father_name'] ?? ''));
$course              = strtoupper($course ?? ($data['course'] ?? ''));
$institute           = strtoupper($institute ?? ($data['institute'] ?? ''));
$theory_m = (int)($data['theory_marks'] ?? 0);
$prac_m   = (int)($data['practical_marks'] ?? 0);
$computed_grade = function_exists('calculateGrade') ? calculateGrade($theory_m, $prac_m) : 'VERY GOOD';
$grade_input = trim($grade ?? ($data['grade'] ?? ''));
if (empty($grade_input) || in_array(strtoupper($grade_input), ['PASS', 'A+', 'A', 'B', 'C', 'DEFAULT'])) {
    $grade = $computed_grade;
} else {
    $grade = strtoupper($grade_input);
}

$start_d = $data['start_date'] ?? 'now';
$end_d   = $data['end_date'] ?? 'now';
$training_period = date('F Y', strtotime($start_d)) . ' to ' . date('F Y', strtotime($end_d));
$award_date = date('d/m/Y', strtotime($issue_date));
$relation   = ($gender === 'Female') ? 'Daughter of' : 'Son of';
$pronoun    = ($gender === 'Female') ? 'She' : 'He';
$possessive = ($gender === 'Female') ? 'her' : 'his';

if (!empty($father_name) && !str_starts_with($father_name, 'MR.') && !str_starts_with($father_name, 'MS.')) {
    $father_display = 'Mr. ' . $father_name;
} else {
    $father_display = $father_name;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate - <?= htmlspecialchars($certificate_number) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            background-color: #ffffff;
        }

        .cert-bg-img {
            position: fixed;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            z-index: -1000;
        }

        .cert-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            box-sizing: border-box;
        }

        /* Regd. No and Certificate No Row */
        .numbers-row {
            position: absolute;
            top: 104mm;
            left: 20mm;
            width: 257mm;
            font-size: 14px;
            font-weight: normal;
            color: #000000;
        }
        .regd-no {
            float: left;
        }
        .cert-no {
            float: right;
        }

        /* Main Body Text Container */
        .body-container {
            position: absolute;
            top: 112mm;
            left: 20mm;
            width: 215mm;
            font-size: 14px;
            line-height: 1.65;
            color: #000000;
            text-align: justify;
        }

        .bi {
            font-weight: bold;
            font-style: italic;
        }

        .b {
            font-weight: bold;
        }

        .award-line {
            margin-top: 4mm;
            font-size: 14px;
        }

        /* Student Photo Box on Right */
        .photo-box-wrapper {
            position: absolute;
            top: 114mm;
            right: 20mm;
            width: 27mm;
            height: 33mm;
            border: 1px solid #000000;
            background: #ffffff;
            text-align: center;
            box-sizing: border-box;
        }
        .photo-box-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* QR Code Box at Bottom Left */
        .qr-code-wrapper {
            position: absolute;
            top: 154mm;
            left: 20mm;
            width: 27mm;
            height: 27mm;
        }
        .qr-code-wrapper img {
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>

<?php if (!empty($bg_base64)): ?>
    <img src="<?= $bg_base64 ?>" class="cert-bg-img" alt="Certificate Background">
<?php endif; ?>

<div class="cert-container">

    <!-- REGD NO AND CERTIFICATE NO OVERLAY -->
    <div class="numbers-row">
        <div class="regd-no">Regd. No. : <?= htmlspecialchars($registration_number) ?></div>
        <div class="cert-no">Certificate No: <?= htmlspecialchars($certificate_number) ?></div>
    </div>

    <!-- MAIN DYNAMIC BODY PARAGRAPH -->
    <div class="body-container">
        This is to certify that <span class="bi"><?= htmlspecialchars($title ? $title . ' ' : '') ?><?= htmlspecialchars($student_name) ?>, <?= $relation ?> <?= htmlspecialchars($father_display) ?></span>, has successfully completed the <span class="bi"><?= htmlspecialchars($course) ?></span> conducted at our authorised study center, <span class="b"><?= htmlspecialchars($institute) ?></span>. <?= $pronoun ?> has demonstrated commendable performance throughout the training period from <span class="bi"><?= htmlspecialchars($training_period) ?></span>, and <?= $possessive ?> overall performance was evaluated as <span class="bi"><?= htmlspecialchars($grade) ?>.</span>
        
        <div class="award-line">
            This certificate is awarded on this <span class="bi"><?= htmlspecialchars($award_date) ?></span>
        </div>
    </div>

    <!-- STUDENT PHOTO (RIGHT SIDE) -->
    <?php
    $photo_file = __DIR__ . "/../uploads/" . ($data['student_photo'] ?? '');
    if (!empty($data['student_photo']) && file_exists($photo_file)):
        $type = pathinfo($photo_file, PATHINFO_EXTENSION);
        $image_data = file_get_contents($photo_file);
        $photo_base64 = "data:image/" . $type . ";base64," . base64_encode($image_data);
    ?>
        <div class="photo-box-wrapper">
            <img src="<?= $photo_base64 ?>" alt="Student Photo">
        </div>
    <?php endif; ?>

    <!-- QR CODE FOR VERIFICATION (BOTTOM LEFT) -->
    <?php if (!empty($qr_code_url)): ?>
        <div class="qr-code-wrapper">
            <img src="<?= $qr_code_url ?>" alt="QR Code">
        </div>
    <?php endif; ?>

</div>

</body>
</html>