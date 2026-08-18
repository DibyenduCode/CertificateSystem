<?php
$page_title = "Manage Students";
require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

/* SEARCH & FILTERS */
$search       = trim($_GET['search'] ?? '');
$course_id    = trim($_GET['course_id'] ?? '');
$institute_id = trim($_GET['institute_id'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$limit        = 10;

$where_clauses = [];
$params = [];

if ($search !== '') {
    $where_clauses[] = "(students.name LIKE ? OR students.registration_number LIKE ? OR students.certificate_number LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($course_id !== '') {
    $where_clauses[] = "students.course_id = ?";
    $params[] = $course_id;
}

if ($institute_id !== '') {
    $where_clauses[] = "students.institute_id = ?";
    $params[] = $institute_id;
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

/* TOTAL COUNT FOR PAGINATION */
$count_sql = "SELECT COUNT(*) FROM students $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_rows = (int)$stmt->fetchColumn();

/* GET PAGINATION METRICS */
$pagination = get_pagination_data($total_rows, $limit, $page);
$offset = $pagination['offset'];

/* FETCH STUDENTS DATA */
$sql = "
    SELECT students.*,
           courses.name AS course,
           mentors.name AS mentor,
           institutes.name AS institute
    FROM students
    LEFT JOIN courses ON courses.id = students.course_id
    LEFT JOIN mentors ON mentors.id = students.mentor_id
    LEFT JOIN institutes ON institutes.id = students.institute_id
    $where_sql
    ORDER BY students.id DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* FETCH LOOKUPS FOR FILTER DROPDOWNS */
$courses_list = $pdo->query("SELECT id, name FROM courses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$institutes_list = $pdo->query("SELECT id, name FROM institutes ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$export_query = http_build_query(['search' => $search, 'course_id' => $course_id, 'institute_id' => $institute_id]);
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <!-- PAGE HEADER -->
    <header class="bg-white border-b border-slate-200 px-8 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-user-graduate text-blue-600"></i> Student Certificates
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage, search, and export registered student records</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="export.php?<?= $export_query ?>" class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="fas fa-file-csv mr-2"></i> Export CSV
            </a>
            <a href="add.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="fas fa-user-plus mr-2"></i> Register Student
            </a>
        </div>
    </header>

    <!-- CONTENT BODY -->
    <main class="p-8 space-y-6">

        <!-- SEARCH AND FILTER TOOLBAR -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Search Keyword -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Search Keyword</label>
                    <div class="relative">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name, Reg No, Cert No..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    </div>
                </div>

                <!-- Course Filter -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Filter by Course</label>
                    <select name="course_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                        <option value="">All Courses</option>
                        <?php foreach ($courses_list as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $course_id == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Institute Filter -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Filter by Institute</label>
                    <select name="institute_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                        <option value="">All Institutes</option>
                        <?php foreach ($institutes_list as $inst): ?>
                            <option value="<?= $inst['id'] ?>" <?= $institute_id == $inst['id'] ? 'selected' : '' ?>><?= htmlspecialchars($inst['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Actions -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <?php if ($search || $course_id || $institute_id): ?>
                        <a href="list.php" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-lg transition" title="Clear Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    <?php endif; ?>
                </div>

            </form>
        </div>

        <!-- STUDENTS TABLE CARD -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100/80 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Student Info</th>
                            <th class="px-6 py-3.5">Course</th>
                            <th class="px-6 py-3.5">Institute</th>
                            <th class="px-6 py-3.5">Grade</th>
                            <th class="px-6 py-3.5">Reg Number</th>
                            <th class="px-6 py-3.5 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                    <i class="fas fa-user-slash text-4xl mb-3 block"></i>
                                    No students found matching your criteria.
                                </td>
                            </tr>
                        <?php else:
                            foreach ($students as $row):
                        ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <?php if (!empty($row['student_photo']) && file_exists(__DIR__ . '/../../uploads/' . $row['student_photo'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($row['student_photo']) ?>" class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-sm">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs">
                                                <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($row['name']) ?></div>
                                            <?php if (!empty($row['email'])): ?>
                                                <div class="text-[11px] text-slate-500 font-normal flex items-center gap-1"><i class="fas fa-envelope text-[10px] text-blue-500"></i> <?= htmlspecialchars($row['email']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 font-medium text-slate-700">
                                    <?= htmlspecialchars($row['course'] ?? 'N/A') ?>
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    <?= htmlspecialchars($row['institute'] ?? 'N/A') ?>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <?= htmlspecialchars($row['grade'] ?? 'Pass') ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4 font-mono font-bold text-blue-700 text-xs">
                                    <?= htmlspecialchars($row['registration_number']) ?>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center space-x-1.5">
                                        <!-- Details toggle button -->
                                        <button onclick="toggleDetails(<?= $row['id'] ?>)" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition" title="View Details">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>

                                        <!-- PDF Certificate Download -->
                                        <a href="<?= BASE_URL ?>/public/download.php?cert=<?= urlencode($row['certificate_number']) ?>" target="_blank" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded transition" title="Download PDF Certificate">
                                            <i class="fas fa-file-pdf text-xs"></i>
                                        </a>

                                         <!-- PDF Marksheet Download -->
                                        <a href="<?= BASE_URL ?>/public/download_marksheet.php?cert=<?= urlencode($row['certificate_number']) ?>" target="_blank" class="p-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded transition" title="Download PDF Marksheet">
                                            <i class="fas fa-file-alt text-xs"></i>
                                        </a>

                                         <!-- Govt ID Document (PDF) -->
                                        <?php if (!empty($row['gov_id_doc']) && file_exists(__DIR__ . '/../../uploads/' . $row['gov_id_doc'])): ?>
                                            <a href="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($row['gov_id_doc']) ?>" target="_blank" class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded transition" title="View Govt ID Document (PDF)">
                                                <i class="fas fa-id-card text-xs"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Resend Email button -->
                                        <?php if (!empty($row['email'])): ?>
                                            <a href="resend_email.php?id=<?= $row['id'] ?>&redirect=list" onclick="return confirm('Resend congratulation email to <?= addslashes($row['name']) ?> (<?= htmlspecialchars($row['email']) ?>)?')" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded transition" title="Resend Congratulation Email">
                                                <i class="fas fa-paper-plane text-xs"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Edit button -->
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="p-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded transition" title="Edit Student">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>

                                        <!-- Delete button -->
                                        <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete student: <?= addslashes($row['name']) ?>?')" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded transition" title="Delete Student">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                </td>

                            </tr>

                            <!-- EXPANDABLE FULL DETAILS ROW -->
                            <tr id="details-<?= $row['id'] ?>" class="hidden bg-slate-50/90 border-t-0">
                                <td colspan="6" class="px-6 py-5">
                                    <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-inner grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                                        <div>
                                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Father's Name</span>
                                            <span class="font-medium text-slate-800"><?= htmlspecialchars($row['father_name'] ?? 'N/A') ?></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Gender</span>
                                            <span class="font-medium text-slate-800"><?= htmlspecialchars($row['gender'] ?? 'N/A') ?></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Date of Birth</span>
                                            <span class="font-medium text-slate-800"><?= htmlspecialchars($row['dob'] ?? 'N/A') ?></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Mentor</span>
                                            <span class="font-medium text-slate-800"><?= htmlspecialchars($row['mentor'] ?? 'N/A') ?></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Theory Marks</span>
                                            <span class="font-bold text-blue-700"><?= (int)($row['theory_marks'] ?? 0) ?></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Practical Marks</span>
                                            <span class="font-bold text-purple-700"><?= (int)($row['practical_marks'] ?? 0) ?></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Total Marks</span>
                                            <span class="font-bold text-emerald-700"><?= ((int)($row['theory_marks'] ?? 0) + (int)($row['practical_marks'] ?? 0)) ?></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Certificate Issue Date</span>
                                            <span class="font-medium text-slate-800"><?= htmlspecialchars($row['issue_date'] ?? 'N/A') ?></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Certificate Number</span>
                                            <span class="font-mono font-bold text-emerald-700"><?= htmlspecialchars($row['certificate_number']) ?></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 font-semibold uppercase text-[10px] block">Govt ID Document</span>
                                            <?php if (!empty($row['gov_id_doc']) && file_exists(__DIR__ . '/../../uploads/' . $row['gov_id_doc'])): ?>
                                                <a href="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($row['gov_id_doc']) ?>" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-semibold underline mt-0.5">
                                                    <i class="fas fa-file-pdf text-rose-600"></i> View Govt ID (PDF)
                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-400 font-normal">Not Uploaded</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION FOOTER -->
            <?php 
            $query_params = ['search' => $search, 'course_id' => $course_id, 'institute_id' => $institute_id];
            include __DIR__ . "/../partials/pagination.php"; 
            ?>

        </div>

    </main>
</div>

<script>
function toggleDetails(id) {
    const row = document.getElementById("details-" + id);
    if (row) {
        row.classList.toggle("hidden");
    }
}
</script>

<?php include __DIR__ . "/../partials/footer.php"; ?>