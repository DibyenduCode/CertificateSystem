<?php

require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/../config/database.php";

include __DIR__ . "/partials/header.php";
include __DIR__ . "/partials/sidebar.php";

/* Dashboard stats */

$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$total_mentors = $pdo->query("SELECT COUNT(*) FROM mentors")->fetchColumn();
$total_api = $pdo->query("SELECT COUNT(*) FROM api_keys")->fetchColumn();

?>

<div class="flex-1 flex flex-col">

<!-- Top Header -->

<header class="bg-white shadow px-6 py-4 flex justify-between items-center">

<h1 class="text-lg font-semibold text-gray-700">
Dashboard
</h1>

<span class="text-sm text-gray-500">
Admin Panel
</span>

</header>


<main class="p-6 space-y-6">

<!-- STAT CARDS -->

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

<div class="bg-white shadow rounded-lg p-6">

<p class="text-sm text-gray-500">Total Students</p>

<p class="text-3xl font-bold text-blue-600 mt-2">
<?= $total_students ?>
</p>

</div>


<div class="bg-white shadow rounded-lg p-6">

<p class="text-sm text-gray-500">Courses</p>

<p class="text-3xl font-bold text-blue-600 mt-2">
<?= $total_courses ?>
</p>

</div>


<div class="bg-white shadow rounded-lg p-6">

<p class="text-sm text-gray-500">Mentors</p>

<p class="text-3xl font-bold text-blue-600 mt-2">
<?= $total_mentors ?>
</p>

</div>


<div class="bg-white shadow rounded-lg p-6">

<p class="text-sm text-gray-500">API Keys</p>

<p class="text-3xl font-bold text-blue-600 mt-2">
<?= $total_api ?>
</p>

</div>

</div>


<!-- RECENT STUDENTS -->

<div class="bg-white shadow rounded-lg">

<div class="p-4 border-b">

<h2 class="font-semibold text-gray-700">
Recent Students
</h2>

</div>


<div class="overflow-x-auto">

<table class="w-full text-sm">

<thead class="bg-gray-100 text-gray-600">

<tr>

<th class="p-3 text-left">Name</th>
<th class="p-3 text-left">Course</th>
<th class="p-3 text-left">Mentor</th>
<th class="p-3 text-left">Grade</th>
<th class="p-3 text-left">Registration</th>

</tr>

</thead>

<tbody>

<?php

$stmt = $pdo->query("
SELECT students.*,courses.name AS course,mentors.name AS mentor
FROM students
LEFT JOIN courses ON courses.id = students.course_id
LEFT JOIN mentors ON mentors.id = students.mentor_id
ORDER BY students.id DESC
LIMIT 5
");

while($row = $stmt->fetch(PDO::FETCH_ASSOC)):

?>

<tr class="border-t hover:bg-gray-50">

<td class="p-3"><?= htmlspecialchars($row['name']) ?></td>

<td class="p-3"><?= htmlspecialchars($row['course']) ?></td>

<td class="p-3"><?= htmlspecialchars($row['mentor']) ?></td>

<td class="p-3"><?= htmlspecialchars($row['grade']) ?></td>

<td class="p-3"><?= $row['registration_number'] ?></td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>


</main>

