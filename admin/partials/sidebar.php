<?php
$current_page = $_SERVER['SCRIPT_NAME'];

function is_active($path) {
    global $current_page;
    return strpos($current_page, $path) !== false ? 'bg-blue-600 text-white font-semibold shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white';
}
?>

<aside class="w-64 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white flex flex-col shrink-0 shadow-2xl">

    <!-- BRAND LOGO -->
    <div class="p-5 border-b border-slate-700/60 flex items-center space-x-3">
        <div class="w-12 h-12 rounded-xl bg-white p-1.5 flex items-center justify-center shadow-lg shrink-0">
            <img src="<?= BASE_URL ?>/assets/logo.png" alt="Biswas Company Logo" class="w-full h-full object-contain">
        </div>
        <div>
            <h2 class="text-base font-bold tracking-tight text-white">CertiPortal</h2>
            <p class="text-xs text-slate-400">Admin Control Center</p>
        </div>
    </div>

    <!-- NAVIGATION MENU -->
    <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">

        <div class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Main Core</div>

        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs transition duration-150 <?= is_active('dashboard.php') ?>">
            <i class="fas fa-chart-line w-4 text-center"></i>
            <span>Dashboard</span>
        </a>

        <?php if (has_permission('students')): ?>
            <a href="<?= BASE_URL ?>/admin/students/list.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs transition duration-150 <?= is_active('students/') ?>">
                <i class="fas fa-user-graduate w-4 text-center"></i>
                <span>Students</span>
            </a>
        <?php endif; ?>

        <?php if (has_permission('courses') || has_permission('subjects') || has_permission('mentors') || has_permission('institutes')): ?>
            <div class="pt-3 px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Academic Setup</div>

            <?php if (has_permission('courses')): ?>
                <a href="<?= BASE_URL ?>/admin/courses/list.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs transition duration-150 <?= is_active('courses/') ?>">
                    <i class="fas fa-book-open w-4 text-center"></i>
                    <span>Courses</span>
                </a>
            <?php endif; ?>

            <?php if (has_permission('subjects') || has_permission('courses')): ?>
                <a href="<?= BASE_URL ?>/admin/subjects/list.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs transition duration-150 <?= is_active('subjects/') ?>">
                    <i class="fas fa-layer-group w-4 text-center"></i>
                    <span>Subjects</span>
                </a>
            <?php endif; ?>

            <?php if (has_permission('mentors')): ?>
                <a href="<?= BASE_URL ?>/admin/mentors/list.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs transition duration-150 <?= is_active('mentors/') ?>">
                    <i class="fas fa-chalkboard-teacher w-4 text-center"></i>
                    <span>Mentors</span>
                </a>
            <?php endif; ?>

            <?php if (has_permission('institutes')): ?>
                <a href="<?= BASE_URL ?>/admin/institutes/list.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs transition duration-150 <?= is_active('institutes/') ?>">
                    <i class="fas fa-university w-4 text-center"></i>
                    <span>Institutes</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <div class="pt-3 px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">System & Access</div>

        <?php if (is_admin() || is_impersonating()): ?>
            <a href="<?= BASE_URL ?>/admin/staff/list.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs transition duration-150 <?= is_active('staff/') ?>">
                <i class="fas fa-users-cog w-4 text-center text-blue-400"></i>
                <span>Staff Accounts</span>
            </a>
        <?php endif; ?>

        <?php if (has_permission('api_keys')): ?>
            <a href="<?= BASE_URL ?>/admin/api_keys/list.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs transition duration-150 <?= is_active('api_keys/') ?>">
                <i class="fas fa-key w-4 text-center"></i>
                <span>API Keys</span>
            </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/admin/profile.php" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-xs transition duration-150 <?= is_active('profile.php') ?>">
            <i class="fas fa-user-shield w-4 text-center"></i>
            <span>Profile & Password</span>
        </a>

    </nav>

    <!-- USER & LOGOUT -->
    <div class="p-4 border-t border-slate-700/60 bg-slate-900/50 flex items-center justify-between">
        <div class="flex items-center space-x-2 min-w-0">
            <div class="w-8 h-8 rounded-full <?= is_admin() ? 'bg-blue-600 text-white' : 'bg-emerald-600 text-white' ?> flex items-center justify-center font-bold text-xs shrink-0">
                <?= is_admin() ? 'A' : strtoupper(substr($_SESSION['staff_name'] ?? 'S', 0, 1)) ?>
            </div>
            <div class="text-xs truncate">
                <p class="font-semibold text-slate-200 truncate">
                    <?= is_admin() ? 'Administrator' : htmlspecialchars($_SESSION['staff_name'] ?? 'Staff User') ?>
                </p>
                <p class="text-[10px] text-emerald-400 flex items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block mr-1"></span> 
                    <?= is_admin() ? 'Full Admin' : 'Staff Access' ?>
                </p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/admin/logout.php" onclick="return confirm('Log out of current session?')" class="text-rose-400 hover:text-rose-300 p-2 rounded-lg hover:bg-slate-800 transition text-sm" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>

</aside>