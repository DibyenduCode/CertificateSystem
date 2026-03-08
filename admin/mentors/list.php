<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

$stmt = $pdo->query("SELECT * FROM mentors ORDER BY id DESC");

?>

<div class="flex-1 flex flex-col">

<header class="bg-white shadow px-6 py-4 flex justify-between items-center">

<h1 class="text-lg font-semibold">
Mentors
</h1>

<a href="add.php"
class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">

Add Mentor

</a>

</header>


<main class="p-6">

<div class="bg-white shadow rounded-lg overflow-hidden">

<table class="w-full text-sm">

<thead class="bg-gray-100 text-gray-600">

<tr>

<th class="p-3 text-left">Mentor Name</th>
<th class="p-3 text-left">Actions</th>

</tr>

</thead>

<tbody>

<?php while($mentor = $stmt->fetch(PDO::FETCH_ASSOC)): ?>

<tr class="border-t hover:bg-gray-50">

<td class="p-3 font-medium">

<?= htmlspecialchars($mentor['name']) ?>

</td>

<td class="p-3 flex gap-2">

<a href="edit.php?id=<?= $mentor['id'] ?>"
class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">

Edit

</a>

<a href="delete.php?id=<?= $mentor['id'] ?>"
onclick="return confirm('Delete mentor?')"
class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600">

Delete

</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</main>