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

    <div class="flex-grow p-8 max-w-7xl mx-auto w-full">

        <div class="mb-10">
            <h1 class="text-3xl font-bold text-white mb-2">Admin Dashboard</h1>
            <p class="text-dark-muted">Welcome back. Select an area to manage.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <a href="student_management.php" class="group bg-dark-surface p-8 rounded-2xl border border-gray-700 hover:border-brand-blue transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/10 flex flex-col items-center justify-center text-center h-64">
                <div class="w-16 h-16 bg-blue-500/10 rounded-full flex items-center justify-center mb-6 group-hover:bg-blue-500/20 transition">
                    <svg class="w-8 h-8 text-brand-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Manage Students</h2>
                <p class="text-sm text-dark-muted">Add, remove, or edit student accounts.</p>
            </a>

            <a href="create_problem.php" class="group bg-dark-surface p-8 rounded-2xl border border-gray-700 hover:border-brand-orange transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-orange-500/10 flex flex-col items-center justify-center text-center h-64">
                <div class="w-16 h-16 bg-orange-500/10 rounded-full flex items-center justify-center mb-6 group-hover:bg-orange-500/20 transition">
                    <svg class="w-8 h-8 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Manage Problems</h2>
                <p class="text-sm text-dark-muted">Create problems, upload test cases.</p>
            </a>


            <a href="list_problems.php" class="group bg-dark-surface p-8 rounded-2xl border border-gray-700 hover:border-brand-orange transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-orange-500/10 flex flex-col items-center justify-center text-center h-64">
                <div class="w-16 h-16 bg-orange-500/10 rounded-full flex items-center justify-center mb-6 group-hover:bg-orange-500/20 transition">
                    <svg class="w-8 h-8 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">List Problems</h2>
                <p class="text-sm text-dark-muted">List all Problems</p>
            </a>

            <a href="contest_management.php" class="group bg-dark-surface p-8 rounded-2xl border border-gray-700 hover:border-brand-green transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-green-500/10 flex flex-col items-center justify-center text-center h-64">
                <div class="w-16 h-16 bg-green-500/10 rounded-full flex items-center justify-center mb-6 group-hover:bg-green-500/20 transition">
                    <svg class="w-8 h-8 text-brand-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Manage Contests</h2>
                <p class="text-sm text-dark-muted">Schedule contests and assign problems.</p>
            </a>

            <a href="submission_management.php" class="group bg-dark-surface p-8 rounded-2xl border border-gray-700 hover:border-brand-purple transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-purple-500/10 flex flex-col items-center justify-center text-center h-64">
                <div class="w-16 h-16 bg-purple-500/10 rounded-full flex items-center justify-center mb-6 group-hover:bg-purple-500/20 transition">
                    <svg class="w-8 h-8 text-brand-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Manage Submissions</h2>
                <p class="text-sm text-dark-muted">Review code, re-judge, detect plagiarism.</p>
            </a>
<!--
            <a href="dashboard_contest.php" class="group bg-dark-surface p-8 rounded-2xl border border-gray-700 hover:border-brand-red transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-red-500/10 flex flex-col items-center justify-center text-center h-64">
                <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mb-6 group-hover:bg-red-500/20 transition">
                    <svg class="w-8 h-8 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Live Dashboard</h2>
                <p class="text-sm text-dark-muted">Real-time stats and contest monitoring.</p>
            </a>
-->
            <a href="course_management.php" class="group bg-dark-surface p-8 rounded-2xl border border-gray-700 hover:border-brand-red transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-red-500/10 flex flex-col items-center justify-center text-center h-64">
                <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mb-6 group-hover:bg-red-500/20 transition">
                    <svg class="w-8 h-8 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Course Management</h2>
                <p class="text-sm text-dark-muted">Manage Course.</p>
            </a>


            <a href="class_list.php" class="group bg-dark-surface p-8 rounded-2xl border border-gray-700 hover:border-brand-cyan transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-cyan-500/10 flex flex-col items-center justify-center text-center h-64">
                <div class="w-16 h-16 bg-cyan-500/10 rounded-full flex items-center justify-center mb-6 group-hover:bg-cyan-500/20 transition">
                    <svg class="w-8 h-8 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Class Scoreboards</h2>
                <p class="text-sm text-dark-muted">View rankings and grades for each class.</p>
            </a>



        <a href="student_registration.php" class="group bg-dark-surface p-8 rounded-2xl border border-gray-700 hover:border-brand-cyan transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-cyan-500/10 flex flex-col items-center justify-center text-center h-64">
            <div class="w-16 h-16 bg-cyan-500/10 rounded-full flex items-center justify-center mb-6 group-hover:bg-cyan-500/20 transition">
                <i class="fa-solid fa-graduation-cap text-3xl text-brand-cyan"></i>
            </div>
            <h2 class="text-xl font-bold text-white mb-2">Add Student-Course</h2>
            <p class="text-sm text-dark-muted">Registration Student to Course.</p>
        </a>

        </div>
    </div>
</body>
</html>