<?php

require_once "auth_check.php";
require_once "../config/database.php";

include "partials/header.php";

?>

<div class="flex">

<?php include "partials/sidebar.php"; ?>

<div class="flex-1 p-10">

<h1 class="text-3xl font-bold mb-8">
Admin Dashboard
</h1>

<?php

$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$total_mentors = $pdo->query("SELECT COUNT(*) FROM mentors")->fetchColumn();
$total_api = $pdo->query("SELECT COUNT(*) FROM api_keys")->fetchColumn();

?>

<div class="grid grid-cols-4 gap-6">

<div class="bg-white p-6 rounded shadow">

<h2 class="text-gray-600">
Total Students
</h2>

<p class="text-3xl font-bold mt-2">
<?= $total_students ?>
</p>

</div>


<div class="bg-white p-6 rounded shadow">

<h2 class="text-gray-600">
Total Courses
</h2>

<p class="text-3xl font-bold mt-2">
<?= $total_courses ?>
</p>

</div>


<div class="bg-white p-6 rounded shadow">

<h2 class="text-gray-600">
Total Mentors
</h2>

<p class="text-3xl font-bold mt-2">
<?= $total_mentors ?>
</p>

</div>


<div class="bg-white p-6 rounded shadow">

<h2 class="text-gray-600">
API Keys
</h2>

<p class="text-3xl font-bold mt-2">
<?= $total_api ?>
</p>

</div>

</div>


<div class="mt-10 bg-white p-6 rounded shadow">

<h2 class="text-xl font-semibold mb-4">
Recent Students
</h2>

<table class="w-full border">

<thead>

<tr class="bg-gray-200">

<th class="p-2">Name</th>
<th>Course</th>
<th>Grade</th>
<th>Registration</th>

</tr>

</thead>

<tbody>

<?php

$stmt = $pdo->query("
SELECT students.name,students.grade,students.registration_number,
courses.name AS course
FROM students
LEFT JOIN courses ON courses.id=students.course_id
ORDER BY students.id DESC
LIMIT 5
");

while($row = $stmt->fetch(PDO::FETCH_ASSOC)):

?>

<tr class="border-t">

<td class="p-2"><?= $row['name'] ?></td>
<td><?= $row['course'] ?></td>
<td><?= $row['grade'] ?></td>
<td><?= $row['registration_number'] ?></td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</div>

<?php include "partials/footer.php"; ?>