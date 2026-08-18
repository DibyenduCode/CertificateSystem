<?php
/**
 * Student Congratulation HTML Email Template (Lightweight & Simple)
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
<title>Registration & Certificate Confirmation</title>
<style>
  body {
    margin: 0;
    padding: 0;
    background-color: #f1f5f9;
    font-family: Arial, sans-serif;
    color: #334155;
  }
  .card {
    max-width: 580px;
    margin: 20px auto;
    background-color: #ffffff;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #cbd5e1;
  }
  .header {
    background-color: #0f172a;
    padding: 22px 20px;
    text-align: center;
    color: #ffffff;
  }
  .header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
  }
  .header p {
    margin: 4px 0 0 0;
    font-size: 12px;
    color: #93c5fd;
  }
  .body {
    padding: 24px 20px;
  }
  .banner {
    background-color: #eff6ff;
    border-left: 4px solid #2563eb;
    padding: 12px 16px;
    border-radius: 4px;
    margin-bottom: 20px;
  }
  .banner h3 {
    margin: 0 0 4px 0;
    color: #1e40af;
    font-size: 16px;
  }
  .banner p {
    margin: 0;
    font-size: 13px;
    color: #1e3a8a;
  }
  .info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
  }
  .info-table td {
    padding: 8px 10px;
    font-size: 13px;
    border-bottom: 1px solid #f1f5f9;
  }
  .info-table .lbl {
    font-weight: 600;
    color: #475569;
    width: 38%;
    background-color: #f8fafc;
  }
  .info-table .val {
    font-weight: 700;
    color: #0f172a;
  }
  .badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    font-family: monospace;
  }
  .badge-reg { background-color: #e0e7ff; color: #3730a3; }
  .badge-cert { background-color: #dcfce7; color: #166534; }
  .badge-grade { background-color: #fef3c7; color: #92400e; font-family: inherit; }
  .actions {
    text-align: center;
    margin: 24px 0 16px 0;
  }
  .btn {
    display: inline-block;
    padding: 10px 22px;
    margin: 4px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    font-size: 13px;
  }
  .btn-primary { background-color: #2563eb; color: #ffffff !important; }
  .btn-secondary { background-color: #0f172a; color: #ffffff !important; }
  .footer {
    background-color: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 16px;
    text-align: center;
    font-size: 11px;
    color: #64748b;
  }
</style>
</head>
<body>

<div class="card">
  <div class="header">
    <h2>BELIEFPRO LEARNING FORUM</h2>
    <p>Official Student Academic Record &amp; Confirmation</p>
  </div>

  <div class="body">
    <div class="banner">
      <h3>Congratulations, <?= htmlspecialchars($student_name) ?>!</h3>
      <p>Your registration and certificate details have been successfully saved and confirmed.</p>
    </div>

    <table class="info-table">
      <tr>
        <td class="lbl">Student Name:</td>
        <td class="val"><?= htmlspecialchars($title . ' ' . $student_name) ?></td>
      </tr>
      <tr>
        <td class="lbl">Father / Guardian:</td>
        <td class="val"><?= htmlspecialchars($father_name) ?></td>
      </tr>
      <tr>
        <td class="lbl">Registration No:</td>
        <td class="val"><span class="badge badge-reg"><?= htmlspecialchars($registration_number) ?></span></td>
      </tr>
      <tr>
        <td class="lbl">Certificate No:</td>
        <td class="val"><span class="badge badge-cert"><?= htmlspecialchars($certificate_number) ?></span></td>
      </tr>
      <tr>
        <td class="lbl">Course Name:</td>
        <td class="val"><?= htmlspecialchars($course_name) ?></td>
      </tr>
      <tr>
        <td class="lbl">Institute Name:</td>
        <td class="val"><?= htmlspecialchars($institute_name) ?></td>
      </tr>
      <tr>
        <td class="lbl">Training Period:</td>
        <td class="val"><?= htmlspecialchars($training_period) ?></td>
      </tr>
      <tr>
        <td class="lbl">Result Date:</td>
        <td class="val"><?= htmlspecialchars($issue_date) ?></td>
      </tr>
      <tr>
        <td class="lbl">Total Marks:</td>
        <td class="val"><?= $total_marks ?> / 200 (Theory: <?= $theory_marks ?>, Practical: <?= $practical_marks ?>)</td>
      </tr>
      <tr>
        <td class="lbl">Overall Grade:</td>
        <td class="val"><span class="badge badge-grade"><?= htmlspecialchars($grade) ?></span></td>
      </tr>
    </table>

    <div class="actions">
      <a href="<?= $verifyUrl ?>" target="_blank" class="btn btn-primary">Verify Certificate</a>
      <a href="<?= $downloadUrl ?>" target="_blank" class="btn btn-secondary">Download Marksheet</a>
    </div>

    <p style="font-size:12px; color:#64748b; text-align:center; margin-top:20px; line-height:1.5;">
      Please retain Registration No. (<strong><?= htmlspecialchars($registration_number) ?></strong>) and Certificate No. (<strong><?= htmlspecialchars($certificate_number) ?></strong>) for future verification.
    </p>
  </div>

  <div class="footer">
    &copy; <?= date("Y") ?> BELIEFPRO LEARNING FORUM. Automated Email Notification.
  </div>
</div>

</body>
</html>
