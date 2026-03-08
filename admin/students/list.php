<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

/* SEARCH */

$search = $_GET['search'] ?? '';

$where = "";

$params = [];

if($search){

$where = "WHERE students.name LIKE ? OR students.registration_number LIKE ?";

$params[] = "%$search%";
$params[] = "%$search%";

}


/* PAGINATION */

$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$offset = ($page - 1) * $limit;


/* TOTAL COUNT */

$count_sql = "
SELECT COUNT(*)
FROM students
LEFT JOIN courses ON courses.id=students.course_id
LEFT JOIN mentors ON mentors.id=students.mentor_id
$where
";

$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);

$total_rows = $stmt->fetchColumn();

$total_pages = ceil($total_rows / $limit);


/* FETCH STUDENTS */

$sql = "
SELECT students.*,courses.name AS course,mentors.name AS mentor
FROM students
LEFT JOIN courses ON courses.id=students.course_id
LEFT JOIN mentors ON mentors.id=students.mentor_id
$where
ORDER BY students.id DESC
LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

?>

<div class="flex-1 flex flex-col">

<header class="bg-white shadow px-6 py-4 flex justify-between items-center">

<h1 class="text-lg font-semibold">
Students
</h1>

<a href="add.php"
class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">

Add Student

</a>

</header>


<main class="p-6 space-y-4">

<!-- SEARCH BAR -->

<form method="GET" class="flex gap-2 max-w-md">

<input
type="text"
name="search"
value="<?= htmlspecialchars($search) ?>"
placeholder="Search student..."
class="flex-1 border rounded px-3 py-2">

<button
class="bg-blue-600 text-white px-4 py-2 rounded">

Search

</button>

</form>


<!-- STUDENTS TABLE -->

<div class="bg-white shadow rounded-lg overflow-hidden">

<table class="w-full text-sm">

<thead class="bg-gray-100 text-gray-600">

<tr>

<th class="p-3 text-left">Student</th>
<th class="p-3 text-left">Course</th>
<th class="p-3 text-left">Mentor</th>
<th class="p-3 text-left">Grade</th>
<th class="p-3 text-left">Registration</th>
<th class="p-3 text-left">Actions</th>

</tr>

</thead>

<tbody>

<?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>

<tr class="border-t hover:bg-gray-50">

<td class="p-3">

<div class="font-medium">

<?= htmlspecialchars($row['name']) ?>

</div>

</td>

<td class="p-3">

<?= htmlspecialchars($row['course']) ?>

</td>

<td class="p-3">

<?= htmlspecialchars($row['mentor']) ?>

</td>

<td class="p-3">

<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">

<?= $row['grade'] ?>

</span>

</td>

<td class="p-3">

<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-semibold">

<?= $row['registration_number'] ?>

</span>

</td>


<td class="p-3 flex gap-2">

<a href="edit.php?id=<?= $row['id'] ?>"
class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">

Edit

</a>

<a href="delete.php?id=<?= $row['id'] ?>"
onclick="return confirm('Delete student?')"
class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600">

Delete

</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>


<!-- PAGINATION -->

<div class="flex gap-2">

<?php for($i=1;$i<=$total_pages;$i++): ?>

<a
href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
class="px-3 py-1 border rounded <?= $i==$page?'bg-blue-600 text-white':'' ?>">

<?= $i ?>

</a>

<?php endfor; ?>

</div>


</main>