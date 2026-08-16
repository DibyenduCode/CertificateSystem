<?php
$page_title = "Edit Student";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

ensure_smtp_and_email_tables($pdo);

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    set_flash('error', "Student record not found.");
    header("Location: list.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $action = $_POST['action'] ?? 'update_student';

    if ($action === 'resend_email') {
        $mailResult = sendStudentCongratulationEmail($id, $pdo, true);
        if ($mailResult['success']) {
            set_flash('success', "Congratulation email successfully sent to {$student['email']}!");
        } else {
            set_flash('error', "Failed to send email: " . $mailResult['message']);
        }
        header("Location: edit.php?id=" . $id);
        exit;
    }

    $name         = trim($_POST['name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $father       = trim($_POST['father_name'] ?? '');
    $gender       = $_POST['gender'] ?? 'Male';
    $course       = $_POST['course'] ?? null;
    $mentor       = $_POST['mentor'] ?? null;
    $institute    = $_POST['institute'] ?? null;
    $dob          = $_POST['dob'] ?? null;
    $start        = $_POST['start_date'] ?? null;
    $end          = $_POST['end_date'] ?? null;
    $issue        = $_POST['issue_date'] ?? null;
    $theory_marks    = max(0, (int)($_POST['theory_marks'] ?? 0));
    $practical_marks = max(0, (int)($_POST['practical_marks'] ?? 0));
    $grade_input     = trim($_POST['grade'] ?? '');

    if (empty($grade_input) || in_array(strtoupper($grade_input), ['A+', 'PASS', 'AUTO'])) {
        $grade = calculateGrade($theory_marks, $practical_marks);
    } else {
        $grade = strtoupper($grade_input);
    }

    if (!$name) $errors[] = "Student name is required.";
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid student email address.";
    if (!$father) $errors[] = "Father name is required.";

    $photo = $student['student_photo'];
    $gov_id_doc = $student['gov_id_doc'] ?? null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $img_val = validateStudentImage($_FILES['photo']);
        if ($img_val !== true) {
            $errors[] = $img_val;
        } else {
            $filename = $student['registration_number'] . ".jpg";
            $targetDir = __DIR__ . "/../../uploads/students/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $destination = $targetDir . $filename;

            // Remove previous photo if existing
            if ($student['student_photo'] && file_exists(__DIR__ . "/../../uploads/" . $student['student_photo'])) {
                @unlink(__DIR__ . "/../../uploads/" . $student['student_photo']);
            }

            $result = compressStudentImage($_FILES['photo']['tmp_name'], $destination);

            if (!$result) {
                $errors[] = "Image compression and saving failed.";
            } else {
                $photo = "students/" . $filename;
            }
        }
    }

    if (isset($_FILES['gov_id_doc']) && $_FILES['gov_id_doc']['error'] === UPLOAD_ERR_OK) {
        $pdf_val = validateGovIdDocument($_FILES['gov_id_doc']);
        if ($pdf_val !== true) {
            $errors[] = $pdf_val;
        } else {
            $pdfFilename = "govid_" . $student['registration_number'] . ".pdf";
            $targetDirPdf = __DIR__ . "/../../uploads/govid/";
            if (!is_dir($targetDirPdf)) {
                mkdir($targetDirPdf, 0755, true);
            }
            $pdfDestination = $targetDirPdf . $pdfFilename;

            // Remove previous file if existing
            if (!empty($student['gov_id_doc']) && file_exists(__DIR__ . "/../../uploads/" . $student['gov_id_doc'])) {
                @unlink(__DIR__ . "/../../uploads/" . $student['gov_id_doc']);
            }

            if (!move_uploaded_file($_FILES['gov_id_doc']['tmp_name'], $pdfDestination)) {
                $errors[] = "Failed to upload Govt ID PDF document.";
            } else {
                $gov_id_doc = "govid/" . $pdfFilename;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE students
            SET name=?, email=?, father_name=?, gender=?, course_id=?, mentor_id=?,
                institute_id=?, dob=?, start_date=?,
                end_date=?, issue_date=?, grade=?, theory_marks=?, practical_marks=?, student_photo=?, gov_id_doc=?
            WHERE id=?
        ");

        $stmt->execute([
            $name, $email, $father, $gender, $course, $mentor,
            $institute, $dob, $start,
            $end, $issue, $grade, $theory_marks, $practical_marks, $photo, $gov_id_doc, $id
        ]);

        set_flash('success', "Student {$name} updated successfully.");
        header("Location: list.php");
        exit;
    }
}

$courses       = $pdo->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$mentors       = $pdo->query("SELECT * FROM mentors ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$institutes    = $pdo->query("SELECT * FROM institutes ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <!-- PAGE HEADER -->
    <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-user-edit text-blue-600"></i> Edit Student Record
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Reg No: <span class="font-mono text-blue-600 font-semibold"><?= htmlspecialchars($student['registration_number']) ?></span></p>
        </div>
        <a href="list.php" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
    </header>

    <main class="p-8">

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-xs space-y-1">
                <p class="font-bold flex items-center gap-1.5"><i class="fas fa-exclamation-triangle"></i> Please correct the following errors:</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 max-w-4xl space-y-8">

            <!-- SECTION 1: PERSONAL DETAILS -->
            <div>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200 pb-2 mb-6 flex items-center gap-2">
                    <i class="fas fa-id-card text-blue-600"></i> Personal & Academic Identifiers
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Student Full Name *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Student Email Address</label>
                        <div class="flex gap-2">
                            <input type="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" placeholder="e.g. student@example.com" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <?php if (!empty($student['email'])): ?>
                                <button type="submit" name="action" value="resend_email" onclick="return confirm('Resend congratulation email to <?= htmlspecialchars($student['email']) ?>?')" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg shadow-sm transition shrink-0 flex items-center gap-1.5" title="Resend Congratulation Email">
                                    <i class="fas fa-paper-plane text-blue-400"></i> Resend Email
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Father / Guardian Name *</label>
                        <input type="text" name="father_name" value="<?= htmlspecialchars($student['father_name']) ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Gender *</label>
                        <select name="gender" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <option value="Male" <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Student Photo (Upload to Replace)</label>
                        <input type="file" name="photo" accept="image/jpeg,image/png" class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                        <?php if (!empty($student['student_photo']) && file_exists(__DIR__ . '/../../uploads/' . $student['student_photo'])): ?>
                            <div class="mt-2 flex items-center space-x-3 bg-slate-50 p-2 rounded border border-slate-200">
                                <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($student['student_photo']) ?>" class="w-12 h-12 rounded object-cover border border-slate-300">
                                <span class="text-[11px] text-slate-500">Current active photo</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Government ID Document (PDF, Max 5MB)</label>
                        <input type="file" name="gov_id_doc" accept="application/pdf" class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                        <p class="text-[11px] text-slate-500 mt-1 mb-1.5"><i class="fas fa-shield-alt text-blue-500 mr-0.5"></i> Internal documentation only. Not shown on verification.</p>
                        <?php if (!empty($student['gov_id_doc']) && file_exists(__DIR__ . '/../../uploads/' . $student['gov_id_doc'])): ?>
                            <div class="mt-2 flex items-center gap-2 bg-slate-50 p-2 rounded border border-slate-200">
                                <span class="text-xs font-semibold text-emerald-700 flex items-center gap-1.5"><i class="fas fa-file-pdf text-rose-600 text-sm"></i> Govt ID PDF Uploaded</span>
                                <a href="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($student['gov_id_doc']) ?>" target="_blank" class="ml-auto inline-flex items-center gap-1 px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded transition shadow-sm">
                                    <i class="fas fa-external-link-alt text-[10px]"></i> View Document
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Institute *</label>
                        <select name="institute" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <?php foreach ($institutes as $i): ?>
                                <option value="<?= $i['id'] ?>" <?= $student['institute_id'] == $i['id'] ? 'selected' : '' ?>><?= htmlspecialchars($i['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Course *</label>
                        <select name="course" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $student['course_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Assigned Mentor *</label>
                        <select name="mentor" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <?php foreach ($mentors as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= $student['mentor_id'] == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>
            </div>

            <!-- SECTION 2: DATES & TIMELINE -->
            <div>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200 pb-2 mb-6 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-blue-600"></i> Course Timeline
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Date of Birth *</label>
                        <input type="date" name="dob" value="<?= htmlspecialchars($student['dob']) ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Course Start Date *</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($student['start_date']) ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Course End Date *</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($student['end_date']) ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Certificate Issue Date *</label>
                        <input type="date" name="issue_date" value="<?= htmlspecialchars($student['issue_date']) ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                </div>
            </div>

            <!-- SECTION 3: PERFORMANCE -->
            <div>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200 pb-2 mb-6 flex items-center gap-2">
                    <i class="fas fa-chart-line text-blue-600"></i> Performance
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Final Grade (Auto: Excellent &ge;80%, Very Good &ge;70%, Good &ge;60%, Fair &ge;50%)</label>
                        <?php $curr_grade = strtoupper($student['grade'] ?? ''); ?>
                        <select name="grade" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <option value="AUTO">-- Auto Calculate from Marks --</option>
                            <option value="EXCELLENT" <?= $curr_grade === 'EXCELLENT' ? 'selected' : '' ?>>EXCELLENT (&ge;80%)</option>
                            <option value="VERY GOOD" <?= $curr_grade === 'VERY GOOD' ? 'selected' : '' ?>>VERY GOOD (&ge;70%)</option>
                            <option value="GOOD" <?= $curr_grade === 'GOOD' ? 'selected' : '' ?>>GOOD (&ge;60%)</option>
                            <option value="FAIR" <?= $curr_grade === 'FAIR' ? 'selected' : '' ?>>FAIR (&ge;50%)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Theory Marks</label>
                        <input type="number" name="theory_marks" min="0" max="100" value="<?= htmlspecialchars($student['theory_marks'] ?? 0) ?>" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Practical Marks</label>
                        <input type="number" name="practical_marks" min="0" max="100" value="<?= htmlspecialchars($student['practical_marks'] ?? 0) ?>" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="list.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>

        </form>

    </main>
</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>