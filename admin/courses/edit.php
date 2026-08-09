<?php
$page_title = "Edit Course";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id=?");
$stmt->execute([$id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    set_flash('error', "Course not found.");
    header("Location: list.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $name = trim($_POST['name'] ?? '');

    if (!$name) {
        $errors[] = "Course name is required.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE courses SET name=? WHERE id=?");
        $stmt->execute([$name, $id]);

        set_flash('success', "Course updated to '{$name}' successfully!");
        header("Location: list.php");
        exit;
    }
}

// Fetch subjects for this course
$subj_stmt = $pdo->prepare("SELECT * FROM subjects WHERE course_id=? ORDER BY id ASC");
$subj_stmt->execute([$id]);
$course_subjects = $subj_stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-edit text-blue-600"></i> Edit Course
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">ID: #<?= $course['id'] ?></p>
        </div>
        <a href="list.php" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Courses
        </a>
    </header>

    <main class="p-8 space-y-8 max-w-4xl">

        <?php if ($errors): ?>
            <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-xs">
                <?php foreach ($errors as $e): ?>
                    <p><i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- COURSE TITLE FORM -->
        <form method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 space-y-6">
            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200 pb-2">
                Course Identity
            </h2>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Course Title *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($course['name']) ?>" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="list.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    Update Course
                </button>
            </div>
        </form>

        <!-- COURSE SUBJECTS MANAGEMENT -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 space-y-6">
            <div class="flex justify-between items-center border-b border-slate-200 pb-3">
                <div>
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-layer-group text-indigo-600"></i> Course Subjects (<?= count($course_subjects) ?>)
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">List of subject modules attached to this course</p>
                </div>
                <a href="../subjects/add.php?course_id=<?= $course['id'] ?>" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <i class="fas fa-plus mr-1.5"></i> Add Subject
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-200 rounded-lg">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">#</th>
                            <th class="px-4 py-3">Subject Title</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($course_subjects)): ?>
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-400">
                                    No subjects added to this course yet.
                                </td>
                            </tr>
                        <?php else: 
                            $idx = 1;
                            foreach ($course_subjects as $s): 
                        ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-center font-semibold text-slate-400"><?= $idx++ ?></td>
                                <td class="px-4 py-3 font-semibold text-slate-800"><?= htmlspecialchars($s['name']) ?></td>
                                <td class="px-4 py-3 text-right space-x-1">
                                    <a href="../subjects/edit.php?id=<?= $s['id'] ?>" class="p-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded inline-block" title="Edit Subject">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <a href="../subjects/delete.php?id=<?= $s['id'] ?>" onclick="return confirm('Delete subject: <?= addslashes($s['name']) ?>?')" class="p-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded inline-block" title="Delete Subject">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>