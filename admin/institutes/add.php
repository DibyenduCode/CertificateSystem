<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

$errors = [];

if($_SERVER['REQUEST_METHOD']=="POST")
{

$name = trim($_POST['name']);

if(!$name){
$errors[]="Institute name is required.";
}

if(empty($errors)){

$stmt = $pdo->prepare("INSERT INTO institutes (name) VALUES (?)");
$stmt->execute([$name]);

header("Location: list.php");
exit;

}

}

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

?>

<div class="flex-1 flex flex-col">

<header class="bg-white shadow px-6 py-4">
<h1 class="text-lg font-semibold">
Add Institute
</h1>
</header>

<main class="p-6">

<?php if($errors): ?>

<div class="bg-red-100 text-red-700 p-4 rounded mb-4">

<?php foreach($errors as $e): ?>
<p><?= $e ?></p>
<?php endforeach; ?>

</div>

<?php endif; ?>

<form method="POST" class="bg-white shadow rounded-lg p-8 max-w-lg">

<label class="block text-sm font-medium mb-1">
Institute Name
</label>

<input
type="text"
name="name"
required
class="w-full border rounded px-3 py-2 mb-6">

<button
class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
Save Institute
</button>

</form>

</main>

</div>