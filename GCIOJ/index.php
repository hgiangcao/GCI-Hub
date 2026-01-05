<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'contest.php';

// 1. Fetch All Contests
$contests = Contest::getAll();

// 2. Status Logic (Based on your previous request: Ignore end_time, check is_active)
function getContestStatus($is_active) {

    if ($is_active > 0) {
        return 'Active';
    } else {
        return 'Inactive';
    }
}

function getStatusColor($status) {
    switch ($status) {
        case 'Active':
            return 'text-brand-green bg-green-900/30 border-green-700 animate-pulse';
        case 'Inactive':
            return 'text-gray-500 bg-gray-900/30 border-gray-700';
        default:
            return 'text-gray-400 bg-gray-800 border-gray-600';
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Contests - GCIOJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', hover: '#3e3e3e', text: '#eff1f6', muted: '#9ca3af' },
                        brand: { orange: '#ffa116', green: '#2cbb5d', red: '#ef4444' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen flex flex-col">
    <?php $activePage = 'contest'; ?>
    <?php include_once 'nav.php'; ?>

    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold border-l-4 border-brand-orange pl-4">Contests</h1>
            <?php if(isset($_SESSION['student_id']) && $_SESSION['student_id'] == 'admin'): ?>
                <a href="contestmanager.php" class="text-sm bg-dark-surface border border-gray-600 px-3 py-1 rounded hover:bg-dark-hover">Manage Contests</a>
            <?php endif; ?>
        </div>

        <div class="bg-dark-surface rounded-lg border border-gray-700 overflow-hidden shadow-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-dark-muted text-xs uppercase border-b border-gray-700">
                        <th class="px-6 py-4 font-medium w-64">Contest Name</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium">Start Time</th>
                        <th class="px-6 py-4 font-medium">End Time</th>
                        <th class="px-6 py-4 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 text-sm">
                    <?php 
                    $activeContestCount = 0;
                    if (count($contests) > 0): 
                    ?>
                        <?php foreach ($contests as $row): 
                            // Filter inactive
                            if ($row['is_active'] == 1) $activeContestCount++;
                            // $activeContestCount++;
                            
                            $status = getContestStatus($row['is_active']);
                            $colorClass = getStatusColor($status);
                        ?>
                        <tr class="hover:bg-dark-hover transition group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white text-lg group-hover:text-brand-orange transition">
                                     <?php if ($status == 'Active'): ?>
                                    <a href="viewcontest.php?name=<?= urlencode($row['name']) ?>">  <?= htmlspecialchars($row['name']) ?> </a>
                                    <?php else: ?>
                                        <font> <?= htmlspecialchars($row['name']) ?></font>
                                <?php endif; ?>
                                </div>
                                <div class="text-xs text-dark-muted mt-1">
                                    Course: <?= htmlspecialchars($row['course']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-xs font-bold border <?= $colorClass ?>">
                                    <?= $status ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-dark-muted">
                                <?= date("Y-m-d H:i", strtotime($row['start_time'])) ?>
                            </td>
                            <td class="px-6 py-4 text-dark-muted">
                                <?= date("Y-m-d H:i", strtotime($row['end_time'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($status == 'Inactive'): ?>
                                    <button disabled class="bg-gray-700 text-gray-400 px-4 py-2 rounded cursor-not-allowed opacity-50">Inactive</button>
                                <?php else: ?>
                                    <a href="viewcontest.php?name=<?= urlencode($row['name']) ?>"
                                       class="bg-brand-green hover:bg-orange-600 text-white font-bold px-4 py-2 rounded transition">
                                        Enter
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($activeContestCount == 0): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-dark-muted">No active contests available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <?php  include 'footer.php' ?>

</body>
</html>