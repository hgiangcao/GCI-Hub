<?php
require_once 'auth.php';
require_once 'check_admin.php';

?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - GCIOJ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', hover: '#3e3e3e', text: '#eff1f6', muted: '#9ca3af' },
                        brand: {
                            orange: '#ffa116',
                            green: '#2cbb5d',
                            blue: '#3b82f6',
                            purple: '#8b5cf6',
                            red: '#ef4444',
                            cyan: '#06b6d4'
                        }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen flex flex-col">

    <?php include_once 'nav.php'; ?>

    <div class="flex-grow p-6 md:p-8 max-w-[90rem] mx-auto w-full space-y-10">

        <div class="border-b border-gray-800 pb-5">
            <h1 class="text-2xl md:text-3xl font-bold text-white mb-1">Admin Dashboard</h1>
            <p class="text-sm text-dark-muted">Welcome back. Select an area to manage.</p>
        </div>

        <section>
            <h2 class="text-lg font-semibold text-gray-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-users text-brand-blue"></i> Students & Courses
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
                
                <a href="student_management.php" class="group bg-dark-surface p-5 rounded-xl border border-gray-700 hover:border-brand-blue transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-blue-500/10 flex flex-col items-center justify-center text-center h-44">
                    <div class="w-12 h-12 bg-blue-500/10 rounded-full flex items-center justify-center mb-3 group-hover:bg-blue-500/20 transition">
                        <svg class="w-6 h-6 text-brand-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1">Manage Students</h3>
                    <p class="text-xs text-dark-muted">Add, remove, or edit student accounts.</p>
                </a>

                <a href="course_management.php" class="group bg-dark-surface p-5 rounded-xl border border-gray-700 hover:border-brand-red transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-red-500/10 flex flex-col items-center justify-center text-center h-44">
                    <div class="w-12 h-12 bg-red-500/10 rounded-full flex items-center justify-center mb-3 group-hover:bg-red-500/20 transition">
                        <svg class="w-6 h-6 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1">Course Management</h3>
                    <p class="text-xs text-dark-muted">Manage Courses.</p>
                </a>

                <a href="student_registration.php" class="group bg-dark-surface p-5 rounded-xl border border-gray-700 hover:border-brand-cyan transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-cyan-500/10 flex flex-col items-center justify-center text-center h-44">
                    <div class="w-12 h-12 bg-cyan-500/10 rounded-full flex items-center justify-center mb-3 group-hover:bg-cyan-500/20 transition">
                        <i class="fa-solid fa-graduation-cap text-xl text-brand-cyan"></i>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1">Add Student-Course</h3>
                    <p class="text-xs text-dark-muted">Register Students to Courses.</p>
                </a>

                <a href="scoreboard.php" class="group bg-dark-surface p-5 rounded-xl border border-gray-700 hover:border-brand-cyan transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-cyan-500/10 flex flex-col items-center justify-center text-center h-44">
                    <div class="w-12 h-12 bg-cyan-500/10 rounded-full flex items-center justify-center mb-3 group-hover:bg-cyan-500/20 transition">
                        <svg class="w-6 h-6 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1">Class Scoreboards</h3>
                    <p class="text-xs text-dark-muted">View rankings and grades for each class.</p>
                </a>

            </div>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-gray-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-code text-brand-orange"></i> Problems & Contests
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
                
                <a href="create_problem.php" class="group bg-dark-surface p-5 rounded-xl border border-gray-700 hover:border-brand-orange transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-orange-500/10 flex flex-col items-center justify-center text-center h-44">
                    <div class="w-12 h-12 bg-orange-500/10 rounded-full flex items-center justify-center mb-3 group-hover:bg-orange-500/20 transition">
                        <svg class="w-6 h-6 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1">Manage Problems</h3>
                    <p class="text-xs text-dark-muted">Create problems, upload test cases.</p>
                </a>

                <a href="create_problem_blank.php" class="group bg-dark-surface p-5 rounded-xl border border-gray-700 hover:border-brand-cyan transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-cyan-500/10 flex flex-col items-center justify-center text-center h-44">
                    <div class="w-12 h-12 bg-cyan-500/10 rounded-full flex items-center justify-center mb-3 group-hover:bg-cyan-500/20 transition">
                        <i class="fas fa-bolt text-xl text-brand-cyan"></i>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1">Quick Create Problem</h3>
                    <p class="text-xs text-dark-muted">DB entry only — no files needed.</p>
                </a>

                <a href="list_problems.php" class="group bg-dark-surface p-5 rounded-xl border border-gray-700 hover:border-brand-orange transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-orange-500/10 flex flex-col items-center justify-center text-center h-44">
                    <div class="w-12 h-12 bg-orange-500/10 rounded-full flex items-center justify-center mb-3 group-hover:bg-orange-500/20 transition">
                        <svg class="w-6 h-6 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1">List Problems</h3>
                    <p class="text-xs text-dark-muted">View and manage all Problems.</p>
                </a>

                <a href="contest_management.php" class="group bg-dark-surface p-5 rounded-xl border border-gray-700 hover:border-brand-green transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-green-500/10 flex flex-col items-center justify-center text-center h-44">
                    <div class="w-12 h-12 bg-green-500/10 rounded-full flex items-center justify-center mb-3 group-hover:bg-green-500/20 transition">
                        <svg class="w-6 h-6 text-brand-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1">Manage Contests</h3>
                    <p class="text-xs text-dark-muted">Schedule contests and assign problems.</p>
                </a>

            </div>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-gray-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-server text-brand-purple"></i> Submissions & System
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
                
                <a href="submission_management.php" class="group bg-dark-surface p-5 rounded-xl border border-gray-700 hover:border-brand-purple transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-purple-500/10 flex flex-col items-center justify-center text-center h-44">
                    <div class="w-12 h-12 bg-purple-500/10 rounded-full flex items-center justify-center mb-3 group-hover:bg-purple-500/20 transition">
                        <svg class="w-6 h-6 text-brand-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1">Manage Submissions</h3>
                    <p class="text-xs text-dark-muted">Review code, re-judge, detect plagiarism.</p>
                </a>

                <a href="view_submissions.php" class="group bg-dark-surface p-5 rounded-xl border border-gray-700 hover:border-brand-orange transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-orange-500/10 flex flex-col items-center justify-center text-center h-44">
                    <div class="w-12 h-12 bg-orange-500/10 rounded-full flex items-center justify-center mb-3 group-hover:bg-orange-500/20 transition">
                        <i class="fa-solid fa-code text-xl text-brand-orange"></i>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1">Review Submissions</h3>
                    <p class="text-xs text-dark-muted">Review & run student code across problems.</p>
                </a>

                <a href="export_database.php" class="group bg-dark-surface p-5 rounded-xl border border-gray-700 hover:border-brand-purple transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-purple-500/10 flex flex-col items-center justify-center text-center h-44">
                    <div class="w-12 h-12 bg-purple-500/10 rounded-full flex items-center justify-center mb-3 group-hover:bg-purple-500/20 transition">
                        <i class="fa-solid fa-database text-xl text-brand-purple"></i>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1">Export Database</h3>
                    <p class="text-xs text-dark-muted">Download entire database as SQL.</p>
                </a>

            </div>
        </section>

    </div>
</body>
</html>