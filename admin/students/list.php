<?php

require_once "../auth_check.php";
require_once "../../config/database.php";

include __DIR__ . "/../partials/header.php";

?>

<div class="flex">

<?php include __DIR__ . "/../partials/sidebar.php"; ?>

<div class="flex-1 p-10">

<div class="flex justify-between mb-6">

<h1 class="text-2xl font-bold">Students</h1>

<a href="add.php"
class="bg-green-600 text-white px-4 py-2 rounded">

Add Student

</a>

</div>

<table class="w-full bg-white shadow rounded">

<thead class="bg-gray-200">

<tr>

<th class="p-3">Name</th>
<th>Course</th>
<th>Mentor</th>
<th>Grade</th>
<th>Registration</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php

$stmt = $pdo->query("
SELECT students.*,courses.name AS course,mentors.name AS mentor
FROM students
LEFT JOIN courses ON courses.id=students.course_id
LEFT JOIN mentors ON mentors.id=students.mentor_id
ORDER BY students.id DESC
");

while($row = $stmt->fetch(PDO::FETCH_ASSOC)):

?>

<tr class="border-t">

<td class="p-3"><?= $row['name'] ?></td>

<td><?= $row['course'] ?></td>

<td><?= $row['mentor'] ?></td>

<td><?= $row['grade'] ?></td>

<td><?= $row['registration_number'] ?></td>

<td>

<a href="edit.php?id=<?= $row['id'] ?>"
class="text-blue-600 mr-3">Edit</a>

<a href="delete.php?id=<?= $row['id'] ?>"
class="text-red-600"
onclick="return confirm('Delete student?')">

Delete

</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>