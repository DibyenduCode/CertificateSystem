<?php
$page_title = "Manage Staff Members";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

// Only Admin can manage staff
if (!is_admin() && empty($_SESSION['impersonated_by_admin'])) {
    set_flash('error', "Access Denied: Only Administrator can manage staff accounts.");
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

$search = trim($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;

$where_sql = "";
$params = [];
if ($search !== '') {
    $where_sql = "WHERE name LIKE ? OR username LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM staff $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();

$pagination = get_pagination_data($total_rows, $limit, $page);
$offset = $pagination['offset'];

$sql = "SELECT * FROM staff $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$staff_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

$module_labels = [
    'students'  => 'Students',
    'courses'   => 'Courses',
    'mentors'   => 'Mentors',
    'institutes'=> 'Institutes',
    'api_keys'  => 'API Keys'
];
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <!-- PAGE HEADER -->
    <header class="bg-white border-b border-slate-200 px-8 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-users-cog text-blue-600"></i> Staff Accounts & Permissions
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage staff members, access permissions, and 1-click dashboard access</p>
        </div>
        <a href="add.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i class="fas fa-user-plus mr-2"></i> Register New Staff
        </a>
    </header>

    <main class="p-8 space-y-6">

        <!-- SEARCH AND FILTER BAR -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row gap-4 justify-between items-center">
            <form method="GET" class="flex gap-3 w-full sm:w-auto flex-1 max-w-md">
                <div class="relative flex-1">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by staff name or username..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition">
                    Search
                </button>
                <?php if ($search !== ''): ?>
                    <a href="list.php" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- STAFF TABLE -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="px-6 py-3.5">Staff Member</th>
                            <th class="px-6 py-3.5">Username</th>
                            <th class="px-6 py-3.5">Allowed Feature Permissions</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-center">1-Click Access</th>
                            <th class="px-6 py-3.5 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($staff_members)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                    <i class="fas fa-user-slash text-3xl mb-2 block"></i>
                                    No staff accounts found. <a href="add.php" class="text-blue-600 underline">Add a new staff member</a>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($staff_members as $staff): 
                                $perms = json_decode($staff['permissions'] ?? '[]', true);
                                if (!is_array($perms)) $perms = [];
                            ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4 font-bold text-slate-800">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                                                <?= strtoupper(substr($staff['name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="text-slate-900 font-bold"><?= htmlspecialchars($staff['name']) ?></div>
                                                <div class="text-[10px] text-slate-400">ID: #<?= $staff['id'] ?> &bull; Added <?= date("d M Y", strtotime($staff['created_at'])) ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 font-mono font-semibold text-slate-700">
                                        <?= htmlspecialchars($staff['username']) ?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <?php if (empty($perms)): ?>
                                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-500 font-medium">None</span>
                                            <?php else: ?>
                                                <?php foreach ($perms as $p): ?>
                                                    <span class="px-2 py-0.5 rounded text-[10px] bg-blue-50 text-blue-700 border border-blue-200 font-medium">
                                                        <i class="fas fa-check-circle text-[9px] mr-1"></i><?= htmlspecialchars($module_labels[$p] ?? $p) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <?php if ($staff['status'] === 'active'): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Active
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span> Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <a href="impersonate.php?id=<?= $staff['id'] ?>" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs transition" title="Access this staff's dashboard with 1 click">
                                            <i class="fas fa-sign-in-alt mr-1.5"></i> Access Portal
                                        </a>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center space-x-1.5">
                                            <a href="edit.php?id=<?= $staff['id'] ?>" class="p-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded transition" title="Edit Staff Permissions">
                                                <i class="fas fa-edit text-xs"></i>
                                            </a>
                                            <a href="delete.php?id=<?= $staff['id'] ?>" onclick="return confirm('Are you sure you want to delete staff account: <?= addslashes($staff['name']) ?>? (Note: Entry data added by this staff will NOT be deleted)')" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded transition" title="Delete Staff Account">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php include __DIR__ . "/../partials/pagination.php"; ?>
        </div>

    </main>
</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>
