<?php
$page_title = "Manage Course Subjects";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

$search    = trim($_GET['search'] ?? '');
$course_id = (int)($_GET['course_id'] ?? 0);
$page      = max(1, (int)($_GET['page'] ?? 1));
$limit     = 10;

$params = [];
$where_clauses = [];

if ($search !== '') {
    $where_clauses[] = "subjects.name LIKE ?";
    $params[] = "%$search%";
}

if ($course_id > 0) {
    $where_clauses[] = "subjects.course_id = ?";
    $params[] = $course_id;
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Count total subjects matching filter
$count_stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM subjects 
    JOIN courses ON courses.id = subjects.course_id
    $where_sql
");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();

$pagination = get_pagination_data($total_rows, $limit, $page);
$offset = $pagination['offset'];

// Fetch subjects with course name
$sql = "
    SELECT subjects.*, courses.name AS course_name
    FROM subjects
    JOIN courses ON courses.id = subjects.course_id
    $where_sql
    ORDER BY courses.name ASC, subjects.id ASC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all courses for the filter dropdown
$all_courses = $pdo->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <!-- PAGE HEADER -->
    <header class="bg-white border-b border-slate-200 px-8 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-layer-group text-indigo-600"></i> Course Subjects
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage subjects and topics covered in each course</p>
        </div>
        <a href="add.php<?= $course_id ? '?course_id=' . $course_id : '' ?>" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i class="fas fa-plus mr-2"></i> Add Subject
        </a>
    </header>

    <main class="p-8 space-y-6">

        <!-- SEARCH & FILTER BAR -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search subject title..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                </div>

                <div class="sm:w-64">
                    <select name="course_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                        <option value="0">All Courses</option>
                        <?php foreach ($all_courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $course_id === (int)$c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        Filter
                    </button>
                    <?php if ($search || $course_id): ?>
                        <a href="list.php" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-lg transition" title="Clear Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- SUBJECTS TABLE -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100/80 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5 w-16 text-center">#</th>
                            <th class="px-6 py-3.5">Subject Title</th>
                            <th class="px-6 py-3.5">Associated Course</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($subjects)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                                    No subjects found.
                                </td>
                            </tr>
                        <?php else: 
                            $i = $offset + 1;
                            foreach ($subjects as $s): 
                        ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-6 py-4 text-center font-semibold text-slate-400">
                                    <?= $i++ ?>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-800 text-sm">
                                    <?= htmlspecialchars($s['name']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fas fa-book-open text-[10px] mr-1"></i> <?= htmlspecialchars($s['course_name']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center space-x-2">
                                        <a href="edit.php?id=<?= $s['id'] ?>" class="p-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded transition" title="Edit Subject">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $s['id'] ?>" onclick="return confirm('Delete subject: <?= addslashes($s['name']) ?>?')" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded transition" title="Delete Subject">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php 
            $query_params = ['search' => $search, 'course_id' => $course_id];
            include __DIR__ . "/../partials/pagination.php"; 
            ?>
        </div>

    </main>
</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>
