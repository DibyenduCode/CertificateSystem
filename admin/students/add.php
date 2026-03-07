<?php

require_once "../auth_check.php";
require_once "../../config/database.php";
require_once "../../config/functions.php";

if($_SERVER['REQUEST_METHOD']=="POST")
{

$name = $_POST['name'];
$gender = $_POST['gender'];
$course = $_POST['course'];
$mentor = $_POST['mentor'];
$dob = $_POST['dob'];
$start = $_POST['start_date'];
$end = $_POST['end_date'];
$grade = $_POST['grade'];

$registration = generateRegistrationNumber($pdo);
$certificate = generateCertificateNumber($pdo);

$stmt = $pdo->prepare("
INSERT INTO students
(name,gender,registration_number,certificate_number,
course_id,mentor_id,dob,start_date,end_date,grade)
VALUES (?,?,?,?,?,?,?,?,?,?)
");

$stmt->execute([
$name,
$gender,
$registration,
$certificate,
$course,
$mentor,
$dob,
$start,
$end,
$grade
]);

header("Location: list.php");
exit;

}

$courses = $pdo->query("SELECT * FROM courses")->fetchAll();
$mentors = $pdo->query("SELECT * FROM mentors")->fetchAll();

include "../partials/header.php";

?>

<div class="flex">

<?php include "../partials/sidebar.php"; ?>

<div class="flex-1 p-10">

<h1 class="text-2xl mb-6">Add Student</h1>

<form method="POST" class="bg-white p-6 shadow rounded w-96">

<input name="name" placeholder="Student Name"
class="w-full border p-2 mb-3">

<select name="gender"
class="w-full border p-2 mb-3">

<option value="Male">Male</option>
<option value="Female">Female</option>

</select>

<select name="course"
class="w-full border p-2 mb-3">

<?php foreach($courses as $c): ?>

<option value="<?= $c['id'] ?>">
<?= $c['name'] ?>
</option>

<?php endforeach; ?>

</select>

<select name="mentor"
class="w-full border p-2 mb-3">

<?php foreach($mentors as $m): ?>

<option value="<?= $m['id'] ?>">
<?= $m['name'] ?>
</option>

<?php endforeach; ?>

</select>

<input type="date" name="dob"
class="w-full border p-2 mb-3">

<input type="date" name="start_date"
class="w-full border p-2 mb-3">

<input type="date" name="end_date"
class="w-full border p-2 mb-3">

<input name="grade" placeholder="Grade"
class="w-full border p-2 mb-3">

<button
class="bg-green-600 text-white px-4 py-2 w-full">

Save Student

</button>

</form>

</div>

</div>

<?php include "../partials/footer.php"; ?>