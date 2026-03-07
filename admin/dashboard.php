<?php

require_once "auth_check.php";

?>

<!DOCTYPE html>

<html>

<head>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

<div class="flex">

<div class="w-64 bg-gray-900 text-white min-h-screen p-4">

<h2 class="text-xl mb-6">Admin Panel</h2>

<ul>

<li class="mb-3">
<a href="students/list.php">Students</a>
</li>

<li class="mb-3">
<a href="courses/list.php">Courses</a>
</li>

<li class="mb-3">
<a href="mentors/list.php">Mentors</a>
</li>

<li class="mb-3">
<a href="api_keys/list.php">API Keys</a>
</li>

<li>
<a href="logout.php">Logout</a>
</li>

</ul>

</div>

<div class="flex-1 p-8">

<h1 class="text-3xl">Dashboard</h1>

<p class="mt-4">Certificate Verification System</p>

</div>

</div>

</body>
</html>