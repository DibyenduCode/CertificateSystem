<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/functions.php";

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$student){
die("Student not found");
}

$errors = [];

if($_SERVER['REQUEST_METHOD']=="POST")
{

$name = trim($_POST['name']);
$father = trim($_POST['father_name']);
$gender = $_POST['gender'];

$course = $_POST['course'];
$mentor = $_POST['mentor'];

$organization = $_POST['organization'];
$institute = $_POST['institute'];

$dob = $_POST['dob'];
$start = $_POST['start_date'];
$end = $_POST['end_date'];
$issue = $_POST['issue_date'];

$grade = $_POST['grade'];

if(!$name){
$errors[]="Student name required";
}

if(!$father){
$errors[]="Father name required";
}


/* =========================
   IMAGE UPDATE
========================= */

$photo = $student['student_photo'];

if(isset($_FILES['photo']) && $_FILES['photo']['error']===0){

$allowed = ['jpg','jpeg','png'];

$ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

if(!in_array($ext,$allowed)){
$errors[]="Only JPG/JPEG/PNG allowed";
}

elseif($_FILES['photo']['size']>1024*1024){
$errors[]="Image must be below 1MB";
}

else{

$filename = $student['registration_number'].".jpg";

$destination = __DIR__."/../../uploads/students/".$filename;

/* delete old photo */

if($student['student_photo'] && file_exists(__DIR__."/../../uploads/students/".$student['student_photo'])){
unlink(__DIR__."/../../uploads/students/".$student['student_photo']);
}

/* compress new image */

compressStudentImage(
$_FILES['photo']['tmp_name'],
$destination
);

$photo = $filename;

}

}


/* =========================
   UPDATE STUDENT
========================= */

if(empty($errors))
{

$stmt = $pdo->prepare("
UPDATE students
SET
name=?,
father_name=?,
gender=?,
course_id=?,
mentor_id=?,
organization_id=?,
institute_id=?,
dob=?,
start_date=?,
end_date=?,
issue_date=?,
grade=?,
student_photo=?
WHERE id=?
");

$stmt->execute([
$name,
$father,
$gender,
$course,
$mentor,
$organization,
$institute,
$dob,
$start,
$end,
$issue,
$grade,
$photo,
$id
]);

header("Location: list.php");
exit;

}

}


/* =========================
   DROPDOWN DATA
========================= */

$courses = $pdo->query("SELECT * FROM courses")->fetchAll();
$mentors = $pdo->query("SELECT * FROM mentors")->fetchAll();
$organizations = $pdo->query("SELECT * FROM organizations")->fetchAll();
$institutes = $pdo->query("SELECT * FROM institutes")->fetchAll();

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";

?>

<div class="flex-1 flex flex-col">

<header class="bg-white shadow px-6 py-4">
<h1 class="text-lg font-semibold">Edit Student</h1>
</header>

<main class="p-6">

<?php if($errors): ?>

<div class="bg-red-100 text-red-700 p-4 rounded mb-4">
<?php foreach($errors as $e): ?>
<p><?= $e ?></p>
<?php endforeach; ?>
</div>

<?php endif; ?>


<form method="POST" enctype="multipart/form-data"
class="bg-white shadow rounded-lg p-8 max-w-4xl">

<h2 class="text-xl font-semibold mb-6">Student Information</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<div>
<label class="block text-sm mb-1">Student Name</label>
<input type="text" name="name"
value="<?= htmlspecialchars($student['name']) ?>"
class="w-full border rounded px-3 py-2">
</div>

<div>
<label class="block text-sm mb-1">Father / Mother Name</label>
<input type="text" name="father_name"
value="<?= htmlspecialchars($student['father_name']) ?>"
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
<label class="block text-sm mb-1">Student Photo</label>

<input type="file" name="photo"
class="w-full border rounded px-3 py-2">

<?php if($student['student_photo']): ?>

<img src="/certificate-system/uploads/students/<?= $student['student_photo'] ?>"
style="margin-top:10px;height:80px;border:1px solid #ccc;">

<?php endif; ?>

</div>

<div>
<label class="block text-sm mb-1">Organization</label>
<select name="organization" class="w-full border rounded px-3 py-2">

<?php foreach($organizations as $o): ?>

<option value="<?= $o['id'] ?>"
<?= $student['organization_id']==$o['id']?'selected':'' ?>>

<?= $o['name'] ?>

</option>

<?php endforeach; ?>

</select>
</div>

<div>
<label class="block text-sm mb-1">Institute</label>
<select name="institute" class="w-full border rounded px-3 py-2">

<?php foreach($institutes as $i): ?>

<option value="<?= $i['id'] ?>"
<?= $student['institute_id']==$i['id']?'selected':'' ?>>

<?= $i['name'] ?>

</option>

<?php endforeach; ?>

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


<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

<div>
<label class="block text-sm mb-1">Issue Date</label>
<input type="date" name="issue_date"
value="<?= $student['issue_date'] ?>"
class="w-full border rounded px-3 py-2">
</div>

<div>
<label class="block text-sm mb-1">Grade</label>
<input type="text" name="grade"
value="<?= $student['grade'] ?>"
class="w-full border rounded px-3 py-2">
</div>

</div>


<div class="mt-8">

<button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
Update Student
</button>

</div>

</form>

</main>
</div>