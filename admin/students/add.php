<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$errors = [];

if($_SERVER['REQUEST_METHOD']=="POST")
{

$name = trim($_POST['name']);
$gender = $_POST['gender'];
$course = $_POST['course'];
$mentor = $_POST['mentor'];
$dob = $_POST['dob'];
$start = $_POST['start_date'];
$end = $_POST['end_date'];
$grade = $_POST['grade'];

if(!$name){
$errors[]="Student name is required.";
}

if(empty($errors)){

$registration = generateRegistrationNumber($pdo);
$certificate = generateCertificateNumber($pdo);

$stmt = $pdo->prepare("
INSERT INTO students
(name,gender,registration_number,certificate_number,course_id,mentor_id,dob,start_date,end_date,grade)
VALUES (?,?,?,?,?,?,?,?,?,?)
");

$stmt->execute([
$name,$gender,$registration,$certificate,$course,$mentor,$dob,$start,$end,$grade
]);

header("Location: list.php");
exit;

}

}

$courses = $pdo->query("SELECT * FROM courses")->fetchAll();
$mentors = $pdo->query("SELECT * FROM mentors")->fetchAll();

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

?>

<div class="flex-1 flex flex-col">

<header class="bg-white shadow px-6 py-4">
<h1 class="text-lg font-semibold">Add Student</h1>
</header>

<main class="p-6">

<?php if($errors): ?>

<div class="bg-red-100 text-red-700 p-4 rounded mb-4">

<?php foreach($errors as $e): ?>
<p><?= $e ?></p>
<?php endforeach; ?>

</div>

<?php endif; ?>


<form method="POST" class="bg-white shadow rounded-lg p-8 max-w-4xl">

<h2 class="text-xl font-semibold mb-6">Student Information</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<div>
<label class="block text-sm mb-1">Student Name</label>
<input type="text" name="name" required class="w-full border rounded px-3 py-2">
</div>

<div>
<label class="block text-sm mb-1">Gender</label>
<select name="gender" class="w-full border rounded px-3 py-2">
<option value="Male">Male</option>
<option value="Female">Female</option>
</select>
</div>

<div>
<label class="block text-sm mb-1">Course</label>
<select name="course" class="w-full border rounded px-3 py-2">

<?php foreach($courses as $c): ?>

<option value="<?= $c['id'] ?>">
<?= $c['name'] ?>
</option>

<?php endforeach; ?>

</select>
</div>

<div>
<label class="block text-sm mb-1">Mentor</label>
<select name="mentor" class="w-full border rounded px-3 py-2">

<?php foreach($mentors as $m): ?>

<option value="<?= $m['id'] ?>">
<?= $m['name'] ?>
</option>

<?php endforeach; ?>

</select>
</div>

</div>


<h2 class="text-xl font-semibold mt-10 mb-6">
Course Duration
</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

<div>
<label class="block text-sm mb-1">Date of Birth</label>
<input type="date" name="dob" class="w-full border rounded px-3 py-2">
</div>

<div>
<label class="block text-sm mb-1">Start Date</label>
<input type="date" name="start_date" class="w-full border rounded px-3 py-2">
</div>

<div>
<label class="block text-sm mb-1">End Date</label>
<input type="date" name="end_date" class="w-full border rounded px-3 py-2">
</div>

</div>


<div class="mt-6 max-w-sm">

<label class="block text-sm mb-1">Grade</label>

<input type="text" name="grade" placeholder="Example: A"
class="w-full border rounded px-3 py-2">

</div>


<div class="mt-8">

<button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
Save Student
</button>

</div>

</form>

</main>