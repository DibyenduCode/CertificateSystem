<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id=?");
$stmt->execute([$id]);
$course = $stmt->fetch();

if(!$course){
die("Course not found");
}

if($_SERVER['REQUEST_METHOD']=="POST")
{

$stmt = $pdo->prepare("UPDATE courses SET name=? WHERE id=?");

$stmt->execute([$_POST['name'],$id]);

header("Location: list.php");
exit;

}

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

?>

<div class="flex-1 flex flex-col">

<header class="bg-white shadow px-6 py-4">

<h1 class="text-lg font-semibold">
Edit Course
</h1>

</header>

<main class="p-6">

<form method="POST" class="bg-white shadow rounded-lg p-8 max-w-lg">

<label class="block text-sm font-medium mb-1">
Course Name
</label>

<input
type="text"
name="name"
value="<?= htmlspecialchars($course['name']) ?>"
class="w-full border rounded px-3 py-2 mb-6">

<button
class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">

Update Course

</button>

</form>

</main>