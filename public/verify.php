<?php

require_once "../config/database.php";

$data = null;

$cert = $_GET['cert'] ?? null;

if($cert)
{

$stmt = $pdo->prepare("
SELECT students.*,courses.name AS course,mentors.name AS mentor
FROM students
LEFT JOIN courses ON courses.id = students.course_id
LEFT JOIN mentors ON mentors.id = students.mentor_id
WHERE certificate_number=?
");

$stmt->execute([$cert]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

}
elseif($_SERVER['REQUEST_METHOD']=="POST")
{

$reg = $_POST['registration_number'];
$dob = $_POST['dob'];

$stmt = $pdo->prepare("
SELECT students.*,courses.name AS course,mentors.name AS mentor
FROM students
LEFT JOIN courses ON courses.id = students.course_id
LEFT JOIN mentors ON mentors.id = students.mentor_id
WHERE registration_number=? AND dob=?
");

$stmt->execute([$reg,$dob]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

}

?>

<!DOCTYPE html>
<html>

<head>

<title>Certificate Verification</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

<div class="max-w-xl mx-auto mt-16 bg-white p-8 shadow rounded">

<h1 class="text-2xl font-bold mb-6 text-center">

Certificate Verification

</h1>

<form method="POST" class="space-y-4">

<div>

<label class="block text-sm mb-1">
Registration Number
</label>

<input
type="text"
name="registration_number"
class="w-full border p-2 rounded"
required>

</div>

<div>

<label class="block text-sm mb-1">
Date of Birth
</label>

<input
type="date"
name="dob"
class="w-full border p-2 rounded"
required>

</div>

<button
class="w-full bg-blue-600 text-white py-2 rounded">

Verify Certificate

</button>

</form>


<?php if($data): ?>

<div class="mt-8 border-t pt-6">

<p><b>Name:</b> <?= $data['name'] ?></p>

<p><b>Course:</b> <?= $data['course'] ?></p>

<p><b>Mentor:</b> <?= $data['mentor'] ?></p>

<p><b>Grade:</b> <?= $data['grade'] ?></p>
<p><b>Certificate No:</b> <?= $data['certificate_number'] ?></p>

<div class="mt-4">

<a
href="download.php?cert=<?= $data['certificate_number'] ?>"
target="_blank"
class="bg-green-600 text-white px-4 py-2 rounded">

Download Certificate

</a>

</div>

</div>

<?php elseif($_SERVER['REQUEST_METHOD']=="POST" || $cert): ?>

<p class="text-red-600 mt-6">
Certificate Not Found
</p>

<?php endif; ?>

</div>

</body>

</html>