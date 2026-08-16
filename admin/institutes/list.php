<?php
$page_title = "Manage Institutes";
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
    $where = "WHERE name LIKE ? OR code LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM institutes $where");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();

$pagination = get_pagination_data($total_rows, $limit, $page);
$offset = $pagination['offset'];

$sql = "
    SELECT institutes.*, 
           (SELECT COUNT(*) FROM students WHERE institute_id = institutes.id) AS total_students
    FROM institutes
    $where
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$institutes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-university text-blue-600"></i> Affiliated Institutes
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage partner institutes and center branches</p>
        </div>
        <a href="add.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i class="fas fa-plus mr-2"></i> Add Institute
        </a>
    </header>

    <main class="p-8 space-y-6">

        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <form method="GET" class="flex gap-3 max-w-md">
                <div class="relative flex-1">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or code..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
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
                            <th class="px-6 py-3.5">Institute Name</th>
                            <th class="px-6 py-3.5">Institute Code</th>
                            <th class="px-6 py-3.5">Registered Students</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($institutes)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                                    No institutes found.
                                </td>
                            </tr>
                        <?php else: foreach ($institutes as $i): ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-6 py-4 font-bold text-slate-800 text-sm">
                                    <?= htmlspecialchars($i['name']) ?>
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-blue-700 text-xs">
                                    <?= htmlspecialchars($i['code'] ?? '1R') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        <i class="fas fa-user-graduate text-[10px] mr-1"></i> <?= $i['total_students'] ?> Students
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center space-x-2">
                                        <a href="edit.php?id=<?= $i['id'] ?>" class="p-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded transition" title="Edit Institute">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $i['id'] ?>" onclick="return confirm('Delete institute: <?= addslashes($i['name']) ?>?')" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded transition" title="Delete Institute">
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

<?php include __DIR__ . "/../partials/footer.php"; ?>