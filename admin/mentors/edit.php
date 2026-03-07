<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

$id = $_GET['id'];

if($_SERVER['REQUEST_METHOD']=="POST")
{

$name = $_POST['name'];

$stmt = $pdo->prepare("UPDATE mentors SET name=? WHERE id=?");

$stmt->execute([$name,$id]);

header("Location: list.php");
exit;

}

$stmt = $pdo->prepare("SELECT * FROM mentors WHERE id=?");
$stmt->execute([$id]);

$mentor = $stmt->fetch();

include __DIR__ . "/../partials/header.php";

?>

<div class="flex">

<?php include __DIR__ . "/../partials/sidebar.php"; ?>

<div class="flex-1 p-10">

<h1 class="text-2xl mb-6">Edit Mentor</h1>

<form method="POST" class="bg-white p-6 shadow rounded w-96">

<input name="name"
value="<?= $mentor['name'] ?>"
class="w-full border p-2 mb-4">

<button class="bg-blue-600 text-white px-4 py-2 w-full">
Update Mentor
</button>

</form>

</div>

</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>