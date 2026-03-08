<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

$stmt = $pdo->query("SELECT * FROM api_keys ORDER BY id DESC");

?>

<div class="flex-1 flex flex-col">

<header class="bg-white shadow px-6 py-4 flex justify-between items-center">

<h1 class="text-lg font-semibold">
API Keys
</h1>

<a href="add.php"
class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">

Generate API Key

</a>

</header>


<!-- IMPORTANT flex-1 here -->

<main class="p-6 flex-1">

<div class="bg-white shadow rounded-lg overflow-hidden">

<table class="w-full text-sm">

<thead class="bg-gray-100 text-gray-600">

<tr>
<th class="p-3 text-left">Name</th>
<th class="p-3 text-left">API Key</th>
<th class="p-3 text-left">Hits</th>
<th class="p-3 text-left">Status</th>
<th class="p-3 text-left">Actions</th>
</tr>

</thead>

<tbody>

<?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>

<tr class="border-t hover:bg-gray-50">

<td class="p-3">
<?= htmlspecialchars($row['name']) ?>
</td>

<td class="p-3 font-mono text-xs">
<?= substr($row['api_key'],0,18) ?>...
</td>

<td class="p-3">
<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">
<?= $row['hit_count'] ?>
</span>
</td>

<td class="p-3">
<?= $row['status'] ?>
</td>

<td class="p-3 flex gap-2">

<a href="delete.php?id=<?= $row['id'] ?>"
class="bg-red-500 text-white px-3 py-1 rounded text-xs">

Delete

</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</main>