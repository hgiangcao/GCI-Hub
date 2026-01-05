<?php
session_start();
require_once 'student.php';

// 1. Fetch Ranking Data
$ranking = Student::getRanking();
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Ranking - GCIOJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', hover: '#3e3e3e', text: '#eff1f6', muted: '#9ca3af' },
                        brand: { orange: '#ffa116', green: '#2cbb5d', red: '#ef4444' },
                        rank: { gold: '#FFD700', silver: '#C0C0C0', bronze: '#CD7F32' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen flex flex-col">

    <?php $activePage = 'ranking'; ?>
    <?php include_once 'nav.php'; ?>

    <main class="flex-grow max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">
        
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-white mb-2">Ranking</h1>
            <p class="text-dark-muted">Top students by total problems solved.</p>
        </div>

        <div class="bg-dark-surface rounded-xl border border-gray-700 overflow-hidden shadow-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-dark-muted text-xs uppercase border-b border-gray-700 tracking-wider">
                        <th class="px-8 py-5 font-bold w-24 text-center">Rank</th>
                        <th class="px-8 py-5 font-bold ">Student</th>
                        <th class="px-8 py-5 font-bold text-center">Problems Solved</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 text-sm">
                    <?php 
                        $lastSolvedCount = -1;
                        $rank = 0;
                        if (count($ranking) > 0): ?>
                        <?php foreach ($ranking as $index => $row): 
                            if ($row['solved_count'] != $lastSolvedCount) {
                                $rank = $index + 1; 
                            }
                            $lastSolvedCount = $row['solved_count'];
                            
                            // Determine Row Styling based on Rank
                            $rankStyle = "text-white font-medium";
                            $icon = "";
                            
                            // if ($rank === 1) {
                            //     $rankStyle = "text-rank-gold font-bold text-lg";
                            //     $icon = "👑";
                            // } elseif ($rank === 2) {
                            //     $rankStyle = "text-rank-silver font-bold text-lg";
                            //     $icon = "🥈";
                            // } elseif ($rank === 3) {
                            //     $rankStyle = "text-rank-bronze font-bold text-lg";
                            //     $icon = "🥉";
                            // }
                        ?>
                        <tr class="hover:bg-dark-hover transition duration-150 ease-in-out group">
                            <td class="px-8 py-5 text-center">
                                <span class="<?= $rankStyle ?>">
                                    <?= $icon ? $icon : "#" . $rank ?>
                                </span>
                            </td>

                            <td class="px-8 py-5 text-center">
                                <div class="flex items-center">

                                    
                                    <div>
                                        <div class="text-base font-semibold text-white group-hover:text-brand-orange transition">
                                            <?= htmlspecialchars($row['student_id']) ; ?>
                                        
                                        <?php if(isset($_SESSION['student_id']) && $_SESSION['student_id'] == $row['student_id']): ?>
                                            <font class="text-[10px] bg-brand-orange/20 text-brand-orange px-1.5 py-0.5 rounded ml-1">YOU</font>
                                        <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-8 py-5 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-900/30 text-brand-green border border-green-800 ">
                                    <?= $row['solved_count'] ?> 
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-dark-muted">
                                No submissions yet. Be the first to solve a problem!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <footer class="bg-dark-surface border-t border-gray-700 py-8 mt-auto text-center text-dark-muted text-sm">
        &copy; 2025 GCIOJ
    </footer>

</body>
</html>