<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

if($_SERVER['REQUEST_METHOD']=="POST")
{

$name = $_POST['name'];

$stmt = $pdo->prepare("INSERT INTO courses (name) VALUES (?)");

$stmt->execute([$name]);

header("Location: list.php");
exit;

}

include __DIR__ . "/../partials/header.php";

?>

<div class="flex">

<?php include __DIR__ . "/../partials/sidebar.php"; ?>

<div class="flex-1 p-10">

<h1 class="text-2xl mb-6">Add Course</h1>

<form method="POST" class="bg-white p-6 shadow rounded w-96">

<input name="name"
placeholder="Course Name"
class="w-full border p-2 mb-4">

<button class="bg-green-600 text-white px-4 py-2 w-full">

Save Course

</button>

</form>

</div>

</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>