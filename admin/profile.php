<?php

require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/../config/database.php";

$admin_id = $_SESSION['admin_id'];

/* get admin data */

$stmt = $pdo->prepare("SELECT * FROM admins WHERE id=?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$message = "";

/* update profile */

if($_SERVER['REQUEST_METHOD']=="POST")
{

$username = $_POST['username'];
$password = $_POST['password'];

if(!empty($password)){

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
UPDATE admins
SET username=?, password=?
WHERE id=?
");

$stmt->execute([$username,$hashed,$admin_id]);

}else{

$stmt = $pdo->prepare("
UPDATE admins
SET username=?
WHERE id=?
");

$stmt->execute([$username,$admin_id]);

}

$message = "Profile updated successfully.";

}

include __DIR__ . "/partials/header.php";
?>

<?php include __DIR__ . "/partials/sidebar.php"; ?>

<div class="flex-1 p-10">

<h1 class="text-2xl font-bold mb-6">Admin Profile</h1>

<?php if(!empty($message)): ?>

<div class="bg-green-100 text-green-700 p-3 rounded mb-4">
<?= $message ?>
</div>

<?php endif; ?>

<form method="POST" class="bg-white shadow rounded p-6 max-w-md space-y-4">

<label class="block text-sm font-semibold">
Username
</label>

<input
type="text"
name="username"
value="<?= htmlspecialchars($admin['username']) ?>"
class="w-full border rounded px-4 py-2">

<label class="block text-sm font-semibold">
New Password (leave empty if unchanged)
</label>

<input
type="password"
name="password"
class="w-full border rounded px-4 py-2">

<button
class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">

Update Profile

</button>

</form>

</div>

