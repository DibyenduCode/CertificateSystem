<?php
$page_title = "Generate API Key";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$error = "";
$generated_key = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $allowed_domain = trim($_POST['allowed_domain'] ?? '');

    // Clean domain format (e.g. https://example.com/path -> example.com)
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

    if (!$name) {
        $error = "Client application name is required.";
    }

    if (!$error) {
        $generated_key = generateApiKey();

        $stmt = $pdo->prepare("
            INSERT INTO api_keys (name, api_key, allowed_domain, status, created_at)
            VALUES (?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([$name, $generated_key, $allowed_domain]);

        set_flash('success', "API Key for '{$name}' created successfully!");
    }
}

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-key text-blue-600"></i> Generate REST API Key
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Create a token with optional domain restriction</p>
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

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Application / Client Name *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="e.g. Mobile App / Corporate Verification Portal" required class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">Specify where this key will be used.</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Allowed Approved Domain (Domain Specific Verification)</label>
                    <input type="text" name="allowed_domain" value="<?= htmlspecialchars($_POST['allowed_domain'] ?? '') ?>" placeholder="e.g. example.com or app.mysite.org (Leave blank for Any Domain)" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none font-mono">
                    <p class="text-[11px] text-slate-400 mt-1">If specified, requests using this API key will be rejected if sent from any other domain.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        <i class="fas fa-magic mr-1"></i> Generate API Key
                    </button>
                </div>
            </form>

            <?php if ($generated_key): ?>
                <div class="mt-6 border-t border-slate-200 pt-6">
                    <label class="block text-xs font-bold text-slate-800 mb-2 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-shield-alt text-emerald-600"></i> New API Token Generated
                    </label>
                    
                    <div class="flex gap-2">
                        <input id="apiKey" value="<?= htmlspecialchars($generated_key) ?>" readonly class="flex-1 border border-slate-300 rounded-lg px-3 py-2 font-mono text-xs bg-slate-50 text-slate-800 focus:outline-none">
                        <button onclick="copyGeneratedKey()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1">
                            <i class="fas fa-copy"></i> Copy Key
                        </button>
                    </div>

                    <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-[11px] flex items-start space-x-2">
                        <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5"></i>
                        <span><strong>Important:</strong> Copy and store this key securely. Include it as header <code>Authorization: Bearer &lt;KEY&gt;</code> or <code>X-API-KEY: &lt;KEY&gt;</code> when making API requests.</span>
                    </div>
                </div>
            <?php endif; ?>

        </div>

    </main>
</div>

<script>
function copyGeneratedKey() {
    const input = document.getElementById("apiKey");
    if (input) {
        input.select();
        navigator.clipboard.writeText(input.value).then(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'API key copied to clipboard!',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        });
    }
}
</script>

<?php include __DIR__ . "/../partials/footer.php"; ?>