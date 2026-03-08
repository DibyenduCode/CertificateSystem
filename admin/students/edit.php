<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if(!$student){
die("Student not found");
}

if($_SERVER['REQUEST_METHOD']=="POST")
{

$stmt = $pdo->prepare("
UPDATE students
SET name=?,gender=?,course_id=?,mentor_id=?,dob=?,start_date=?,end_date=?,grade=?
WHERE id=?
");

$stmt->execute([
$_POST['name'],
$_POST['gender'],
$_POST['course'],
$_POST['mentor'],
$_POST['dob'],
$_POST['start_date'],
$_POST['end_date'],
$_POST['grade'],
$id
]);

header("Location: list.php");
exit;

}

$courses = $pdo->query("SELECT * FROM courses")->fetchAll();
$mentors = $pdo->query("SELECT * FROM mentors")->fetchAll();

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

?>

<div class="flex-1 flex flex-col">

<header class="bg-white shadow px-6 py-4">
<h1 class="text-lg font-semibold">Edit Student</h1>
</header>

<main class="p-6">

<form method="POST" class="bg-white shadow rounded-lg p-8 max-w-4xl">

<h2 class="text-xl font-semibold mb-6">Student Information</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<div>
<label class="block text-sm mb-1">Student Name</label>
<input type="text" name="name"
value="<?= htmlspecialchars($student['name']) ?>"
class="w-full border rounded px-3 py-2">
</div>

<div>
<label class="block text-sm mb-1">Gender</label>
<select name="gender" class="w-full border rounded px-3 py-2">

<option value="Male"
<?= $student['gender']=="Male"?'selected':'' ?>>Male</option>

<option value="Female"
<?= $student['gender']=="Female"?'selected':'' ?>>Female</option>

</select>
</div>

<div>
<label class="block text-sm mb-1">Course</label>
<select name="course" class="w-full border rounded px-3 py-2">

<?php foreach($courses as $c): ?>

<option value="<?= $c['id'] ?>"
<?= $student['course_id']==$c['id']?'selected':'' ?>>

<?= $c['name'] ?>

</option>

<?php endforeach; ?>

</select>
</div>

<div>
<label class="block text-sm mb-1">Mentor</label>
<select name="mentor" class="w-full border rounded px-3 py-2">

<?php foreach($mentors as $m): ?>

<option value="<?= $m['id'] ?>"
<?= $student['mentor_id']==$m['id']?'selected':'' ?>>

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
<input type="date" name="dob"
value="<?= $student['dob'] ?>"
class="w-full border rounded px-3 py-2">
</div>

<div>
<label class="block text-sm mb-1">Start Date</label>
<input type="date" name="start_date"
value="<?= $student['start_date'] ?>"
class="w-full border rounded px-3 py-2">
</div>

<div>
<label class="block text-sm mb-1">End Date</label>
<input type="date" name="end_date"
value="<?= $student['end_date'] ?>"
class="w-full border rounded px-3 py-2">
</div>

</div>


<div class="mt-6 max-w-sm">

<label class="block text-sm mb-1">Grade</label>

<input type="text" name="grade"
value="<?= $student['grade'] ?>"
class="w-full border rounded px-3 py-2">

</div>


<div class="mt-8">

<button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
Update Student
</button>

</div>

</form>

</main>