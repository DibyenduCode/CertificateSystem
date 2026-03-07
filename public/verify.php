<?php

require_once "../config/database.php";

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

?>

<!DOCTYPE html>

<html>

<head>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-10">

<?php if($data): ?>

<div class="border p-6 w-96">

<h2 class="text-xl mb-4 text-green-600">
Certificate VERIFIED
</h2>

<p>Name: <?= $data['name'] ?></p>

<p>Course: <?= $data['course'] ?></p>

<p>Mentor: <?= $data['mentor'] ?></p>

<p>Grade: <?= $data['grade'] ?></p>

<a href="download.php?reg=<?= $data['registration_number'] ?>"
class="bg-blue-600 text-white px-4 py-2 mt-4 inline-block">

Download Certificate

</a>

</div>

<?php else: ?>

<h2 class="text-red-600 text-xl">
Certificate Not Found
</h2>

<?php endif; ?>

</body>

</html>