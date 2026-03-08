<?php

require_once "../config/database.php";

if($_SERVER['REQUEST_METHOD']=="POST")
{
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username=?");
    $stmt->execute([$username]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if($admin && password_verify($password,$admin['password']))
    {
        $_SESSION['admin_id'] = $admin['id'];

        header("Location: dashboard.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<title>Admin Login</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gradient-to-br from-blue-600 to-indigo-700 min-h-screen flex items-center justify-center">

<div class="bg-white shadow-xl rounded-xl w-96 p-8">

<!-- Logo -->

<div class="flex flex-col items-center mb-6">

<img src="../assets/logo.png" class="w-16 h-16 mb-2">

<h1 class="text-xl font-bold text-gray-700">
Certificate System
</h1>

<p class="text-gray-400 text-sm">
Admin Panel
</p>

</div>

<form method="POST">

<h2 class="text-lg font-semibold text-gray-700 mb-4 text-center">
Admin Login
</h2>

<input
name="username"
placeholder="Username"
class="w-full border border-gray-300 rounded px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">

<input
type="password"
name="password"
placeholder="Password"
class="w-full border border-gray-300 rounded px-3 py-2 mb-5 focus:outline-none focus:ring-2 focus:ring-blue-500">

<button class="bg-blue-600 hover:bg-blue-700 transition text-white px-4 py-2 w-full rounded font-semibold">
Login
</button>

</form>

</div>

</body>
</html>