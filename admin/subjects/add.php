<?php
$page_title = "Add Subject";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$preset_course_id = (int)($_GET['course_id'] ?? 0);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $course_id = (int)($_POST['course_id'] ?? 0);
    $raw_names = trim($_POST['names'] ?? '');

    if ($course_id <= 0) {
        $errors[] = "Please select a valid course.";
    }
    if (!$raw_names) {
        $errors[] = "Please enter at least one subject title.";
    }

    if (empty($errors)) {
        // Split by line breaks to support bulk adding multiple subjects at once
        $lines = preg_split('/\r\n|\r|\n/', $raw_names);
        $added_count = 0;

        $stmt = $pdo->prepare("INSERT INTO subjects (course_id, name) VALUES (?, ?)");

        foreach ($lines as $line) {
            $subject_title = trim($line);
            if (!empty($subject_title)) {
                $stmt->execute([$course_id, $subject_title]);
                $added_count++;
            }
        }

        if ($added_count > 0) {
            set_flash('success', "{$added_count} subject(s) added successfully!");
        } else {
            set_flash('error', "No valid subject titles were entered.");
        }

        header("Location: list.php?course_id=" . $course_id);
        exit;
    }
}

$courses = $pdo->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-plus-circle text-indigo-600"></i> Add Course Subject
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Attach subject topics covered under an academic course</p>
        </div>
        <a href="list.php" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Subjects
        </a>
    </header>

    <main class="p-8">

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-xs space-y-1">
                <p class="font-bold flex items-center gap-1.5"><i class="fas fa-exclamation-triangle"></i> Correct the following issues:</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 max-w-xl space-y-6">

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Select Academic Course *</label>
                <select name="course_id" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                    <option value="">-- Choose Course --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ((int)($_POST['course_id'] ?? $preset_course_id) === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Subject Title / Content *</label>
                <textarea name="names" rows="5" required placeholder="e.g. Data Structures & Algorithms&#10;Web Development Fundamentals&#10;Database Management Systems" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none"><?= htmlspecialchars($_POST['names'] ?? '') ?></textarea>
                <p class="text-[11px] text-slate-400 mt-1">Tip: You can enter multiple subjects (one per line) to add them all at once.</p>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="list.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    Save Subject(s)
                </button>
            </div>
        </form>

    </main>
</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>
