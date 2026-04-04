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
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Certificate Verification</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-r from-blue-500 to-blue-600 min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 sm:p-8">

<!-- LOGO -->
<div class="flex justify-center mb-4">
<img src="logo.png" class="h-10 sm:h-12 object-contain" alt="Logo">
</div>

<h1 class="text-xl sm:text-2xl font-bold text-center text-gray-800 mb-6">
Certificate Verification
</h1>

<!-- FORM -->
<?php if(!$data && $_SERVER['REQUEST_METHOD'] != "POST" && !$cert): ?>

<form method="POST" class="space-y-4">

<input
type="text"
name="registration_number"
class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm sm:text-base"
placeholder="Registration Number"
required>

<input
type="date"
name="dob"
class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm sm:text-base"
required>

<button
class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition text-sm sm:text-base">
Verify Certificate
</button>

</form>

<?php endif; ?>


<!-- SUCCESS -->
<?php if($data): ?>

<div class="space-y-2 text-gray-700 text-sm sm:text-base">

<p><b>Name:</b> <?= $data['name'] ?></p>
<p><b>Course:</b> <?= $data['course'] ?></p>
<p><b>Mentor:</b> <?= $data['mentor'] ?></p>
<p><b>Grade:</b> <?= $data['grade'] ?></p>
<p><b>Certificate No:</b> <?= $data['certificate_number'] ?></p>

<div class="flex flex-col sm:flex-row gap-3 mt-6">

<a
href="download.php?cert=<?= $data['certificate_number'] ?>"
target="_blank"
class="flex-1 text-center bg-green-500 hover:bg-green-600 text-white py-2.5 rounded-lg font-semibold transition">
Download
</a>

<a
href="index.php"
class="flex-1 text-center bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-lg font-semibold transition">
Back
</a>

</div>

</div>

<?php elseif($_SERVER['REQUEST_METHOD']=="POST" || $cert): ?>

<!-- ERROR -->
<p class="text-red-500 text-center font-semibold mt-4 text-sm sm:text-base">
Certificate Not Found
</p>

<div class="mt-4 text-center">
<a href="index.php"
class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm sm:text-base">
Back
</a>
</div>

<?php endif; ?>

</div>

</body>
</html>