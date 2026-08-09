<?php
$page_title = "Dashboard";
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/../config/database.php";

include __DIR__ . "/partials/header.php";
include __DIR__ . "/partials/sidebar.php";

/* Dashboard Metrics - Fetch conditionally based on module permissions */
$total_students   = has_permission('students')   ? $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn() : 0;
$total_courses    = has_permission('courses')    ? $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn() : 0;
$total_institutes = has_permission('institutes') ? $pdo->query("SELECT COUNT(*) FROM institutes")->fetchColumn() : 0;
$total_mentors    = has_permission('mentors')    ? $pdo->query("SELECT COUNT(*) FROM mentors")->fetchColumn() : 0;
$active_api_keys  = has_permission('api_keys')   ? $pdo->query("SELECT COUNT(*) FROM api_keys WHERE status='active'")->fetchColumn() : 0;
?>

<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

    <!-- TOP BAR HEADER -->
    <header class="bg-white border-b border-slate-200 px-8 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-chart-line text-blue-600"></i> System Overview
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Real-time statistics & recent activity feed</p>
        </div>

        <!-- QUICK ACTIONS BUTTONS -->
        <div class="flex items-center gap-3">
            <?php if (has_permission('students')): ?>
                <a href="<?= BASE_URL ?>/admin/students/add.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <i class="fas fa-plus mr-2"></i> Add Student
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/public/index.php" target="_blank" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                <i class="fas fa-external-link-alt mr-2"></i> Public Portal
            </a>
        </div>
    </header>

    <!-- MAIN DASHBOARD CONTENT -->
    <main class="p-8 space-y-8">

        <!-- METRIC CARDS GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            
            <!-- Students -->
            <?php if (has_permission('students')): ?>
                <a href="<?= BASE_URL ?>/admin/students/list.php" class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-5 flex items-center justify-between hover:shadow-md hover:border-blue-300 transition group">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider group-hover:text-blue-600 transition">Students</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($total_students) ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold group-hover:bg-blue-600 group-hover:text-white transition">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </a>
            <?php endif; ?>

            <!-- Courses -->
            <?php if (has_permission('courses')): ?>
                <a href="<?= BASE_URL ?>/admin/courses/list.php" class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-5 flex items-center justify-between hover:shadow-md hover:border-emerald-300 transition group">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider group-hover:text-emerald-600 transition">Courses</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($total_courses) ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold group-hover:bg-emerald-600 group-hover:text-white transition">
                        <i class="fas fa-book-open"></i>
                    </div>
                </a>
            <?php endif; ?>

            <!-- Institutes -->
            <?php if (has_permission('institutes')): ?>
                <a href="<?= BASE_URL ?>/admin/institutes/list.php" class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-5 flex items-center justify-between hover:shadow-md hover:border-purple-300 transition group">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider group-hover:text-purple-600 transition">Institutes</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($total_institutes) ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold group-hover:bg-purple-600 group-hover:text-white transition">
                        <i class="fas fa-university"></i>
                    </div>
                </a>
            <?php endif; ?>

            <!-- Mentors -->
            <?php if (has_permission('mentors')): ?>
                <a href="<?= BASE_URL ?>/admin/mentors/list.php" class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-5 flex items-center justify-between hover:shadow-md hover:border-amber-300 transition group">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider group-hover:text-amber-600 transition">Mentors</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($total_mentors) ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold group-hover:bg-amber-600 group-hover:text-white transition">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </a>
            <?php endif; ?>

            <!-- Active API Keys -->
            <?php if (has_permission('api_keys')): ?>
                <a href="<?= BASE_URL ?>/admin/api_keys/list.php" class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-5 flex items-center justify-between hover:shadow-md hover:border-rose-300 transition group">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider group-hover:text-rose-600 transition">API Keys</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($active_api_keys) ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold group-hover:bg-rose-600 group-hover:text-white transition">
                        <i class="fas fa-key"></i>
                    </div>
                </a>
            <?php endif; ?>

        </div>

        <!-- RECENT STUDENT REGISTRATIONS -->
        <?php if (has_permission('students')): ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
                    <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fas fa-clock text-slate-400"></i> Recent Student Certificates
                    </h2>
                    <a href="<?= BASE_URL ?>/admin/students/list.php" class="text-xs text-blue-600 hover:text-blue-700 font-semibold hover:underline">
                        View All Students <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100/70 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3">Student Name</th>
                                <th class="px-6 py-3">Course</th>
                                <th class="px-6 py-3">Institute</th>
                                <th class="px-6 py-3">Grade</th>
                                <th class="px-6 py-3">Reg. Number</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/80">
                            <?php
                            $stmt = $pdo->query("
                                SELECT students.*, courses.name AS course, institutes.name AS institute
                                FROM students
                                LEFT JOIN courses ON courses.id = students.course_id
                                LEFT JOIN institutes ON institutes.id = students.institute_id
                                ORDER BY students.id DESC
                                LIMIT 6
                            ");
                            $recent_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($recent_students)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                        <i class="fas fa-folder-open text-3xl mb-2 block"></i>
                                        No student certificates found yet. <a href="<?= BASE_URL ?>/admin/students/add.php" class="text-blue-600 underline">Add one now</a>.
                                    </td>
                                </tr>
                            <?php else:
                                foreach ($recent_students as $row):
                            ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-3.5 font-semibold text-slate-800 flex items-center space-x-3">
                                        <?php if (!empty($row['student_photo']) && file_exists(__DIR__ . '/../uploads/' . $row['student_photo'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($row['student_photo']) ?>" class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-sm">
                                        <?php else: ?>
                                            <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs">
                                                <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($row['name']) ?></span>
                                    </td>
                                    <td class="px-6 py-3.5 text-slate-600"><?= htmlspecialchars($row['course'] ?? 'N/A') ?></td>
                                    <td class="px-6 py-3.5 text-slate-600"><?= htmlspecialchars($row['institute'] ?? 'N/A') ?></td>
                                    <td class="px-6 py-3.5">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                            <?= htmlspecialchars($row['grade'] ?? 'Pass') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 font-mono text-xs font-semibold text-blue-700"><?= htmlspecialchars($row['registration_number']) ?></td>
                                    <td class="px-6 py-3.5 text-right">
                                        <a href="<?= BASE_URL ?>/public/download.php?cert=<?= urlencode($row['certificate_number']) ?>" target="_blank" class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-medium transition" title="Download PDF Certificate">
                                            <i class="fas fa-file-pdf text-rose-500 mr-1"></i> PDF
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!has_permission('students') && !has_permission('courses') && !has_permission('institutes') && !has_permission('mentors') && !has_permission('api_keys')): ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center text-slate-500">
                <i class="fas fa-user-lock text-4xl text-slate-400 mb-3 block"></i>
                <h3 class="text-base font-bold text-slate-700 mb-1">Welcome to CertiPortal</h3>
                <p class="text-xs">You currently do not have permissions assigned to access specific academic modules. Please contact an Administrator to update your permissions.</p>
            </div>
        <?php endif; ?>

    </main>
</div>

<?php include __DIR__ . "/partials/footer.php"; ?>
