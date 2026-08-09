<?php
$page_title = "Add Course";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $name = trim($_POST['name'] ?? '');

    if (!$name) {
        $errors[] = "Course name is required.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO courses (name) VALUES (?)");
        $stmt->execute([$name]);

        set_flash('success', "Course '{$name}' created successfully!");
        header("Location: list.php");
        exit;
    }
}

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-plus-circle text-blue-600"></i> Create Course
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Add a new course offering</p>
        </div>
        <a href="list.php" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Courses
        </a>
    </header>

    <main class="p-8">

        <?php if ($errors): ?>
            <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-xs">
                <?php foreach ($errors as $e): ?>
                    <p><i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 max-w-lg space-y-6">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Course Title *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required placeholder="e.g. Full Stack Web Development" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="list.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    Save Course
                </button>
            </div>
        </form>

    </main>
</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>