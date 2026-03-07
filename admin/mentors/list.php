<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

include __DIR__ . "/../partials/header.php";

?>

<div class="flex">

<?php include __DIR__ . "/../partials/sidebar.php"; ?>

<div class="flex-1 p-10">

<div class="flex justify-between mb-6">

<h1 class="text-2xl font-bold">Mentors</h1>

<a href="add.php"
class="bg-green-600 text-white px-4 py-2 rounded">
Add Mentor
</a>

</div>

<table class="w-full bg-white shadow rounded">

<thead class="bg-gray-200">

<tr>
<th class="p-3">Mentor Name</th>
<th>Actions</th>
</tr>

</thead>

<tbody>

<?php

$stmt = $pdo->query("SELECT * FROM mentors ORDER BY id DESC");

while($mentor = $stmt->fetch(PDO::FETCH_ASSOC)):

?>

<tr class="border-t">

<td class="p-3"><?= $mentor['name'] ?></td>

<td>

<a href="edit.php?id=<?= $mentor['id'] ?>"
class="text-blue-600 mr-3">Edit</a>

<a href="delete.php?id=<?= $mentor['id'] ?>"
class="text-red-600"
onclick="return confirm('Delete mentor?')">
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