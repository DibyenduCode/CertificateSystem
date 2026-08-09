<?php
$page_title = "Account Security & Profile";
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/functions.php";

$is_admin_user = is_admin();

if ($is_admin_user) {
    $user_id = $_SESSION['admin_id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $user_id = $_SESSION['staff_id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE id=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {
    set_flash('error', "User account session expired or record not found.");
    header("Location: " . BASE_URL . "/admin/login.php");
    exit;
}

$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === "POST") {

    $username         = trim($_POST['username'] ?? '');
    $name             = trim($_POST['name'] ?? ($user['name'] ?? ''));
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$username) {
        $errors[] = "Username cannot be empty.";
    }

    if (!$is_admin_user && !$name) {
        $errors[] = "Full name cannot be empty.";
    }

    if (!empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        }
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "New password and confirm password do not match.";
        }
    }

    if (empty($errors)) {
        if ($is_admin_user) {
            if (!empty($new_password)) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE admins SET username=?, password=? WHERE id=?");
                $update->execute([$username, $hashed, $user_id]);
            } else {
                $update = $pdo->prepare("UPDATE admins SET username=? WHERE id=?");
                $update->execute([$username, $user_id]);
            }
        } else {
            if (!empty($new_password)) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE staff SET name=?, username=?, password=? WHERE id=?");
                $update->execute([$name, $username, $hashed, $user_id]);
            } else {
                $update = $pdo->prepare("UPDATE staff SET name=?, username=? WHERE id=?");
                $update->execute([$name, $username, $user_id]);
            }
            $_SESSION['staff_name'] = $name;
            $_SESSION['staff_username'] = $username;
        }

        set_flash('success', "Profile details updated successfully.");
        header("Location: profile.php");
        exit;
    }
}

include __DIR__ . "/partials/header.php";
include __DIR__ . "/partials/sidebar.php";
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-user-shield text-blue-600"></i> Account Security & Profile
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Update credentials and login security</p>
        </div>
    </header>

    <main class="p-8">

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 max-w-xl space-y-6">

            <?php if (!empty($errors)): ?>
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-xs space-y-1">
                    <p class="font-bold flex items-center gap-1.5"><i class="fas fa-exclamation-triangle"></i> Please fix errors:</p>
                    <ul class="list-disc pl-5">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6 text-xs">

                <?php if (!$is_admin_user): ?>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Full Name *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Username *</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none font-mono">
                </div>

                <div class="border-t border-slate-200 pt-6 space-y-4">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-lock text-amber-500"></i> Change Password
                    </h3>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Current Password (Required only if changing password)</label>
                        <input type="password" name="current_password" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">New Password</label>
                            <input type="password" name="new_password" placeholder="Min 6 characters" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_password" placeholder="Re-type new password" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Save Profile Changes
                    </button>
                </div>

            </form>

        </div>

    </main>
</div>

<?php include __DIR__ . "/partials/footer.php"; ?>
