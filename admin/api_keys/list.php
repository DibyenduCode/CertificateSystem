<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

include __DIR__ . "/../partials/header.php";

?>

<div class="flex">

<?php include __DIR__ . "/../partials/sidebar.php"; ?>

<div class="flex-1 p-10">

<div class="flex justify-between mb-6">

<h1 class="text-2xl font-bold">API Keys</h1>

<a href="add.php"
class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">

Generate API Key

</a>

</div>


<div class="bg-white shadow rounded overflow-x-auto">

<table class="w-full text-sm">

<thead class="bg-gray-200">

<tr>

<th class="p-3 text-left">Name</th>
<th class="text-left">API Key</th>
<th>Status</th>
<th>Hits</th>
<th>Created</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php

$stmt = $pdo->query("SELECT * FROM api_keys ORDER BY id DESC");

while($row = $stmt->fetch(PDO::FETCH_ASSOC)):

?>

<tr class="border-t hover:bg-gray-50">

<td class="p-3">

<?= htmlspecialchars($row['name']) ?>

</td>

<td class="text-xs break-all">

<?= htmlspecialchars($row['api_key']) ?>

</td>

<td>

<?php if($row['status'] == "active"): ?>

<span class="text-green-600 font-semibold">

Active

</span>

<?php else: ?>

<span class="text-red-600 font-semibold">

Inactive

</span>

<?php endif; ?>

</td>

<td>

<?= $row['hit_count'] ?>

</td>

<td>

<?= $row['created_at'] ?>

</td>

<td class="space-x-3">

<a href="toggle.php?id=<?= $row['id'] ?>"
class="text-yellow-600 hover:underline">

Toggle

</a>

<a href="delete.php?id=<?= $row['id'] ?>"
class="text-red-600 hover:underline"
onclick="return confirm('Delete this API Key?')">

Delete

</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>