<?php
$page_title = "Register Student";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $name         = trim($_POST['name'] ?? '');
    $father       = trim($_POST['father_name'] ?? '');
    $gender       = $_POST['gender'] ?? 'Male';
    $course       = $_POST['course'] ?? null;
    $mentor       = $_POST['mentor'] ?? null;
    $institute    = $_POST['institute'] ?? null;
    $dob          = $_POST['dob'] ?? null;
    $start        = $_POST['start_date'] ?? null;
    $end          = $_POST['end_date'] ?? null;
    $issue        = $_POST['issue_date'] ?? null;
    $grade        = trim($_POST['grade'] ?? 'A+');
    $theory_marks    = max(0, (int)($_POST['theory_marks'] ?? 0));
    $practical_marks = max(0, (int)($_POST['practical_marks'] ?? 0));

    if (!$name) $errors[] = "Student full name is required.";
    if (!$father) $errors[] = "Father/Mother name is required.";
    if (!$dob) $errors[] = "Date of birth is required.";
    if (!$start) $errors[] = "Course start date is required.";
    if (!$end) $errors[] = "Course end date is required.";
    if (!$issue) $errors[] = "Certificate issue date is required.";

    $photo = null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $img_val = validateStudentImage($_FILES['photo']);
        if ($img_val !== true) {
            $errors[] = $img_val;
        } else {
            $tempRegistration = generateRegistrationNumber($pdo);
            $filename = $tempRegistration . ".jpg";
            $targetDir = __DIR__ . "/../../uploads/students/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $destination = $targetDir . $filename;
            $result = compressStudentImage($_FILES['photo']['tmp_name'], $destination);

            if (!$result) {
                $errors[] = "Image compression and saving failed.";
            } else {
                $photo = "students/" . $filename;
            }
        }
    }

    if (empty($errors)) {
        $registration = $tempRegistration ?? generateRegistrationNumber($pdo);
        $certificate  = generateCertificateNumber($pdo);

        $stmt = $pdo->prepare("
            INSERT INTO students
            (name, father_name, gender, registration_number, certificate_number,
             course_id, mentor_id, institute_id,
             dob, start_date, end_date, issue_date, grade, theory_marks, practical_marks, student_photo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $name, $father, $gender, $registration, $certificate,
            $course, $mentor, $institute,
            $dob, $start, $end, $issue, $grade, $theory_marks, $practical_marks, $photo
        ]);

        set_flash('success', "Student {$name} registered successfully! Reg: {$registration}");
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
                <i class="fas fa-user-plus text-blue-600"></i> Register New Student
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Issue a new certificate record</p>
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
                        <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Father / Guardian Name *</label>
                        <input type="text" name="father_name" value="<?= htmlspecialchars($_POST['father_name'] ?? '') ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Gender *</label>
                        <select name="gender" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <option value="Male" <?= ($_POST['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($_POST['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Student Photo (Max 5MB, JPG/PNG)</label>
                        <input type="file" name="photo" accept="image/jpeg,image/png" class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Institute *</label>
                        <select name="institute" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <?php foreach ($institutes as $i): ?>
                                <option value="<?= $i['id'] ?>" <?= ($_POST['institute'] ?? '') == $i['id'] ? 'selected' : '' ?>><?= htmlspecialchars($i['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Course *</label>
                        <select name="course" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($_POST['course'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Assigned Mentor *</label>
                        <select name="mentor" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <?php foreach ($mentors as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= ($_POST['mentor'] ?? '') == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
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
                        <input type="date" name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Course Start Date *</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Course End Date *</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Certificate Issue Date *</label>
                        <input type="date" name="issue_date" value="<?= htmlspecialchars($_POST['issue_date'] ?? date('Y-m-d')) ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
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
                        <label class="block text-xs font-medium text-slate-700 mb-1">Final Grade (e.g. A+, A, Excellent)</label>
                        <input type="text" name="grade" value="<?= htmlspecialchars($_POST['grade'] ?? 'A+') ?>" placeholder="A+" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Theory Marks (e.g. 75)</label>
                        <input type="number" name="theory_marks" min="0" max="100" value="<?= htmlspecialchars($_POST['theory_marks'] ?? '0') ?>" placeholder="Theory Marks" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Practical Marks (e.g. 85)</label>
                        <input type="number" name="practical_marks" min="0" max="100" value="<?= htmlspecialchars($_POST['practical_marks'] ?? '0') ?>" placeholder="Practical Marks" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="list.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                    <i class="fas fa-check"></i> Register Student & Issue Certificate
                </button>
            </div>

        </form>

    </main>
</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>