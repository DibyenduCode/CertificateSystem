<?php

require_once "../config/database.php";

$error = null;

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id']) || isset($_SESSION['staff_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === "POST")
{
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember_me']);

    if ($username && $password) {

        // 1. Check Admins Table
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username=?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password']))
        {
            $_SESSION['admin_id'] = $admin['id'];
            unset($_SESSION['staff_id']);
            unset($_SESSION['staff_name']);
            unset($_SESSION['staff_permissions']);
            unset($_SESSION['impersonated_by_admin']);

            // 7 Days Stay Logged In Cookie
            if ($remember) {
                $lifetime = 7 * 86400; // 7 days in seconds
                setcookie(session_name(), session_id(), time() + $lifetime, "/");
            }

            header("Location: dashboard.php");
            exit;
        }

        // 2. Check Staff Table
        $stmt = $pdo->prepare("SELECT * FROM staff WHERE username=?");
        $stmt->execute([$username]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($staff && password_verify($password, $staff['password']))
        {
            if ($staff['status'] !== 'active') {
                $error = "Account is inactive. Please contact your administrator.";
            } else {
                $_SESSION['staff_id'] = $staff['id'];
                $_SESSION['staff_name'] = $staff['name'];
                $_SESSION['staff_username'] = $staff['username'];
                $_SESSION['staff_permissions'] = json_decode($staff['permissions'] ?? '[]', true);
                unset($_SESSION['admin_id']);
                unset($_SESSION['impersonated_by_admin']);

                // 7 Days Stay Logged In Cookie
                if ($remember) {
                    $lifetime = 7 * 86400; // 7 days in seconds
                    setcookie(session_name(), session_id(), time() + $lifetime, "/");
                }

                header("Location: dashboard.php");
                exit;
            }
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please enter both username and password.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Login - Certificate Portal</title>
<link rel="icon" type="image/png" href="../assets/logo.png">

<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- FontAwesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #071527 0%, #0f2744 50%, #030a14 100%);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(24px);
    }

    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 0 30px rgba(14, 165, 233, 0.25), 0 0 15px rgba(29, 78, 216, 0.2); }
        50% { box-shadow: 0 0 50px rgba(14, 165, 233, 0.5), 0 0 25px rgba(29, 78, 216, 0.35); }
    }

    .glow-effect {
        animation: pulseGlow 4s infinite ease-in-out;
    }
</style>

</head>

<body class="min-h-screen flex flex-col items-center justify-center p-4">

<div class="glass-card rounded-3xl shadow-2xl w-full max-w-md p-8 sm:p-10 border border-sky-100/60 glow-effect">

    <!-- BRANDING & LOGO -->
    <div class="flex flex-col items-center mb-6 text-center">
        <div class="inline-flex items-center justify-center p-4 rounded-2xl bg-white shadow-xl mb-3 border border-slate-100">
            <img src="../assets/logo.png" alt="Biswas Company Logo" class="h-24 sm:h-28 w-auto object-contain">
        </div>

        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
            Certificate System
        </h1>

        <p class="text-slate-500 text-xs font-medium mt-0.5 flex items-center justify-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> Official Admin & Staff Portal
        </p>
    </div>

    <!-- ERROR MESSAGE BADGE -->
    <?php if ($error): ?>
        <div class="mb-5 p-3.5 bg-rose-50 border-l-4 border-rose-500 text-rose-800 text-xs rounded-r-xl font-semibold flex items-center gap-2 shadow-xs">
            <i class="fas fa-exclamation-circle text-rose-500 text-sm"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <form method="POST" class="space-y-5 text-xs">

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Username</label>
            <div class="relative">
                <input
                type="text"
                name="username"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                placeholder="Enter username"
                required
                class="w-full pl-10 pr-4 py-3 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:outline-none font-mono text-slate-800 bg-slate-50/50">
                <i class="fas fa-user absolute left-3.5 top-3.5 text-slate-400 text-xs sm:text-sm"></i>
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Password</label>
            <div class="relative">
                <input
                type="password"
                id="passwordInput"
                name="password"
                placeholder="Enter password"
                required
                class="w-full pl-10 pr-10 py-3 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:outline-none text-slate-800 bg-slate-50/50">
                <i class="fas fa-lock absolute left-3.5 top-3.5 text-slate-400 text-xs sm:text-sm"></i>
                <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-2.5 text-slate-400 hover:text-sky-600 focus:outline-none p-1.5 transition" title="Show / Hide Password">
                    <i id="eyeIcon" class="fas fa-eye text-xs sm:text-sm"></i>
                </button>
            </div>
        </div>

        <!-- STAY LOGGED IN FOR 7 DAYS CHECKBOX -->
        <div class="flex items-center justify-between pt-1">
            <label class="flex items-center space-x-2 text-xs text-slate-600 cursor-pointer select-none">
                <input type="checkbox" name="remember_me" value="1" class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500 cursor-pointer">
                <span class="font-medium">Stay logged in on this device (7 days)</span>
            </label>
        </div>

        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-blue-700 via-blue-600 to-sky-600 hover:from-blue-800 hover:to-sky-700 transition duration-150 text-white rounded-xl font-bold text-xs sm:text-sm shadow-lg hover:shadow-sky-500/30 flex items-center justify-center gap-2">
            <i class="fas fa-sign-in-alt"></i> Log In Now
        </button>

    </form>

    <div class="mt-6 pt-4 border-t border-slate-200 text-center">
        <a href="<?= BASE_URL ?>/public/index.php" class="text-xs text-slate-500 hover:text-sky-600 font-medium transition inline-flex items-center gap-1.5">
            <i class="fas fa-external-link-alt text-[10px]"></i> Public Certificate Verification Portal
        </a>
    </div>

</div>

<!-- PASSWORD VISIBILITY TOGGLE SCRIPT -->
<script>
function togglePasswordVisibility() {
    const input = document.getElementById('passwordInput');
    const icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>