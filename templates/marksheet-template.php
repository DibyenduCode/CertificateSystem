<?php
// Marksheet PDF Template
$registration_number = $registration_number ?? ($data['registration_number'] ?? '');
$certificate_number  = $certificate_number ?? ($data['certificate_number'] ?? '');
$issue_date          = $issue_date ?? ($data['issue_date'] ?? date('Y-m-d'));
$dob                 = $dob ?? ($data['dob'] ?? '');
$title               = $title ?? (function_exists('genderTitle') ? genderTitle($data['gender'] ?? 'Male') : 'Mr.');
$student_name        = $student_name ?? ($data['name'] ?? '');
$father_name         = $father_name ?? ($data['father_name'] ?? '');
$course              = $course ?? ($data['course'] ?? '');
$institute           = $institute ?? ($data['institute'] ?? '');
$theory_marks        = (int)($data['theory_marks'] ?? 0);
$practical_marks     = (int)($data['practical_marks'] ?? 0);
$total_obtained      = $theory_marks + $practical_marks;
$max_marks           = 200; // 100 Theory + 100 Practical
$pct_val             = ($total_obtained / $max_marks) * 100;
$percentage          = number_format($pct_val, 2);

$computed_letter_grade = function_exists('calculateMarksheetGrade') ? calculateMarksheetGrade($theory_marks, $practical_marks) : 'A+';
$grade_input           = trim($grade ?? ($data['grade'] ?? ''));
if (empty($grade_input) || in_array(strtoupper($grade_input), ['PASS', 'EXCELLENT', 'VERY GOOD', 'GOOD', 'FAIR', 'DEFAULT', 'AUTO'])) {
    $grade = $computed_letter_grade;
} else {
    $grade = strtoupper($grade_input);
}

$result_status       = ($pct_val >= 80) ? "PASSED WITH DISTINCTION" : (($pct_val >= 70) ? "PASSED (FIRST CLASS)" : (($pct_val >= 60) ? "PASSED (SECOND CLASS)" : (($pct_val >= 50) ? "PASSED" : "FAILED")));
$training_period     = $training_period ?? (date("d M Y", strtotime($data['start_date'] ?? 'now')) . " – " . date("d M Y", strtotime($data['end_date'] ?? 'now')));

// Fetch course subjects if not already supplied
$subjects = $subjects ?? [];
if (empty($subjects) && !empty($pdo) && !empty($data['course_id'])) {
    $stmt_subj = $pdo->prepare("SELECT name FROM subjects WHERE course_id = ? ORDER BY id ASC");
    $stmt_subj->execute([$data['course_id']]);
    $subjects = $stmt_subj->fetchAll(PDO::FETCH_COLUMN);
}

// Logo base64
$logo_base64 = '';
$logo_file = __DIR__ . '/../assets/logo.png';
if (file_exists($logo_file)) {
    $type = pathinfo($logo_file, PATHINFO_EXTENSION);
    $imgData = file_get_contents($logo_file);
    $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Official Marksheet - <?= htmlspecialchars($certificate_number) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e293b;
            background-color: #ffffff;
            font-size: 11px;
        }

        .marksheet-box {
            border: 3px double #1e3a8a;
            padding: 10mm;
            min-height: 260mm;
            box-sizing: border-box;
            position: relative;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 5mm;
            margin-bottom: 6mm;
        }

        .header-title {
            text-align: center;
        }

        .header-title h1 {
            font-size: 20px;
            color: #1e3a8a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-title h2 {
            font-size: 13px;
            color: #475569;
            margin: 3px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-title p {
            font-size: 10px;
            color: #64748b;
            margin: 2px 0 0 0;
        }

        .document-heading {
            text-align: center;
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 6px 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 6mm;
            border-radius: 4px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6mm;
        }

        .info-table td {
            padding: 5px 8px;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #475569;
            width: 25%;
            text-transform: uppercase;
            font-size: 9.5px;
        }

        .info-value {
            color: #0f172a;
            font-weight: bold;
            width: 25%;
            font-size: 10.5px;
        }

        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6mm;
        }

        .marks-table th, .marks-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: center;
        }

        .marks-table th {
            background-color: #f1f5f9;
            color: #1e3a8a;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }

        .marks-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .marks-table .text-left {
            text-align: left;
        }

        .total-row td {
            font-weight: bold;
            background-color: #eff6ff !important;
            color: #1e3a8a;
            border-top: 2px solid #1e3a8a;
            font-size: 11px;
        }

        .summary-card {
            border: 1px solid #93c5fd;
            background-color: #f0f9ff;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 8mm;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 4px 6px;
            text-align: center;
        }

        .summary-title {
            font-size: 9px;
            color: #0369a1;
            text-transform: uppercase;
            font-weight: bold;
        }

        .summary-value {
            font-size: 14px;
            color: #0c4a6e;
            font-weight: bold;
            margin-top: 2px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15mm;
        }

        .footer-table td {
            vertical-align: bottom;
        }

        .sig-box {
            text-align: center;
            width: 50mm;
        }

        .sig-line {
            border-top: 1.5px solid #475569;
            margin-top: 12mm;
            padding-top: 4px;
            font-weight: bold;
            color: #1e293b;
            font-size: 10px;
        }

        .sig-sub {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
        }

        .qr-box {
            text-align: center;
        }

        .qr-box img {
            width: 22mm;
            height: 22mm;
            border: 1px solid #cbd5e1;
            padding: 1mm;
            background: #ffffff;
        }

        .photo-box {
            width: 24mm;
            height: 28mm;
            border: 1px solid #cbd5e1;
            padding: 1mm;
            background: #ffffff;
            text-align: center;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="marksheet-box">

    <!-- HEADER BRANDING -->
    <table class="header-table">
        <tr>
            <td style="width: 20%; text-align: left;">
                <?php if (!empty($logo_base64)): ?>
                    <img src="<?= $logo_base64 ?>" style="max-height: 20mm; max-width: 35mm;" alt="Logo">
                <?php endif; ?>
            </td>
            <td class="header-title" style="width: 60%;">
                <h1><?= htmlspecialchars($institute ?: 'INSTITUTE OF ACADEMIC EXCELLENCE') ?></h1>
                <h2>Official Statement of Marks & Evaluation</h2>
                <p>Authorized Digital Registry & Academic Verification System</p>
            </td>
            <td style="width: 20%; text-align: right; font-family: monospace; font-size: 9px; line-height: 1.4;">
                <div><strong>Reg No:</strong><br><?= htmlspecialchars($registration_number) ?></div>
                <div style="margin-top: 4px;"><strong>Cert No:</strong><br><?= htmlspecialchars($certificate_number) ?></div>
            </td>
        </tr>
    </table>

    <!-- DOCUMENT HEADING -->
    <div class="document-heading">Academic Marksheet</div>

    <!-- STUDENT IDENTITY TABLE -->
    <table class="info-table">
        <tr>
            <td class="info-label">Student Name:</td>
            <td class="info-value"><?= htmlspecialchars($title ? $title . ' ' : '') ?><?= htmlspecialchars($student_name) ?></td>
            <td class="info-label">Father's Name:</td>
            <td class="info-value"><?= htmlspecialchars($father_name) ?></td>
        </tr>
        <tr>
            <td class="info-label">Course Enrolled:</td>
            <td class="info-value"><?= htmlspecialchars($course) ?></td>
            <td class="info-label">Course Duration:</td>
            <td class="info-value"><?= htmlspecialchars($training_period) ?></td>
        </tr>
        <tr>
            <td class="info-label">Date of Birth:</td>
            <td class="info-value"><?= !empty($dob) ? date("d M Y", strtotime($dob)) : 'N/A' ?></td>
            <td class="info-label">Date of Issue:</td>
            <td class="info-value"><?= date("d M Y", strtotime($issue_date)) ?></td>
        </tr>
    </table>

    <!-- COURSE SUBJECTS / CURRICULUM COVERED -->
    <?php if (!empty($subjects) && is_array($subjects)): ?>
        <table class="marks-table" style="margin-bottom: 5mm;">
            <thead>
                <tr>
                    <th colspan="2" class="text-left" style="background-color: #1e3a8a; color: #ffffff; font-size: 10px; padding: 5px 10px;">
                        SUBJECTS &amp; CURRICULUM COVERED
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" class="text-left" style="padding: 6px 10px; background-color: #f8fafc;">
                        <table style="width: 100%; border-collapse: collapse; border: none;">
                            <?php 
                            $subj_names = array_values(array_filter(array_map(function($s) {
                                return is_array($s) ? ($s['name'] ?? '') : $s;
                            }, $subjects)));
                            $total_subs = count($subj_names);
                            for ($idx = 0; $idx < $total_subs; $idx += 2): 
                            ?>
                                <tr>
                                    <td style="width: 50%; border: none; padding: 2px 4px; font-size: 9.5px; color: #1e293b;">
                                        <strong><?= ($idx + 1) ?>.</strong> <?= htmlspecialchars($subj_names[$idx]) ?>
                                    </td>
                                    <td style="width: 50%; border: none; padding: 2px 4px; font-size: 9.5px; color: #1e293b;">
                                        <?php if (isset($subj_names[$idx + 1])): ?>
                                            <strong><?= ($idx + 2) ?>.</strong> <?= htmlspecialchars($subj_names[$idx + 1]) ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- MARKS DETAILS TABLE -->
    <table class="marks-table">
        <thead>
            <tr>
                <th style="width: 8%;">S.No</th>
                <th class="text-left" style="width: 44%;">Evaluation Module / Component</th>
                <th style="width: 16%;">Maximum Marks</th>
                <th style="width: 16%;">Pass Marks</th>
                <th style="width: 16%;">Marks Obtained</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td class="text-left"><strong>Theory Examination Paper</strong><br><span style="font-size: 8.5px; color: #64748b;">Comprehensive Written Assessment & Core Subject Knowledge</span></td>
                <td>100</td>
                <td>40</td>
                <td><strong><?= $theory_marks ?></strong></td>
            </tr>
            <tr>
                <td>2</td>
                <td class="text-left"><strong>Practical & Project Evaluation</strong><br><span style="font-size: 8.5px; color: #64748b;">Lab Performance, Hands-on Tasks & Technical Viva</span></td>
                <td>100</td>
                <td>40</td>
                <td><strong><?= $practical_marks ?></strong></td>
            </tr>
            <tr class="total-row">
                <td colspan="2" class="text-left" style="text-align: right; padding-right: 15px;">TOTAL MARKS OBTAINED:</td>
                <td>200</td>
                <td>80</td>
                <td><?= $total_obtained ?> / 200</td>
            </tr>
        </tbody>
    </table>

    <!-- PERFORMANCE SUMMARY CARD -->
    <div class="summary-card">
        <table class="summary-table">
            <tr>
                <td style="width: 25%;">
                    <div class="summary-title">Total Marks</div>
                    <div class="summary-value"><?= $total_obtained ?> / 200</div>
                </td>
                <td style="width: 25%;">
                    <div class="summary-title">Percentage</div>
                    <div class="summary-value"><?= $percentage ?> %</div>
                </td>
                <td style="width: 25%;">
                    <div class="summary-title">Overall Grade</div>
                    <div class="summary-value" style="color: #b45309;"><?= htmlspecialchars($grade) ?></div>
                </td>
                <td style="width: 25%;">
                    <div class="summary-title">Result Status</div>
                    <div class="summary-value" style="color: #15803d; font-size: 11px;"><?= $result_status ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER SIGNATURE & QR ROW -->
    <table class="footer-table">
        <tr>
            <td style="width: 30%;">
                <?php
                $photo_file = __DIR__ . "/../uploads/" . ($data['student_photo'] ?? '');
                if (!empty($data['student_photo']) && file_exists($photo_file)):
                    $type = pathinfo($photo_file, PATHINFO_EXTENSION);
                    $image_data = file_get_contents($photo_file);
                    $photo_base64 = "data:image/" . $type . ";base64," . base64_encode($image_data);
                ?>
                    <div class="photo-box">
                        <img src="<?= $photo_base64 ?>" alt="Student Photo">
                    </div>
                <?php endif; ?>
            </td>

            <td style="width: 40%; text-align: center;">
                <?php if (!empty($qr_code_url)): ?>
                    <div class="qr-box">
                        <img src="<?= $qr_code_url ?>" alt="QR Code">
                        <div style="font-size: 8px; color: #64748b; margin-top: 2px; font-weight: bold;">Scan to Verify Online</div>
                    </div>
                <?php endif; ?>
            </td>

            <td style="width: 30%;">
                <div class="sig-box" style="margin-left: auto;">
                    <div class="sig-line">Controller of Examinations</div>
                    <div class="sig-sub">Authorized Signatory</div>
                </div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>
