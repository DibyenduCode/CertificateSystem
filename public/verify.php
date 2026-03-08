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

<meta charset="UTF-8">
<title>Certificate Verification</title>

<script src="https://cdn.tailwindcss.com"></script>

<style>

/* verified badge animation */

@keyframes pop{
0%{transform:scale(0)}
70%{transform:scale(1.2)}
100%{transform:scale(1)}
}

.verified-badge{
animation:pop .6s ease;
}

/* card animation */

@keyframes fadeIn{
from{
opacity:0;
transform:translateY(30px);
}
to{
opacity:1;
transform:translateY(0);
}
}

.card{
animation:fadeIn .8s ease;
}

/* logo animation */

.logo{
animation:pulse 3s infinite;
}

/* download button */

.download-btn:hover{
transform:scale(1.05);
box-shadow:0 10px 20px rgba(0,0,0,0.2);
}

</style>

</head>

<body class="bg-gradient-to-r from-blue-600 to-blue-500 min-h-screen flex items-center justify-center px-4">

<?php if($data): ?>

<div class="card bg-white shadow-2xl rounded-xl p-10 w-full max-w-xl text-center">

<!-- LOGO -->

<div class="flex justify-center mb-6">

<img src="../assets/logo.png" class="h-16 logo">

</div>

<!-- VERIFIED BADGE -->

<div class="flex justify-center mb-6">

<div class="verified-badge bg-green-100 text-green-700 px-6 py-2 rounded-full text-lg font-bold shadow">

✔ VERIFIED

</div>

</div>

<h2 class="text-2xl font-bold text-gray-800 mb-6">

Certificate Verified Successfully

</h2>


<!-- CERTIFICATE DETAILS -->

<div class="bg-gray-50 border rounded-lg p-6 text-left space-y-2">

<p><strong>Name:</strong> <?= htmlspecialchars($data['name']) ?></p>

<p><strong>Course:</strong> <?= htmlspecialchars($data['course']) ?></p>

<p><strong>Mentor:</strong> <?= htmlspecialchars($data['mentor']) ?></p>

<p><strong>Grade:</strong> <?= htmlspecialchars($data['grade']) ?></p>

<p><strong>Registration Number:</strong> <?= $data['registration_number'] ?></p>

<p><strong>Certificate Number:</strong> <?= $data['certificate_number'] ?></p>

</div>


<!-- DOWNLOAD BUTTON -->

<div class="mt-8">

<a href="download.php?reg=<?= $data['registration_number'] ?>"

class="download-btn bg-blue-600 text-white px-6 py-3 rounded-lg transition">

Download Certificate

</a>

</div>

</div>


<?php else: ?>

<div class="bg-white shadow-xl rounded-lg p-10 text-center">

<h2 class="text-2xl font-bold text-red-600">

Certificate Not Found

</h2>

<p class="text-gray-500 mt-3">

Please check the registration number and date of birth.

</p>

</div>

<?php endif; ?>

</body>

</html>