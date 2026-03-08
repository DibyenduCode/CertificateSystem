<?php

require_once __DIR__ . "/../auth_check.php";
require_once __DIR__ . "/../../config/database.php";

$error = "";
$generated_key = "";

/* Generate random API key */

function generateApiKey($length = 40){
return bin2hex(random_bytes($length/2));
}

if($_SERVER['REQUEST_METHOD']=="POST"){

$name = trim($_POST['name']);

if(!$name){
$error = "Client name is required.";
}

if(!$error){

$generated_key = generateApiKey();

$stmt = $pdo->prepare("
INSERT INTO api_keys (name, api_key, status, created_at)
VALUES (?, ?, 'active', NOW())
");

$stmt->execute([$name,$generated_key]);

}

}

include __DIR__ . "/../partials/header.php";
include __DIR__ . "/../partials/sidebar.php";
?>

<div class="flex-1 flex flex-col">

<header class="bg-white shadow px-6 py-4">
<h1 class="text-lg font-semibold">Generate API Key</h1>
</header>


<main class="p-6 flex-1">

<div class="bg-white shadow rounded-lg p-8 max-w-xl">


<?php if($error): ?>

<div class="bg-red-100 text-red-700 p-3 rounded mb-4">
<?= $error ?>
</div>

<?php endif; ?>


<form method="POST" class="space-y-4">

<div>

<label class="block text-sm font-medium text-gray-600 mb-1">
Client Name
</label>

<input
type="text"
name="name"
placeholder="Example: Mobile App / Website"
class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500"
required>

<p class="text-xs text-gray-400 mt-1">
Identify where this API key will be used.
</p>

</div>


<button
class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">

Generate API Key

</button>

</form>


<?php if($generated_key): ?>

<div class="mt-6 border-t pt-6">

<label class="block text-sm font-medium text-gray-600 mb-2">
Generated API Key
</label>

<div class="flex gap-2">

<input
id="apiKey"
value="<?= $generated_key ?>"
class="w-full border rounded px-3 py-2 font-mono text-sm"
readonly>

<button
onclick="copyKey()"
class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">

Copy

</button>

</div>

<p class="text-xs text-gray-400 mt-2">
Save this key now. You may not see it again.
</p>

</div>

<?php endif; ?>

</div>

</main>




<script>

function copyKey(){

let key = document.getElementById("apiKey");

key.select();
key.setSelectionRange(0,99999);

navigator.clipboard.writeText(key.value);

alert("API key copied!");

}

</script>