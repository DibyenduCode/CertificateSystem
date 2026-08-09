<?php
$page_title = "API Keys";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

$search = trim($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;

$params = [];
$where = "";
if ($search !== '') {
    $where = "WHERE name LIKE ? OR api_key LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM api_keys $where");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();

$pagination = get_pagination_data($total_rows, $limit, $page);
$offset = $pagination['offset'];

$sql = "SELECT * FROM api_keys $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$api_keys = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-key text-blue-600"></i> REST API Keys
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">API access credentials for third-party verification</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/public/api_demo.html" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="fas fa-code mr-1.5"></i> Test API Portal HTML
            </a>
            <a href="add.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="fas fa-plus mr-2"></i> Generate API Key
            </a>
        </div>
    </header>

    <main class="p-8 space-y-6">

        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <form method="GET" class="flex gap-3 max-w-md">
                <div class="relative flex-1">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search client name or key..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    Search
                </button>
                <?php if ($search): ?>
                    <a href="list.php" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-lg transition" title="Clear">
                        <i class="fas fa-undo"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100/80 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Client App Name</th>
                            <th class="px-6 py-3.5">API Key</th>
                            <th class="px-6 py-3.5">Approved Domain</th>
                            <th class="px-6 py-3.5">Hits Count</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($api_keys)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                    No API keys generated yet.
                                </td>
                            </tr>
                        <?php else: foreach ($api_keys as $key): ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-6 py-4 font-bold text-slate-800 text-sm">
                                    <?= htmlspecialchars($key['name']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <code class="font-mono text-xs bg-slate-100 px-2 py-1 rounded border border-slate-200 text-slate-700 select-all">
                                            <?= htmlspecialchars(substr($key['api_key'], 0, 16)) ?>...
                                        </code>
                                        <button onclick="copyToClipboard('<?= htmlspecialchars($key['api_key']) ?>')" class="text-slate-400 hover:text-blue-600 transition p-1" title="Copy Full API Key">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($key['allowed_domain'])): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-indigo-50 text-indigo-700 border border-indigo-200" title="Restricted to this domain">
                                            <i class="fas fa-globe text-[10px] mr-1 text-indigo-500"></i> <?= htmlspecialchars($key['allowed_domain']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-500 font-semibold" title="Unrestricted domain access">
                                            Any Domain (*)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-bolt text-[10px] mr-1"></i> <?= number_format($key['hit_count']) ?> hits
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($key['status'] === 'active'): ?>
                                        <a href="toggle.php?id=<?= $key['id'] ?>" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 hover:bg-emerald-200 transition" title="Click to deactivate">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Active
                                        </a>
                                    <?php else: ?>
                                        <a href="toggle.php?id=<?= $key['id'] ?>" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 hover:bg-rose-200 transition" title="Click to activate">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span> Inactive
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center space-x-1.5">
                                        <a href="edit.php?id=<?= $key['id'] ?>" class="p-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded transition" title="Edit Domain Settings">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $key['id'] ?>" onclick="return confirm('Revoke and delete API Key for: <?= addslashes($key['name']) ?>?')" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded transition" title="Delete API Key">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <?php 
            $query_params = ['search' => $search];
            include __DIR__ . "/../partials/pagination.php"; 
            ?>
        </div>

    </main>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'API key copied to clipboard!',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
</script>

<?php include __DIR__ . "/../partials/footer.php"; ?>