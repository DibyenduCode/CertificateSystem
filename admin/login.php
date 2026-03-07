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

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">

<form method="POST" class="bg-white p-8 rounded shadow w-96">

<h2 class="text-2xl mb-6">Admin Login</h2>

<input name="username" placeholder="Username"
class="w-full border p-2 mb-4">

<input type="password" name="password"
placeholder="Password"
class="w-full border p-2 mb-4">

<button class="bg-blue-600 text-white px-4 py-2 w-full">
Login
</button>

</form>

</body>
</html>