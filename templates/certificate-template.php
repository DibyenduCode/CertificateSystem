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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate - <?= htmlspecialchars($certificate_number ?? '') ?></title>
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
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e293b;
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
            padding: 12mm 18mm 10mm 18mm;
            overflow: hidden;
            background: transparent;
        }

        /* Top Right Metadata (Reg No, Cert No, Date) */
        .meta-number {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9.5px;
            color: #1e293b;
            text-align: right;
            line-height: 1.4;
            float: right;
            margin-top: 4mm;
            margin-right: 4mm;
        }

        /* Certificate Content Area (positioned in open middle section) */
        .cert-content {
            margin-top: 42mm;
            text-align: center;
        }

        .cert-subtitle {
            font-size: 11px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 3mm;
            font-weight: 600;
        }

        .student-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e3a8a;
            display: inline-block;
            border-bottom: 2px solid #d97706;
            padding: 0 15px 2px 15px;
            margin: 1mm 0 3mm 0;
        }

        .body-text {
            font-size: 13px;
            line-height: 1.75;
            color: #334155;
            padding: 0 15mm;
        }

        .highlight {
            font-weight: bold;
            color: #0f172a;
        }

        .course-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 1mm 0;
        }

        /* Bottom Row Table for Photo, QR Code, Signatures */
        .bottom-table {
            position: absolute;
            bottom: 12mm;
            left: 18mm;
            width: 261mm;
            border-collapse: collapse;
        }

        .bottom-table td {
            vertical-align: bottom;
        }

        .photo-box {
            width: 25mm;
            height: 30mm;
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
        .qr-label {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 1mm;
            font-weight: bold;
        }

        .signature-box {
            text-align: center;
            width: 48mm;
        }
        .sig-line {
            border-top: 1.5px solid #64748b;
            margin-top: 8mm;
            padding-top: 1.5mm;
            font-size: 10.5px;
            font-weight: bold;
            color: #1e293b;
        }
        .sig-title {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

<?php
$registration_number = $registration_number ?? ($data['registration_number'] ?? '');
$certificate_number  = $certificate_number ?? ($data['certificate_number'] ?? '');
$issue_date          = $issue_date ?? ($data['issue_date'] ?? date('Y-m-d'));
$title               = $title ?? (function_exists('genderTitle') ? genderTitle($data['gender'] ?? 'Male') : 'Mr.');
$student_name        = $student_name ?? ($data['name'] ?? '');
$father_name         = $father_name ?? ($data['father_name'] ?? '');
$course              = $course ?? ($data['course'] ?? '');
$institute           = $institute ?? ($data['institute'] ?? '');
$grade               = $grade ?? ($data['grade'] ?? 'Pass');
$training_period     = $training_period ?? (date("d M Y", strtotime($data['start_date'] ?? 'now')) . " – " . date("d M Y", strtotime($data['end_date'] ?? 'now')));

if (!empty($bg_base64)): ?>
    <img src="<?= $bg_base64 ?>" class="cert-bg-img" alt="Certificate Background">
<?php endif; ?>

<div class="cert-container">

    <!-- TOP RIGHT METADATA -->
    <div class="meta-number">
        <div><strong>Reg No:</strong> <?= htmlspecialchars($registration_number) ?></div>
        <div><strong>Cert No:</strong> <?= htmlspecialchars($certificate_number) ?></div>
        <div><strong>Issue Date:</strong> <?= date("d M Y", strtotime($issue_date)) ?></div>
    </div>

    <div style="clear: both;"></div>

    <!-- MAIN CERTIFICATE TEXT CONTENT -->
    <div class="cert-content">
        <div class="cert-subtitle">This is to proudly certify that</div>
        
        <div class="student-name">
            <?= htmlspecialchars($title ? $title . ' ' : '') ?><?= htmlspecialchars($student_name) ?>
        </div>

        <div class="body-text">
            <?= ($data['gender'] ?? 'Male') === 'Female' ? 'daughter' : 'son' ?> of <span class="highlight"><?= htmlspecialchars($father_name) ?></span> has successfully completed the course
            <div class="course-name"><?= htmlspecialchars($course) ?></div>
            conducted by <span class="highlight"><?= htmlspecialchars($institute) ?></span> with Grade <span class="highlight" style="color: #b45309;"><?= htmlspecialchars($grade) ?></span>
            <br>
            for the period of <span class="highlight"><?= htmlspecialchars($training_period) ?></span>.
        </div>
    </div>

    <!-- BOTTOM ROW: PHOTO, QR CODE, SIGNATURE -->
    <table class="bottom-table">
        <tr>
            <td style="width: 30%;">
                <!-- STUDENT PHOTO -->
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
                <!-- QR CODE FOR INSTANT VALIDATION -->
                <?php if (!empty($qr_code_url)): ?>
                    <div class="qr-box">
                        <img src="<?= $qr_code_url ?>" alt="QR Code">
                        <div class="qr-label">Scan to Verify</div>
                    </div>
                <?php endif; ?>
            </td>

            <td style="width: 30%;">
                <div class="signature-box" style="margin-left: auto;">
                    <div class="sig-line">Director</div>
                    <div class="sig-title"><?= htmlspecialchars($mentor ?? 'Course Director') ?></div>
                </div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>