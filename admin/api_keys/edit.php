<?php
$page_title = "Edit API Key";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM api_keys WHERE id = ?");
$stmt->execute([$id]);
$key = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$key) {
    set_flash('error', "API Key record not found.");
    header("Location: list.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $allowed_domain = trim($_POST['allowed_domain'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if (!$name) {
        $error = "Client application name is required.";
    }

    if ($allowed_domain !== '' && $allowed_domain !== '*') {
        $parsed = parse_url($allowed_domain, PHP_URL_HOST);
        if ($parsed) {
            $allowed_domain = $parsed;
        } else {
            $allowed_domain = preg_replace('/^https?:\/\//i', '', $allowed_domain);
            $allowed_domain = explode('/', $allowed_domain)[0];
            $allowed_domain = explode(':', $allowed_domain)[0];
        }
        $allowed_domain = strtolower(trim($allowed_domain));
    } else {
        $allowed_domain = null;
    }

    if (!$error) {
        $stmt_update = $pdo->prepare("
            UPDATE api_keys 
            SET name = ?, allowed_domain = ?, status = ?
            WHERE id = ?
        ");
        $stmt_update->execute([$name, $allowed_domain, $status, $id]);

        set_flash('success', "API Key for '{$name}' updated successfully!");
        header("Location: list.php");
        exit;
    }
} else {
    $_POST['name'] = $key['name'];
    $_POST['allowed_domain'] = $key['allowed_domain'];
    $_POST['status'] = $key['status'];
}

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-edit text-blue-600"></i> Edit REST API Key Settings
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure client name, approved domain restrictions, and key status</p>
        </div>
        <a href="list.php" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to API Keys
        </a>
    </header>

    <main class="p-8">

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 max-w-xl space-y-6">

            <?php if ($error): ?>
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-xs">
                    <i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5 text-xs">

                <div>
                    <label class="block font-medium text-slate-700 mb-1">API Key Token (Read-Only)</label>
                    <input type="text" value="<?= htmlspecialchars($key['api_key']) ?>" readonly class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg bg-slate-100 font-mono text-slate-700 select-all">
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Application / Client Name *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Allowed Approved Domain (Domain Specific Verification)</label>
                    <input type="text" name="allowed_domain" value="<?= htmlspecialchars($_POST['allowed_domain'] ?? '') ?>" placeholder="e.g. example.com or app.mysite.org (Leave blank for Any Domain)" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none font-mono">
                    <p class="text-[11px] text-slate-400 mt-1">If specified, requests using this API key will be rejected if sent from any unapproved domain.</p>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Status *</label>
                    <select name="status" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                        <option value="active" <?= ($_POST['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="pt-3 flex justify-end gap-3 border-t border-slate-200">
                    <a href="list.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition flex items-center gap-1.5">
                        <i class="fas fa-save"></i> Save API Key Settings
                    </button>
                </div>
            </form>

        </div>

    </main>
</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>
