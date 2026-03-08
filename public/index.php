<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Certificate Verification</title>

<script src="https://cdn.tailwindcss.com"></script>

<style>

/* animated gradient background */

body{
background: linear-gradient(-45deg,#2563eb,#3b82f6,#1e40af,#2563eb);
background-size:400% 400%;
animation:gradientMove 12s ease infinite;
}

@keyframes gradientMove{
0%{background-position:0% 50%}
50%{background-position:100% 50%}
100%{background-position:0% 50%}
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
animation:fadeIn 0.8s ease;
}

/* logo animation */

.logo{
animation:pulse 3s infinite;
}

/* button animation */

.verify-btn:hover{
transform:scale(1.05);
box-shadow:0 10px 20px rgba(0,0,0,0.2);
}

</style>

</head>

<body class="min-h-screen flex items-center justify-center px-4">

<div class="card bg-white shadow-2xl rounded-xl p-10 w-full max-w-md text-center">

<!-- LOGO -->

<div class="flex justify-center mb-6">

<img src="../assets/logo.png" class="h-16 logo">

</div>


<!-- TITLE -->

<h1 class="text-2xl font-bold text-gray-800 mb-6">

Certificate Verification

</h1>


<!-- FORM -->

<form action="verify.php" method="POST" class="space-y-4">

<input
type="text"
name="registration_number"
placeholder="Registration Number"
required
class="w-full border rounded px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none">

<input
type="date"
name="dob"
required
class="w-full border rounded px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none">

<button
type="submit"
class="verify-btn w-full bg-blue-600 text-white py-3 rounded-lg transition hover:bg-blue-700">

Verify Certificate

</button>

</form>

</div>

</body>

</html>