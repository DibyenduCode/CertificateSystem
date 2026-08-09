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
$pct_val             = ($total_obtained / $max_marks) * 100;
$percentage          = number_format($pct_val, 0);

// Fetch course subjects if not already supplied
$subjects = $subjects ?? [];
if (empty($subjects) && !empty($pdo) && !empty($data['course_id'])) {
    $stmt_subj = $pdo->prepare("SELECT name FROM subjects WHERE course_id = ? ORDER BY id ASC");
    $stmt_subj->execute([$data['course_id']]);
    $subjects = $stmt_subj->fetchAll(PDO::FETCH_COLUMN);
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
            font-family: 'Helvetica Neue', Helvetica, Arial, 'DejaVu Sans', sans-serif;
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

        /* STUDENT IDENTITY OVERLAY */
        .lbl-student-name {
            position: absolute;
            top: 94.5mm;
            left: 60mm;
            font-size: 11.5px;
            font-weight: bold;
            color: #000000;
        }

        .lbl-reg-no {
            position: absolute;
            top: 100mm;
            left: 60mm;
            font-size: 11.5px;
            font-weight: bold;
            color: #000000;
        }

        .lbl-course {
            position: absolute;
            top: 105.5mm;
            left: 60mm;
            width: 105mm;
            font-size: 11px;
            font-weight: bold;
            color: #000000;
            line-height: 1.2;
        }

        .lbl-center {
            position: absolute;
            top: 94.5mm;
            left: 204mm;
            width: 65mm;
            font-size: 11px;
            font-weight: bold;
            color: #000000;
            line-height: 1.2;
        }

        .lbl-date {
            position: absolute;
            top: 100mm;
            left: 204mm;
            font-size: 11.5px;
            font-weight: bold;
            color: #000000;
        }

        /* SUBJECTS AND MARKS TABLE OVERLAY */
        .table-rows-container {
            position: absolute;
            top: 132mm;
            left: 28mm;
            width: 241mm;
            font-size: 10.5px;
            color: #000000;
        }

        .subject-row {
            height: 5.5mm;
            line-height: 5.5mm;
            clear: both;
        }

        .col-subjects {
            float: left;
            width: 142mm;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .col-theory {
            float: left;
            width: 27mm;
            text-align: center;
            font-weight: bold;
        }

        .col-practical {
            float: left;
            width: 27mm;
            text-align: center;
            font-weight: bold;
        }

        .col-obtained {
            float: left;
            width: 45mm;
            text-align: center;
            font-weight: bold;
        }

        /* SUMMARY ROW OVERLAY */
        .lbl-total-obtained {
            position: absolute;
            top: 156.5mm;
            left: 222mm;
            font-size: 11.5px;
            font-weight: bold;
            color: #000000;
        }

        .lbl-percentage {
            position: absolute;
            top: 156.5mm;
            left: 260mm;
            font-size: 11.5px;
            font-weight: bold;
            color: #000000;
        }
    </style>
</head>
<body>

<?php if (!empty($bg_base64)): ?>
    <img src="<?= $bg_base64 ?>" class="marks-bg-img" alt="Statement of Marks Background">
<?php endif; ?>

<div class="marks-container">

    <!-- STUDENT IDENTITY OVERLAYS -->
    <div class="lbl-student-name"><?= htmlspecialchars($title ? $title . ' ' : '') ?><?= htmlspecialchars($student_name) ?></div>
    <div class="lbl-reg-no"><?= htmlspecialchars($registration_number) ?></div>
    <div class="lbl-course"><?= htmlspecialchars($course) ?></div>

    <div class="lbl-center"><?= htmlspecialchars($institute) ?></div>
    <div class="lbl-date"><?= date('d/m/Y', strtotime($issue_date)) ?></div>

    <!-- SUBJECTS & MARKS TABLE ROWS OVERLAY -->
    <div class="table-rows-container">
        <?php
        $clean_subjects = array_values(array_filter(array_map(function($s) {
            return is_array($s) ? ($s['name'] ?? '') : $s;
        }, $subjects)));

        if (!empty($clean_subjects)):
            $num_subjects = count($clean_subjects);
            // Distribute theory & practical marks proportionately across subject rows
            $per_subject_theory = (int)ceil($theory_marks / $num_subjects);
            $per_subject_prac   = (int)ceil($practical_marks / $num_subjects);

            foreach (array_slice($clean_subjects, 0, 5) as $idx => $subj_name):
                $sub_theory = min(100, $per_subject_theory);
                $sub_prac   = min(100, $per_subject_prac);
                $sub_total  = $sub_theory + $sub_prac;
        ?>
            <div class="subject-row">
                <div class="col-subjects"><?= ($idx + 1) ?>. <?= htmlspecialchars(strtoupper($subj_name)) ?></div>
                <div class="col-theory"><?= $sub_theory ?></div>
                <div class="col-practical"><?= $sub_prac ?></div>
                <div class="col-obtained"><?= $sub_total ?></div>
            </div>
        <?php 
            endforeach;
        else:
        ?>
            <!-- Default single summary row if no individual subjects defined -->
            <div class="subject-row">
                <div class="col-subjects">1. <?= htmlspecialchars($course) ?></div>
                <div class="col-theory"><?= $theory_marks ?></div>
                <div class="col-practical"><?= $practical_marks ?></div>
                <div class="col-obtained"><?= $total_obtained ?></div>
            </div>
        <?php endif; ?>
    </div>

    <!-- TOTAL MARKS & PERCENTAGE OVERLAY -->
    <div class="lbl-total-obtained"><?= $total_obtained ?></div>
    <div class="lbl-percentage"><?= $percentage ?>%</div>

</div>

</body>
</html>
