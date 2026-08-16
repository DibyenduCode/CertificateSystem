<?php
require_once __DIR__ . "/../config/config.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Certificate Verification Portal</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/logo.png">

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

<body class="min-h-screen flex flex-col justify-between items-center px-4 py-8 text-slate-800">

    <!-- TOP LOGO / HEADER -->
    <header class="w-full max-w-lg text-center pt-4">
        <div class="inline-flex items-center justify-center p-4 sm:p-5 rounded-2xl bg-white shadow-2xl mb-4">
            <img src="<?= BASE_URL ?>/assets/logo.png" alt="Biswas Company Logo" class="h-24 sm:h-28 w-auto object-contain">
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Certificate Verification Portal</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">Authentic, instant digital certificate validation system</p>
    </header>

    <!-- VERIFICATION CARD CONTAINER -->
    <main class="w-full max-w-md my-8">
        <div class="glass-card rounded-2xl shadow-2xl p-6 sm:p-8 border border-slate-200/80 glow-effect">

            <!-- TAB CONTROLS -->
            <div class="flex rounded-xl bg-slate-100 p-1 mb-6 text-xs font-semibold">
                <button type="button" id="tab-reg-btn" onclick="switchTab('reg')" class="flex-1 py-2.5 rounded-lg transition text-slate-700 bg-white shadow-sm flex items-center justify-center gap-1.5">
                    <i class="fas fa-id-card text-blue-600"></i> Reg & DOB
                </button>
                <button type="button" id="tab-cert-btn" onclick="switchTab('cert')" class="flex-1 py-2.5 rounded-lg transition text-slate-500 hover:text-slate-700 flex items-center justify-center gap-1.5">
                    <i class="fas fa-barcode text-blue-600"></i> Certificate No
                </button>
            </div>

            <!-- FORM 1: BY REGISTRATION NUMBER AND DOB -->
            <form id="form-reg" action="verify.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Registration Number</label>
                    <div class="relative">
                        <input type="text" name="registration_number" placeholder="e.g. BPLF<?= date('y') ?>0000001" required class="w-full pl-10 pr-4 py-3 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none uppercase font-mono">
                        <i class="fas fa-hashtag absolute left-3.5 top-3.5 text-slate-400 text-xs sm:text-sm"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Date of Birth</label>
                    <div class="relative">
                        <input type="date" name="dob" required class="w-full pl-10 pr-4 py-3 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <i class="fas fa-calendar-alt absolute left-3.5 top-3.5 text-slate-400 text-xs sm:text-sm"></i>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs sm:text-sm shadow-lg hover:shadow-blue-500/30 transition duration-150 flex items-center justify-center gap-2">
                    <i class="fas fa-shield-alt"></i> Verify Certificate Now
                </button>
            </form>

            <!-- FORM 2: BY CERTIFICATE NUMBER DIRECT -->
            <form id="form-cert" action="verify.php" method="GET" class="space-y-4 hidden">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Certificate Number</label>
                    <div class="relative">
                        <input type="text" name="cert" placeholder="e.g. UN1RWB26C0000001" required class="w-full pl-10 pr-4 py-3 text-xs sm:text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none uppercase font-mono">
                        <i class="fas fa-award absolute left-3.5 top-3.5 text-slate-400 text-xs sm:text-sm"></i>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs sm:text-sm shadow-lg hover:shadow-blue-500/30 transition duration-150 flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i> Direct Search Certificate
                </button>
            </form>

        </div>
    </main>

    <!-- FOOTER LINK -->
    <footer class="text-center text-xs text-slate-500 pb-4">
        <p>&copy; <?= date("Y") ?> BELIEFPRO LEARNING FORUM. Certificate Verification & Management System. All rights reserved.</p>
        <p class="mt-1"><a href="<?= BASE_URL ?>/admin/login.php" class="text-slate-400 hover:text-white underline transition"><i class="fas fa-lock text-[10px] mr-1"></i> Admin Login</a></p>
    </footer>

    <script>
        function switchTab(type) {
            const formReg = document.getElementById('form-reg');
            const formCert = document.getElementById('form-cert');
            const btnReg = document.getElementById('tab-reg-btn');
            const btnCert = document.getElementById('tab-cert-btn');

            if (type === 'reg') {
                formReg.classList.remove('hidden');
                formCert.classList.add('hidden');

                btnReg.className = "flex-1 py-2.5 rounded-lg transition text-slate-700 bg-white shadow-sm flex items-center justify-center gap-1.5";
                btnCert.className = "flex-1 py-2.5 rounded-lg transition text-slate-500 hover:text-slate-700 flex items-center justify-center gap-1.5";
            } else {
                formCert.classList.remove('hidden');
                formReg.classList.add('hidden');

                btnCert.className = "flex-1 py-2.5 rounded-lg transition text-slate-700 bg-white shadow-sm flex items-center justify-center gap-1.5";
                btnReg.className = "flex-1 py-2.5 rounded-lg transition text-slate-500 hover:text-slate-700 flex items-center justify-center gap-1.5";
            }
        }
    </script>
</body>

</html>