<?php
/**
 * Student Congratulation HTML Email Template
 * Expected input array: $data (student details joined with course_name, institute_name, mentor_name)
 */

$student_name        = strtoupper($data['name'] ?? '');
$gender              = $data['gender'] ?? 'Male';
$title               = ($gender === 'Female') ? 'Ms.' : 'Mr.';
$father_name         = strtoupper($data['father_name'] ?? '');
$registration_number = $data['registration_number'] ?? '';
$certificate_number  = $data['certificate_number'] ?? '';
$course_name         = strtoupper($data['course_name'] ?? ($data['course'] ?? 'N/A'));
$institute_name      = strtoupper($data['institute_name'] ?? ($data['institute'] ?? 'N/A'));
$theory_marks        = (int)($data['theory_marks'] ?? 0);
$practical_marks     = (int)($data['practical_marks'] ?? 0);
$total_marks         = $theory_marks + $practical_marks;
$grade               = strtoupper($data['grade'] ?? 'VERY GOOD');
$issue_date          = !empty($data['issue_date']) ? date('d F, Y', strtotime($data['issue_date'])) : date('d F, Y');
$start_date          = !empty($data['start_date']) ? date('M Y', strtotime($data['start_date'])) : '';
$end_date            = !empty($data['end_date']) ? date('M Y', strtotime($data['end_date'])) : '';
$training_period     = ($start_date && $end_date) ? "{$start_date} to {$end_date}" : "Completed";

$baseUrl             = defined('BASE_URL') ? BASE_URL : 'http://localhost/cert';
$verifyUrl           = $baseUrl . "/public/verify.php";
$downloadUrl         = $baseUrl . "/public/download_marksheet.php?registration_number=" . urlencode($registration_number);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congratulations on Your Course Registration & Certificate</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }
        table {
            border-collapse: collapse;
        }
        .wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 30px 0;
        }
        .container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .header-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #2563eb 100%);
            padding: 35px 30px;
            text-align: center;
            color: #ffffff;
        }
        .header-title {
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 13px;
            color: #93c5fd;
            margin-top: 6px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .body-content {
            padding: 35px 30px;
        }
        .congrats-banner {
            background-color: #eff6ff;
            border-left: 5px solid #2563eb;
            padding: 16px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }
        .congrats-heading {
            font-size: 18px;
            font-weight: 700;
            color: #1e40af;
            margin: 0 0 5px 0;
        }
        .congrats-text {
            font-size: 14px;
            color: #3b82f6;
            margin: 0;
            line-height: 1.5;
        }
        .section-label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 6px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .info-table td {
            padding: 9px 12px;
            font-size: 13.5px;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-table .lbl {
            width: 38%;
            font-weight: 600;
            color: #475569;
            background-color: #f8fafc;
        }
        .info-table .val {
            font-weight: 700;
            color: #0f172a;
        }
        .badge-reg {
            display: inline-block;
            background-color: #e0e7ff;
            color: #3730a3;
            padding: 4px 10px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
            font-weight: bold;
        }
        .badge-cert {
            display: inline-block;
            background-color: #dcfce7;
            color: #166534;
            padding: 4px 10px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
            font-weight: bold;
        }
        .badge-grade {
            display: inline-block;
            background-color: #fef3c7;
            color: #92400e;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0 15px 0;
        }
        .btn-primary {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            padding: 12px 26px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            margin: 5px;
        }
        .btn-secondary {
            display: inline-block;
            background-color: #0f172a;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            padding: 12px 26px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
            margin: 5px;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="container">
        
        <!-- HEADER -->
        <div class="header-bg">
            <h1 class="header-title">BELIEFPRO LEARNING FORUM</h1>
            <div class="header-subtitle">Official Student Academic Record</div>
        </div>

        <!-- BODY CONTENT -->
        <div class="body-content">
            
            <!-- CONGRATS BANNER -->
            <div class="congrats-banner">
                <h2 class="congrats-heading">🎉 Congratulations, <?= htmlspecialchars($student_name) ?>!</h2>
                <p class="congrats-text">
                    Your registration and certificate details have been successfully saved and verified in our official database.
                </p>
            </div>

            <!-- ACADEMIC & STUDENT CREDENTIALS -->
            <div class="section-label">Student & Course Details</div>
            <table class="info-table">
                <tr>
                    <td class="lbl">Student Full Name:</td>
                    <td class="val"><?= htmlspecialchars($title . ' ' . $student_name) ?></td>
                </tr>
                <tr>
                    <td class="lbl">Father / Guardian:</td>
                    <td class="val"><?= htmlspecialchars($father_name) ?></td>
                </tr>
                <tr>
                    <td class="lbl">Registration No:</td>
                    <td class="val"><span class="badge-reg"><?= htmlspecialchars($registration_number) ?></span></td>
                </tr>
                <tr>
                    <td class="lbl">Certificate No:</td>
                    <td class="val"><span class="badge-cert"><?= htmlspecialchars($certificate_number) ?></span></td>
                </tr>
                <tr>
                    <td class="lbl">Course Name:</td>
                    <td class="val"><?= htmlspecialchars($course_name) ?></td>
                </tr>
                <tr>
                    <td class="lbl">Study Centre (Institute):</td>
                    <td class="val"><?= htmlspecialchars($institute_name) ?></td>
                </tr>
                <tr>
                    <td class="lbl">Training Duration:</td>
                    <td class="val"><?= htmlspecialchars($training_period) ?></td>
                </tr>
                <tr>
                    <td class="lbl">Result Declared On:</td>
                    <td class="val"><?= htmlspecialchars($issue_date) ?></td>
                </tr>
            </table>

            <!-- MARKS & EVALUATION SUMMARY -->
            <div class="section-label">Marks & Grade Details</div>
            <table class="info-table">
                <tr>
                    <td class="lbl">Theory Marks (Max 100):</td>
                    <td class="val"><?= $theory_marks ?></td>
                </tr>
                <tr>
                    <td class="lbl">Practical Marks (Max 100):</td>
                    <td class="val"><?= $practical_marks ?></td>
                </tr>
                <tr>
                    <td class="lbl">Total Marks Obtained:</td>
                    <td class="val"><?= $total_marks ?> / 200</td>
                </tr>
                <tr>
                    <td class="lbl">Overall Grade:</td>
                    <td class="val"><span class="badge-grade"><?= htmlspecialchars($grade) ?></span></td>
                </tr>
            </table>

            <!-- BUTTON ACTIONS -->
            <div class="btn-container">
                <a href="<?= $verifyUrl ?>" target="_blank" class="btn-primary">🔍 Verify Certificate</a>
                <a href="<?= $downloadUrl ?>" target="_blank" class="btn-secondary">📄 Download Marksheet</a>
            </div>

            <p style="font-size: 12.5px; color: #64748b; margin-top: 25px; line-height: 1.6; text-align: center;">
                Please retain your Registration Number (<strong><?= htmlspecialchars($registration_number) ?></strong>) and Certificate Number (<strong><?= htmlspecialchars($certificate_number) ?></strong>) for all future academic and career verifications.
            </p>

        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p style="margin: 0 0 5px 0;">This is an automated system notification from <strong>BELIEFPRO LEARNING FORUM</strong>.</p>
            <p style="margin: 0 0 5px 0;">Need help or have questions? Contact your authorised study centre administration.</p>
            <p style="margin: 8px 0 0 0; font-size: 11px; opacity: 0.8;">&copy; <?= date("Y") ?> BELIEFPRO LEARNING FORUM. Certificate Verification & Management System. All rights reserved.</p>
        </div>

    </div>
</div>

</body>
</html>
