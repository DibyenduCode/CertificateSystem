<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

include __DIR__ . "/../partials/header.php";

?>

<div class="flex">

<?php include __DIR__ . "/../partials/sidebar.php"; ?>

<div class="flex-1 p-10">

<div class="flex justify-between mb-6">

<h1 class="text-2xl font-bold">Courses</h1>

<a href="add.php"
class="bg-green-600 text-white px-4 py-2 rounded">

Add Course

</a>

</div>

<table class="w-full bg-white shadow rounded">

<thead class="bg-gray-200">

<tr>

<th class="p-3">Course Name</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php

$stmt = $pdo->query("SELECT * FROM courses ORDER BY id DESC");

while($course = $stmt->fetch(PDO::FETCH_ASSOC)):

?>

<tr class="border-t">

<td class="p-3"><?= $course['name'] ?></td>

<td>

<a href="edit.php?id=<?= $course['id'] ?>"
class="text-blue-600 mr-3">Edit</a>

<a href="delete.php?id=<?= $course['id'] ?>"
class="text-red-600"
onclick="return confirm('Delete course?')">

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