<?php require_once __DIR__ . "/../../config/config.php"; ?>

<div class="w-64 bg-gray-900 text-white min-h-screen p-6">

<h2 class="text-2xl font-bold mb-8">Certificate Admin</h2>

<ul class="space-y-4">

<li>
<a href="<?= BASE_URL ?>/admin/dashboard.php"
class="block hover:text-green-400">
Dashboard
</a>
</li>

<li>
<a href="<?= BASE_URL ?>/admin/students/list.php"
class="block hover:text-green-400">
Students
</a>
</li>

<li>
<a href="<?= BASE_URL ?>/admin/courses/list.php"
class="block hover:text-green-400">
Courses
</a>
</li>

<li>
<a href="<?= BASE_URL ?>/admin/mentors/list.php"
class="block hover:text-green-400">
Mentors
</a>
</li>

<li>
<a href="<?= BASE_URL ?>/admin/api_keys/list.php"
class="block hover:text-green-400">
API Keys
</a>
</li>

<li>
<a href="<?= BASE_URL ?>/admin/logout.php"
class="block text-red-400 hover:text-red-500">
Logout
</a>
</li>

</ul>

</div>