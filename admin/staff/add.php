<?php
$page_title = "Register Staff Member";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

if (!is_admin() && empty($_SESSION['impersonated_by_admin'])) {
    set_flash('error', "Access Denied: Only Administrator can create staff accounts.");
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $name        = trim($_POST['name'] ?? '');
    $username    = trim($_POST['username'] ?? '');
    $password    = trim($_POST['password'] ?? '');
    $status      = $_POST['status'] ?? 'active';
    $permissions = $_POST['permissions'] ?? [];

    if (!$name) $errors[] = "Staff full name is required.";
    if (!$username) $errors[] = "Username is required.";
    if (!$password) $errors[] = "Password is required.";

    // Check if username already exists in staff or admins table
    if ($username) {
        $stmt = $pdo->prepare("SELECT id FROM staff WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = "Username '{$username}' is already taken by another staff member.";
        }

        $stmt2 = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt2->execute([$username]);
        if ($stmt2->fetch()) {
            $errors[] = "Username '{$username}' is reserved for admin account.";
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $json_perms = json_encode(array_values($permissions));

        $stmt = $pdo->prepare("
            INSERT INTO staff (name, username, password, permissions, status)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([$name, $username, $hashed_password, $json_perms, $status]);

        set_flash('success', "Staff account '{$name}' created successfully!");
        header("Location: list.php");
        exit;
    }
}

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

$available_permissions = [
    'students'  => ['label' => 'Students Management', 'desc' => 'Allow staff to add, view, edit, and export student certificates.'],
    'courses'   => ['label' => 'Courses Management', 'desc' => 'Allow staff to create and manage academic course subjects.'],
    'mentors'   => ['label' => 'Mentors Management', 'desc' => 'Allow staff to create and manage instructors and mentors.'],
    'institutes'=> ['label' => 'Institutes Management', 'desc' => 'Allow staff to create and manage affiliated institute centers.'],
    'api_keys'  => ['label' => 'Developer API Keys', 'desc' => 'Allow staff to view and generate integration API keys.']
];
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <!-- PAGE HEADER -->
    <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-user-plus text-blue-600"></i> Create Staff Account
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Register a new staff member and select feature permissions</p>
        </div>
        <a href="list.php" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Staff List
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

        <form method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 max-w-3xl space-y-8">

            <!-- SECTION 1: ACCOUNT INFORMATION -->
            <div>
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200 pb-2 mb-6 flex items-center gap-2">
                    <i class="fas fa-user text-blue-600"></i> Account Identity Details
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Staff Full Name *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="e.g. Sarah Jenkins" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Login Username *</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="e.g. sarah_j" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Account Password *</label>
                        <input type="password" name="password" placeholder="Enter secure password" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Account Status *</label>
                        <select name="status" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <option value="active" <?= ($_POST['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active (Can Login)</option>
                            <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive (Disabled)</option>
                        </select>
                    </div>

                </div>
            </div>

            <!-- SECTION 2: CHECKBOX FEATURE PERMISSIONS -->
            <div>
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                    <i class="fas fa-user-shield text-blue-600"></i> Allowed Feature Access (Checkbox Selection)
                </h2>
                <p class="text-xs text-slate-500 mb-6">Select which system modules this staff member is authorized to view and manage:</p>

                <div class="space-y-3">
                    <?php 
                    $selected_perms = $_POST['permissions'] ?? ['students'];
                    foreach ($available_permissions as $key => $perm): 
                    ?>
                        <label class="flex items-start space-x-3.5 p-4 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-slate-50/50 transition cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="<?= $key ?>" <?= in_array($key, $selected_perms) ? 'checked' : '' ?> class="mt-0.5 w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                            <div>
                                <div class="text-xs font-bold text-slate-800"><?= htmlspecialchars($perm['label']) ?></div>
                                <div class="text-[11px] text-slate-500 mt-0.5"><?= htmlspecialchars($perm['desc']) ?></div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="list.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-2">
                    <i class="fas fa-check"></i> Create Staff Account
                </button>
            </div>

        </form>

    </main>
</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>
