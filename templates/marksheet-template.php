<?php
// Base64 encoding for marksheet background image to ensure Dompdf renders it reliably
$bg_base64 = '';
$bg_file = __DIR__ . '/../assets/marksheet-bg.png';
if (file_exists($bg_file)) {
    $type = pathinfo($bg_file, PATHINFO_EXTENSION);
    $imgData = file_get_contents($bg_file);
    $bg_base64 = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
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

$theory_marks        = (int)($data['theory_marks'] ?? 0);
$practical_marks     = (int)($data['practical_marks'] ?? 0);
$total_obtained      = $theory_marks + $practical_marks;
$max_marks           = 200; // 100 Theory + 100 Practical
$pass_marks          = 100;
$pct_val             = ($total_obtained / $max_marks) * 100;
$percentage          = number_format($pct_val, 0);

// Fetch course subjects if not already supplied
$subjects = $subjects ?? [];
if (empty($subjects) && !empty($pdo) && !empty($data['course_id'])) {
    $stmt_subj = $pdo->prepare("SELECT name FROM subjects WHERE course_id = ? ORDER BY id ASC");
    $stmt_subj->execute([$data['course_id']]);
    $subjects = $stmt_subj->fetchAll(PDO::FETCH_COLUMN);
}

// Parse subjects cleanly line-by-line
$clean_subjects = [];
if (!empty($subjects)) {
    $raw_items = is_array($subjects) ? $subjects : [$subjects];
    foreach ($raw_items as $item) {
        $str = is_array($item) ? ($item['name'] ?? '') : (string)$item;
        $split = preg_split('/[\n\r,;]+/', $str);
        foreach ($split as $part) {
            $part = trim($part);
            if (!empty($part)) {
                $clean_subjects[] = $part;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Official Statement of Marks - <?= htmlspecialchars($certificate_number) ?></title>
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

        .marks-bg-img {
            position: fixed;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            z-index: -1000;
        }

        .marks-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            box-sizing: border-box;
        }

        /* STUDENT IDENTITY HEADER BLOCK (Positioned below STATEMENT OF MARKS) */
        .student-info-table {
            position: absolute;
            top: 94mm;
            left: 24mm;
            width: 249mm;
            border-collapse: collapse;
            font-size: 11pt;
            font-weight: bold;
            color: #000000;
        }

        .student-info-table td {
            padding: 3.5px 0;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #000000;
        }

        .info-val {
            font-weight: 900;
            color: #000000;
        }

        /* MARKS TABLE OVERLAY */
        .marks-table-wrapper {
            position: absolute;
            top: 122mm;
            left: 24mm;
            width: 249mm;
        }

        .marks-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000000;
            background-color: transparent;
        }

        .marks-table th, .marks-table td {
            border: 1.5px solid #000000;
            padding: 5px 8px;
            font-size: 10.5pt;
            color: #000000;
        }

        .marks-table th {
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            background-color: transparent;
        }

        .marks-table td {
            font-weight: bold;
        }

        .col-subj-title {
            text-align: left;
            padding-left: 12px !important;
        }

        .col-num {
            text-align: center;
        }

        /* SUMMARY FOOTER BAR */
        .marks-summary-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000000;
            border-top: none;
        }

        .marks-summary-table td {
            border: 1.5px solid #000000;
            padding: 6px 4px;
            font-size: 10pt;
            font-weight: 900;
            text-align: center;
            color: #000000;
            text-transform: uppercase;
        }

        /* EXAMINATION CONTROLLER SIGNATURE OVERLAY (RESTING FLUSH ON TOP OF LINE) */
        .exam-controller-sig-wrapper {
            position: absolute;
            top: 171.5mm;
            left: 88mm;
            width: 75mm;
            height: 14mm;
            text-align: center;
        }

        .exam-controller-sig-img {
            height: 14mm;
            max-width: 70mm;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<?php
if (empty($signature_base64) && !empty($data['mentor_signature'])) {
    $sig_file = __DIR__ . '/../uploads/' . $data['mentor_signature'];
    if (file_exists($sig_file)) {
        $sig_type = pathinfo($sig_file, PATHINFO_EXTENSION);
        $sig_data = file_get_contents($sig_file);
        $signature_base64 = 'data:image/' . $sig_type . ';base64,' . base64_encode($sig_data);
    }
}
?>

<?php if (!empty($bg_base64)): ?>
    <img src="<?= $bg_base64 ?>" class="marks-bg-img" alt="Statement of Marks Background">
<?php endif; ?>

<div class="marks-container">

    <!-- STUDENT & COURSE IDENTITY SECTION -->
    <table class="student-info-table">
        <tr>
            <td style="width: 58%;">
                <span class="info-label">STUDENT NAME:</span> <span class="info-val"><?= htmlspecialchars($title ? $title . ' ' : '') ?><?= htmlspecialchars($student_name) ?></span>
            </td>
            <td style="width: 42%;">
                <span class="info-label">TRAINING CENTRE:</span> <span class="info-val"><?= htmlspecialchars($institute) ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="info-label">REGISTRATION NO:</span> <span class="info-val"><?= htmlspecialchars($registration_number) ?></span>
            </td>
            <td>
                <span class="info-label">RESULT DECLARED ON:</span> <span class="info-val"><?= date('d/m/Y', strtotime($issue_date)) ?></span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="info-label">COURSE NAME:</span> <span class="info-val"><?= htmlspecialchars($course) ?></span>
            </td>
        </tr>
    </table>

    <!-- MARKS TABLE SECTION -->
    <div class="marks-table-wrapper">
        <table class="marks-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 50%;">SUBJECTS</th>
                    <th colspan="3" style="width: 50%;">SCHEME OF MARKS</th>
                </tr>
                <tr>
                    <th style="width: 16%;">THEORY</th>
                    <th style="width: 16%;">PRACTICAL</th>
                    <th style="width: 18%;">MARKS OBTAINED</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $display_subjects = !empty($clean_subjects) ? implode(', ', array_map('strtoupper', $clean_subjects)) : $course;
                ?>
                <tr>
                    <td class="col-subj-title"><?= htmlspecialchars($display_subjects) ?></td>
                    <td class="col-num"><?= $theory_marks ?></td>
                    <td class="col-num"><?= $practical_marks ?></td>
                    <td class="col-num"><?= $total_obtained ?></td>
                </tr>
            </tbody>
        </table>

        <!-- SUMMARY FOOTER BAR -->
        <table class="marks-summary-table">
            <tr>
                <td style="width: 25%;">FULL MARKS: 200</td>
                <td style="width: 25%;">PASS MARKS: 100</td>
                <td style="width: 28%;">TOTAL MARKS OBTAINED: <?= $total_obtained ?></td>
                <td style="width: 22%;">PERCENTAGE: <?= $percentage ?>%</td>
            </tr>
        </table>
    </div>

    <!-- EXAMINATION CONTROLLER SIGNATURE (BOTTOM LEFT - ABOVE 'EXAMINATION CONTROLLER') -->
    <?php if (!empty($signature_base64)): ?>
        <div class="exam-controller-sig-wrapper">
            <img src="<?= $signature_base64 ?>" class="exam-controller-sig-img" alt="Examination Controller Signature">
        </div>
    <?php endif; ?>

</div>

</div>

</body>
</html>

