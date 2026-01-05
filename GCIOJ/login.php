<?php
// --- 1. BACKEND LOGIC ---
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'student.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);

    // Fetch user from DB
    $user = Student::getByStudentId($student_id);
    // Verify User and Password
    // Note: In production, compare hashed passwords.
    if ($user && $user['password'] === $password) {
        // Set Session Variables
        $_SESSION['user_id'] = $user['id'];        // Internal DB ID
        $_SESSION['student_id'] = $user['student_id']; // U123...
        $_SESSION['name'] = $user['name']; // Name
        $_SESSION['role'] = 'student'; // Default role

        // Redirect
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid Student ID or Password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - GCIOJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: '#1a1a1a',
                            surface: '#282828',
                            hover: '#3e3e3e',
                            text: '#eff1f6',
                            muted: '#9ca3af',
                            input: '#202020'
                        },
                        brand: {
                            orange: '#ffa116',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen flex flex-col antialiased">

    <?php include_once 'nav.php'; ?>

    <main class="flex-grow flex items-center justify-center px-4 sm:px-6 lg:px-8 relative">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-brand-orange/5 rounded-full blur-3xl -z-10"></div>

        <div class="max-w-md w-full space-y-8 bg-dark-surface p-8 rounded-xl border border-gray-700 shadow-2xl">

            <div class="text-center">
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-brand-orange">&lt;/GCI Online Judge&gt; </h2>
                <p class="mt-2 text-m text-white font-bold">Welcome back</p>
            </div>

            <?php if($error): ?>
                <div class="bg-red-900/20 border border-red-500/50 text-red-400 text-sm px-4 py-3 rounded-md text-center">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="login.php" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="student_id" class="block text-sm font-medium text-dark-muted">Student ID</label>
                        <div class="mt-1">
                            <input type="text" id="student_id" name="student_id" required
                                class="block w-full rounded-md border border-gray-700 bg-dark-input px-3 py-2 text-white placeholder-gray-500 focus:border-brand-orange focus:outline-none focus:ring-1 focus:ring-brand-orange sm:text-sm transition"
                                placeholder="U12627000">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-dark-muted">Password</label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" required
                                class="block w-full rounded-md border border-gray-700 bg-dark-input px-3 py-2 text-white placeholder-gray-500 focus:border-brand-orange focus:outline-none focus:ring-1 focus:ring-brand-orange sm:text-sm transition"
                                placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="flex w-full justify-center rounded-md bg-brand-orange px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-orange-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-orange transition duration-200">
                        Sign in
                    </button>
                </div>
            </form>

        </div>
    </main>

    <?= include 'footer.php' ?>
</body>
</html>