<!DOCTYPE html>

<html>

<head>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">

<form action="verify.php" method="POST"
class="bg-white p-8 shadow rounded w-96">

<h2 class="text-2xl mb-6">Certificate Verification</h2>

<input name="registration_number"
placeholder="Registration Number"
class="w-full border p-2 mb-4">

<input type="date" name="dob"
class="w-full border p-2 mb-4">

<button class="bg-green-600 text-white w-full p-2">
Verify
</button>

</form>

</body>

</html>