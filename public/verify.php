<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

$data = null;
$searched = false;

$cert = trim($_GET['cert'] ?? '');
$reg  = trim($_POST['registration_number'] ?? '');
$dob  = trim($_POST['dob'] ?? '');

if ($cert !== '') {
    $searched = true;
    $stmt = $pdo->prepare("
        SELECT students.*, 
               courses.name AS course, 
               mentors.name AS mentor,
               institutes.name AS institute
        FROM students
        LEFT JOIN courses ON courses.id = students.course_id
        LEFT JOIN mentors ON mentors.id = students.mentor_id
        LEFT JOIN institutes ON institutes.id = students.institute_id
        WHERE certificate_number = ?
    ");
    $stmt->execute([$cert]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($reg !== '' && $dob !== '') {
    $searched = true;
    $stmt = $pdo->prepare("
        SELECT students.*, 
               courses.name AS course, 
               mentors.name AS mentor,
               institutes.name AS institute
        FROM students
        LEFT JOIN courses ON courses.id = students.course_id
        LEFT JOIN mentors ON mentors.id = students.mentor_id
        LEFT JOIN institutes ON institutes.id = students.institute_id
        WHERE registration_number = ? AND dob = ?
    ");
    $stmt->execute([$reg, $dob]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
}
$subjects = [];
if ($data && !empty($data['course_id'])) {
    $sub_stmt = $pdo->prepare("SELECT name FROM subjects WHERE course_id = ? ORDER BY id ASC");
    $sub_stmt->execute([$data['course_id']]);
    $subjects = $sub_stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification Result</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/logo.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #071527 0%, #0f2744 50%, #030a14 100%);
        }
        .swal2-popup {
            border-radius: 1rem !important;
            font-family: 'Inter', sans-serif !important;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col justify-between items-center px-4 py-8 text-slate-800">

    <!-- HEADER BRANDING -->
    <header class="w-full max-w-2xl text-center pt-2">
        <div class="inline-flex items-center justify-center p-3.5 sm:p-4 rounded-2xl bg-white shadow-xl mb-3">
            <img src="<?= BASE_URL ?>/assets/logo.png" alt="Biswas Company Logo" class="h-20 sm:h-24 w-auto object-contain">
        </div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Official Certificate Verification</h1>
    </header>

    <main class="w-full max-w-2xl my-6">

        <?php if ($data): ?>
            <!-- VERIFICATION SUCCESS CONTAINER -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200">

                <!-- STATUS BANNER -->
                <div class="bg-emerald-600 text-white p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-2xl">
                            <i class="fas fa-check-circle text-white"></i>
                        </div>
                        <div>
                            <span class="inline-block uppercase tracking-wider text-[10px] font-bold bg-emerald-700/80 px-2 py-0.5 rounded text-emerald-100">Authentic & Verified</span>
                            <h2 class="text-lg font-bold">Certificate Verified Valid</h2>
                        </div>
                    </div>

                    <a href="download.php?cert=<?= urlencode($data['certificate_number']) ?>" target="_blank" class="px-4 py-2.5 bg-white hover:bg-emerald-50 text-emerald-800 text-xs font-bold rounded-xl shadow transition flex items-center gap-2">
                        <i class="fas fa-file-pdf text-rose-600 text-sm"></i> Download Official PDF
                    </a>
                </div>

                <!-- STUDENT DETAILS BODY -->
                <div class="p-6 sm:p-8 space-y-6">

                    <!-- STUDENT IDENTITY & QR CODE ROW -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-5 p-5 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-sm">
                        
                        <!-- Student Photo -->
                        <div class="flex items-center space-x-4 text-center sm:text-left">
                            <?php if (!empty($data['student_photo']) && file_exists(__DIR__ . '/../uploads/' . $data['student_photo'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($data['student_photo']) ?>" alt="Student Photo" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-white shadow-md">
                            <?php else: ?>
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-3xl shadow-md">
                                    <?= strtoupper(substr($data['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <div>
                                <h3 class="text-xl font-extrabold text-slate-800 tracking-tight"><?= htmlspecialchars($data['name']) ?></h3>
                                <p class="text-xs text-slate-500 mt-0.5">Father/Guardian: <span class="font-medium text-slate-700"><?= htmlspecialchars($data['father_name'] ?? 'N/A') ?></span></p>
                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 font-mono">
                                        Reg: <?= htmlspecialchars($data['registration_number']) ?>
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 font-mono">
                                        Cert: <?= htmlspecialchars($data['certificate_number']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Verification QR Code -->
                        <div class="flex flex-col items-center justify-center p-3 bg-white rounded-xl border border-slate-200 shadow-xs text-center shrink-0">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode(BASE_URL . "/public/verify.php?cert=" . $data['certificate_number']) ?>" alt="Verification QR Code" class="w-20 h-20 rounded-lg object-contain">
                            <span class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-wider">Scan to Verify</span>
                        </div>

                    </div>

                    <!-- METADATA GRID -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-3.5 rounded-lg border border-slate-200 bg-white">
                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Course Name</span>
                            <span class="font-bold text-slate-800 text-sm mt-0.5 block"><?= htmlspecialchars($data['course'] ?? 'N/A') ?></span>
                        </div>

                        <div class="p-3.5 rounded-lg border border-slate-200 bg-white">
                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Final Grade</span>
                            <?php
                            $raw_grade = strtoupper(trim($data['grade'] ?? ''));
                            $grade_map = [
                                'EXCELLENT' => 'A+',
                                'VERY GOOD' => 'A',
                                'GOOD' => 'B',
                                'FAIR' => 'C',
                                'FAIL' => 'F'
                            ];
                            $display_grade = $grade_map[$raw_grade] ?? ($raw_grade ?: 'A+');
                            ?>
                            <span class="font-bold text-emerald-600 text-sm mt-0.5 block"><i class="fas fa-star mr-1"></i> Grade <?= htmlspecialchars($display_grade) ?></span>
                        </div>

                        <div class="p-3.5 rounded-lg border border-slate-200 bg-white">
                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Marks Breakdown</span>
                            <div class="mt-1 flex items-center justify-between text-xs">
                                <span class="text-slate-600">Theory: <strong class="text-blue-700"><?= (int)($data['theory_marks'] ?? 0) ?>/100</strong></span>
                                <span class="text-slate-600">Practical: <strong class="text-purple-700"><?= (int)($data['practical_marks'] ?? 0) ?>/100</strong></span>
                            </div>
                            <div class="mt-1 text-[11px] font-bold text-emerald-700 border-t border-slate-100 pt-1">
                                Total: <?= ((int)($data['theory_marks'] ?? 0) + (int)($data['practical_marks'] ?? 0)) ?> / 200
                            </div>
                        </div>

                        <div class="p-3.5 rounded-lg border border-slate-200 bg-white">
                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Affiliated Institute</span>
                            <span class="font-semibold text-slate-700 mt-0.5 block"><?= htmlspecialchars($data['institute'] ?? 'N/A') ?></span>
                        </div>

                        <div class="p-3.5 rounded-lg border border-slate-200 bg-white">
                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Date of Birth (DOB)</span>
                            <span class="font-semibold text-slate-700 mt-0.5 block"><?= !empty($data['dob']) ? date("d M Y", strtotime($data['dob'])) : 'N/A' ?></span>
                        </div>

                        <div class="p-3.5 rounded-lg border border-slate-200 bg-white">
                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Issue Date</span>
                            <span class="font-semibold text-slate-700 mt-0.5 block"><?= htmlspecialchars($data['issue_date'] ?? 'N/A') ?></span>
                        </div>
                    </div>

                    <!-- DUAL DOWNLOAD ACTIONS SECTION -->
                    <div class="pt-4 border-t border-slate-200 space-y-3">
                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider text-center sm:text-left">
                            <i class="fas fa-file-download text-blue-600 mr-1"></i> Available Documents for Download
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Option 1: Download Certificate -->
                            <a href="download.php?cert=<?= urlencode($data['certificate_number']) ?>" target="_blank" class="py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                                <i class="fas fa-certificate text-amber-300 text-sm"></i> Option 1: Download Certificate
                            </a>

                            <!-- Option 2: Download Marksheet -->
                            <a href="download_marksheet.php?cert=<?= urlencode($data['certificate_number']) ?>" target="_blank" class="py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                                <i class="fas fa-file-invoice text-indigo-200 text-sm"></i> Option 2: Download Marksheet
                            </a>
                        </div>

                        <div class="pt-2 text-center">
                            <a href="index.php" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-700 transition">
                                <i class="fas fa-arrow-left mr-1.5"></i> Verify Another Student Certificate
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        <?php elseif ($searched): ?>
            <!-- INVALID / NOT FOUND CONTAINER -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 border border-slate-200 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-3xl mx-auto">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800">Certificate Record Not Found</h2>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">We could not find any matching certificate record for the provided registration number or certificate code.</p>
                <div class="pt-4">
                    <a href="index.php" class="inline-flex items-center px-6 py-3 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow transition">
                        <i class="fas fa-arrow-left mr-2"></i> Try Search Again
                    </a>
                </div>
            </div>

        <?php else: ?>
            <?php header("Location: index.php"); exit; ?>
        <?php endif; ?>

    </main>

    <footer class="text-center text-xs text-slate-500 pb-4">
        &copy; <?= date("Y") ?> BELIEFPRO LEARNING FORUM. Certificate Verification & Management System. All rights reserved.
    </footer>

    <?php if ($data): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'success',
            title: 'Certificate Verified!',
            text: 'Valid certificate found for <?= addslashes($data['name']) ?>',
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true
        });
    });
    </script>
    <?php elseif ($searched): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'error',
            title: 'Certificate Not Found',
            text: 'No certificate record found matching your search details.',
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Try Search Again'
        });
    });
    </script>
    <?php endif; ?>

</body>

</html>