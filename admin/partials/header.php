<?php 
require_once __DIR__ . "/../../config/config.php"; 
require_once __DIR__ . "/../../config/functions.php"; 

$flash = get_flash();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= $page_title ?? 'Admin Dashboard' ?> - Certificate Portal</title>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/logo.png">

<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- FontAwesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
body {
    font-family: 'Inter', sans-serif;
}
.swal2-popup {
    border-radius: 1rem !important;
    font-family: 'Inter', sans-serif !important;
}
</style>

</head>

<body class="bg-slate-100 text-slate-800 antialiased min-h-screen">

<!-- SWEETALERT2 FLASH TOASTER -->
<?php if ($flash): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: '<?= strtolower($flash['type'] === 'error' ? 'error' : ($flash['type'] === 'warning' ? 'warning' : ($flash['type'] === 'info' ? 'info' : 'success'))) ?>',
        title: '<?= addslashes($flash['message']) ?>',
        showConfirmButton: false,
        timer: 4500,
        timerProgressBar: true,
        customClass: {
            popup: 'shadow-2xl rounded-xl border border-slate-200'
        }
    });
});
</script>
<?php endif; ?>

<?php if (is_impersonating()): ?>
<div class="bg-indigo-950 text-indigo-100 px-6 py-2 text-xs font-semibold flex items-center justify-between border-b border-indigo-800 shadow-md">
    <div class="flex items-center space-x-2">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        <span>1-Click Access Active: Viewing Portal as Staff Member <strong><?= htmlspecialchars($_SESSION['staff_name'] ?? 'Staff') ?></strong></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/staff/exit_impersonate.php" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded font-bold transition flex items-center gap-1.5 shadow-sm">
        <i class="fas fa-undo"></i> Return to Admin Panel
    </a>
</div>
<?php endif; ?>

<div class="flex min-h-screen">