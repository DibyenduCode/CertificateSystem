<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

/* get student id safely */

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$id){
    die("Invalid Student ID");
}


/* update student */

if($_SERVER['REQUEST_METHOD']=="POST")
{

$stmt = $pdo->prepare("
UPDATE students
SET name=?, gender=?, course_id=?, mentor_id=?, dob=?, start_date=?, end_date=?, grade=?
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


/* fetch student */

$stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$student){
    die("Student not found");
}


/* fetch courses */

$courses = $pdo->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll();


/* fetch mentors */

$mentors = $pdo->query("SELECT * FROM mentors ORDER BY name ASC")->fetchAll();


include __DIR__ . "/../partials/header.php";

?>

<div class="flex">

<?php include __DIR__ . "/../partials/sidebar.php"; ?>

<div class="flex-1 p-10">

<h1 class="text-2xl font-bold mb-6">Edit Student</h1>


<form method="POST" class="bg-white p-6 shadow rounded max-w-lg">


<!-- NAME -->

<input
name="name"
value="<?= htmlspecialchars($student['name']) ?>"
placeholder="Student Name"
class="w-full border p-2 mb-3">


<!-- GENDER -->

<select name="gender" class="w-full border p-2 mb-3">

<option value="Male" <?= $student['gender']=="Male"?'selected':'' ?>>
Male
</option>

<option value="Female" <?= $student['gender']=="Female"?'selected':'' ?>>
Female
</option>

</select>


<!-- COURSE -->

<select name="course" class="w-full border p-2 mb-3">

<?php foreach($courses as $course): ?>

<option value="<?= $course['id'] ?>"
<?= $student['course_id']==$course['id'] ? 'selected' : '' ?>>

<?= htmlspecialchars($course['name']) ?>

</option>

<?php endforeach; ?>

</select>


<!-- MENTOR -->

<select name="mentor" class="w-full border p-2 mb-3">

<?php foreach($mentors as $mentor): ?>

<option value="<?= $mentor['id'] ?>"
<?= $student['mentor_id']==$mentor['id'] ? 'selected' : '' ?>>

<?= htmlspecialchars($mentor['name']) ?>

</option>

<?php endforeach; ?>

</select>


<!-- DOB -->

<input
type="date"
name="dob"
value="<?= $student['dob'] ?>"
class="w-full border p-2 mb-3">


<!-- START DATE -->

<input
type="date"
name="start_date"
value="<?= $student['start_date'] ?>"
class="w-full border p-2 mb-3">


<!-- END DATE -->

<input
type="date"
name="end_date"
value="<?= $student['end_date'] ?>"
class="w-full border p-2 mb-3">


<!-- GRADE -->

<input
name="grade"
value="<?= htmlspecialchars($student['grade']) ?>"
placeholder="Grade"
class="w-full border p-2 mb-4">


<button
class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 w-full rounded">

Update Student

</button>

</form>

</div>

</div>

<?php include __DIR__ . "/../partials/footer.php"; ?>