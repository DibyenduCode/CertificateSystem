<?php
$page_title = "SMTP Email Settings";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

ensure_smtp_and_email_tables($pdo);

$test_result = null;
$errors = [];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save_settings';

    if ($action === 'save_settings') {
        $settings = [
            'smtp_enabled'    => isset($_POST['smtp_enabled']) ? '1' : '0',
            'smtp_host'       => trim($_POST['smtp_host'] ?? ''),
            'smtp_port'       => trim($_POST['smtp_port'] ?? '587'),
            'smtp_auth'       => isset($_POST['smtp_auth']) ? '1' : '0',
            'smtp_username'   => trim($_POST['smtp_username'] ?? ''),
            'smtp_password'   => trim($_POST['smtp_password'] ?? ''),
            'smtp_encryption' => trim($_POST['smtp_encryption'] ?? 'tls'),
            'smtp_from_email' => trim($_POST['smtp_from_email'] ?? ''),
            'smtp_from_name'  => trim($_POST['smtp_from_name'] ?? '')
        ];

        save_smtp_settings($pdo, $settings);
        set_flash('success', "SMTP Email Settings saved successfully!");
        header("Location: smtp.php");
        exit;
    } elseif ($action === 'test_email') {
        $test_to = trim($_POST['test_to_email'] ?? '');
        if (empty($test_to) || !filter_var($test_to, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid target email address for testing.";
        } else {
            $current_settings = get_smtp_settings($pdo);
            $mailer = new SmtpMailer($current_settings);

            $subject = "CertiPortal SMTP Test Email - " . date('Y-m-d H:i:s');
            $htmlBody = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f8fafc;'>
                    <h2 style='color: #2563eb;'>CertiPortal SMTP Test Message</h2>
                    <p>If you are reading this email, your SMTP configuration is working perfectly!</p>
                    <ul>
                        <li><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</li>
                        <li><strong>SMTP Host:</strong> {$current_settings['smtp_host']}</li>
                        <li><strong>Port:</strong> {$current_settings['smtp_port']}</li>
                        <li><strong>Encryption:</strong> {$current_settings['smtp_encryption']}</li>
                    </ul>
                </div>
            ";

            $success = $mailer->send($test_to, $subject, $htmlBody);

            $test_result = [
                'success' => $success,
                'message' => $success ? "Test email sent successfully to {$test_to}!" : "Failed to send test email to {$test_to}.",
                'debugLog' => $mailer->debugLog
            ];
        }
    }
}

$settings = get_smtp_settings($pdo);

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <!-- PAGE HEADER -->
    <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-paper-plane text-blue-600"></i> SMTP Email Configuration
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure mail server credentials for automated student congratulation emails</p>
        </div>
    </header>

    <main class="p-8 max-w-5xl space-y-8">

        <?php if ($errors): ?>
            <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-xs">
                <?php foreach ($errors as $e): ?>
                    <p><i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($test_result): ?>
            <div class="p-5 rounded-xl border <?= $test_result['success'] ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-rose-50 border-rose-300 text-rose-900' ?>">
                <div class="flex items-center gap-2 font-bold text-sm mb-2">
                    <i class="fas <?= $test_result['success'] ? 'fa-check-circle text-emerald-600' : 'fa-times-circle text-rose-600' ?>"></i>
                    <?= htmlspecialchars($test_result['message']) ?>
                </div>

                <div class="mt-3">
                    <p class="text-xs font-bold mb-1 opacity-80 uppercase tracking-wider">SMTP Transaction Log:</p>
                    <pre class="bg-slate-900 text-slate-200 text-[11px] p-3 rounded-lg overflow-x-auto max-h-60 leading-relaxed font-mono"><?php foreach ($test_result['debugLog'] as $logLine) { echo htmlspecialchars($logLine) . "\n"; } ?></pre>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- SMTP FORM (LEFT 2 COLUMNS) -->
            <div class="lg:col-span-2">
                <form method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6">
                    <input type="hidden" name="action" value="save_settings">

                    <!-- ENABLE TOGGLE -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <label class="text-sm font-bold text-slate-800">Enable Automated SMTP Emails</label>
                            <p class="text-xs text-slate-500">Automatically send congratulation email when student entry is registered</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="smtp_enabled" value="1" <?= !empty($settings['smtp_enabled']) && $settings['smtp_enabled'] !== '0' ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- HOST & PORT -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 mb-1">SMTP Host Server *</label>
                            <input type="text" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host']) ?>" required placeholder="e.g. smtp.gmail.com or smtp.mailtrap.io" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Port *</label>
                            <input type="number" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port']) ?>" required placeholder="587" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- ENCRYPTION & AUTH -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Encryption Security</label>
                            <select name="smtp_encryption" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                                <option value="tls" <?= strtolower($settings['smtp_encryption']) === 'tls' ? 'selected' : '' ?>>TLS / STARTTLS (Port 587)</option>
                                <option value="ssl" <?= strtolower($settings['smtp_encryption']) === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                                <option value="none" <?= strtolower($settings['smtp_encryption']) === 'none' ? 'selected' : '' ?>>None (Plain Text - Port 25)</option>
                            </select>
                        </div>
                        <div class="flex items-center pt-5">
                            <label class="inline-flex items-center text-xs text-slate-700 font-medium cursor-pointer">
                                <input type="checkbox" name="smtp_auth" value="1" <?= !empty($settings['smtp_auth']) && $settings['smtp_auth'] !== '0' ? 'checked' : '' ?> class="rounded text-blue-600 focus:ring-blue-500 mr-2">
                                Require SMTP Authentication
                            </label>
                        </div>
                    </div>

                    <!-- USERNAME & PASSWORD -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">SMTP Username / Email</label>
                            <input type="text" name="smtp_username" value="<?= htmlspecialchars($settings['smtp_username']) ?>" placeholder="your-email@example.com" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">SMTP Password / App Secret</label>
                            <input type="password" name="smtp_password" value="<?= htmlspecialchars($settings['smtp_password']) ?>" placeholder="••••••••••••" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- FROM DETAILS -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Sender "From" Email *</label>
                            <input type="email" name="smtp_from_email" value="<?= htmlspecialchars($settings['smtp_from_email']) ?>" required placeholder="no-reply@beliefpro.org" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Sender "From" Name</label>
                            <input type="text" name="smtp_from_name" value="<?= htmlspecialchars($settings['smtp_from_name']) ?>" placeholder="BELIEFPRO LEARNING FORUM" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                            <i class="fas fa-save mr-1.5"></i> Save SMTP Credentials
                        </button>
                    </div>
                </form>
            </div>

            <!-- TEST EMAIL PANEL (RIGHT COLUMN) -->
            <div>
                <form method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
                    <input type="hidden" name="action" value="test_email">

                    <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-vial text-emerald-600"></i> Test Connection
                    </h2>
                    <p class="text-xs text-slate-500">Send an instant test email to verify your SMTP server configuration and handshake.</p>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Recipient Email Address</label>
                        <input type="email" name="test_to_email" required placeholder="admin@example.com" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Send Test Message
                    </button>
                </form>
            </div>

        </div>

    </main>
</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>
