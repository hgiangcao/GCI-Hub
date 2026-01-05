<?php
session_start();
require_once 'problem.php';

// --- Pagination Logic ---
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20; // Problems per page
$offset = ($page - 1) * $limit;

// Fetch Data
$problems = Problem::getPage($limit, $offset);
$totalProblems = Problem::count();
$totalPages = ceil($totalProblems / $limit);

// Helper for Difficulty Colors
function getDifficultyColor($level) {
    return match (strtolower($level)) {
        'easy' => 'text-brand-green',
        'medium' => 'text-brand-yellow',
        'hard' => 'text-brand-red',
        default => 'text-dark-muted',
    };
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Problems - GCIOJ</title>
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
                        },
                        brand: {
                            orange: '#ffa116',  
                            green: '#2cbb5d',   
                            yellow: '#ffc01e',  
                            red: '#ef4743'      
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['Menlo', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1a1a1a; }
        ::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #6b7280; }
    </style>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen flex flex-col antialiased">

    <?php include 'nav.php';  ?>

    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">All Problems</h2>
            <div class="flex gap-3">
                <input type="text" placeholder="Search questions..." class="bg-dark-surface border border-gray-700 rounded-md px-4 py-2 text-sm text-dark-text focus:border-brand-orange outline-none placeholder-gray-500">
                <button class="bg-dark-surface hover:bg-dark-hover border border-gray-700 text-dark-text px-4 py-2 rounded-md text-sm transition">Pick One</button>
            </div>
        </div>

        <div class="bg-dark-surface rounded-lg border border-gray-700 overflow-hidden shadow-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-dark-muted text-xs uppercase border-b border-gray-700">
                        <th class="px-6 py-4 font-medium w-16">ID</th>
                        <th class="px-6 py-4 font-medium w-50">Title</th>
                        <th class="px-6 py-4 font-medium w-30">Tags</th>
                        <th class="px-6 py-4 font-medium w-32">Difficulty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 text-sm">
                    <?php if (count($problems) > 0): ?>
                        <?php foreach ($problems as $row): ?>
                            <tr class="hover:bg-dark-hover transition group cursor-pointer" onclick="window.location='solve_problem.php?code=<?= $row['code'] ?>'">
                                <td class="px-6 py-4 text-dark-muted font-mono">
                                    <?= htmlspecialchars($row['code']) ?>
                                </td>
                                <td class="px-6 py-4 font-medium text-dark-text group-hover:text-brand-orange transition">
                                    <?= htmlspecialchars($row['title']) ?>
                                    <?php if(!empty($row['Leetcode_link'])): ?>
                                        <a href="<?= htmlspecialchars($row['Leetcode_link']) ?>" target="_blank" class="text-xs text-gray-500 hover:text-brand-orange ml-2" onclick="event.stopPropagation()">
                                            [LeetCode ↗]
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-xs text-dark-muted">
                                    <?php 
                                        // Clean up tags: ['Array', 'DP'] -> Array, DP
                                        $tags = str_replace(['[', ']', "'"], '', $row['tag']);
                                        echo htmlspecialchars(substr($tags, 0, 30)) . (strlen($tags)>30 ? '...' : '');
                                    ?>
                                </td>
                                <td class="px-6 py-4 <?= getDifficultyColor($row['level']) ?>">
                                    <?= htmlspecialchars($row['level']) ?>
                                </td>
                                
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-dark-muted">No problems found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-6 gap-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 bg-dark-surface border border-gray-700 rounded hover:bg-dark-hover text-dark-text">&lt;</a>
            <?php else: ?>
                <span class="px-3 py-1 bg-dark-surface border border-gray-700 rounded opacity-50 cursor-not-allowed text-dark-muted">&lt;</span>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="px-3 py-1 bg-brand-orange text-white border border-brand-orange rounded"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>" class="px-3 py-1 bg-dark-surface border border-gray-700 rounded hover:bg-dark-hover text-dark-text"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 bg-dark-surface border border-gray-700 rounded hover:bg-dark-hover text-dark-text">&gt;</a>
            <?php else: ?>
                <span class="px-3 py-1 bg-dark-surface border border-gray-700 rounded opacity-50 cursor-not-allowed text-dark-muted">&gt;</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </main>

    <?php include 'footer.php';  ?>

</body>
</html>