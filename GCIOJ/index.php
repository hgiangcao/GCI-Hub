<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'contest.php';

// 1. Fetch All Contests
$contests = Contest::getAll();

// 2. Group Contests by Course
$contestsByCourse = [];
if (!empty($contests)) {
    foreach ($contests as $row) {
        $courseName = !empty($row['course']) ? $row['course'] : 'General / Uncategorized';
        $contestsByCourse[$courseName][] = $row;
    }
    ksort($contestsByCourse); // Optional: Sort courses alphabetically
}

// 3. Status Logic
function getContestStatus($is_active) {
    return ($is_active > 0) ? 'Active' : 'Inactive';
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

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold border-l-4 border-brand-orange pl-4">Contests</h1>
            <?php if(isset($_SESSION['student_id']) && $_SESSION['student_id'] == 'chgiang'): ?>
                <a href="dashboard.php" class="text-sm bg-blue-500 border border-gray-600 px-3 py-1 rounded hover:bg-dark-hover">ADMIN</a>
            <?php endif; ?>
        </div>

        <?php if (empty($contestsByCourse)): ?>
            <div class="bg-dark-surface rounded-lg border border-gray-700 p-8 text-center text-dark-muted">
                No contests available at the moment.
            </div>
        <?php else: ?>

            <?php foreach ($contestsByCourse as $courseName => $courseContests): ?>

                <div class="mb-6">
                    <h2 class="text-xl font-bold text-brand-orange mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <?= htmlspecialchars($courseName) ?>
                    </h2>

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
                                <?php foreach ($courseContests as $row):
                                    $status = getContestStatus($row['is_active']);
                                    $colorClass = getStatusColor($status);
                                ?>
                                <tr class="hover:bg-dark-hover transition group">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-white text-lg group-hover:text-brand-orange transition">
                                             <?php if ($status == 'Active'): ?>
                                                <a href="viewcontest.php?name=<?= urlencode($row['name']) ?>&course=<?= urlencode($row['course']) ?>">
                                                    <?= htmlspecialchars($row['name']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-400"><?= htmlspecialchars($row['name']) ?></span>
                                            <?php endif; ?>
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
                                            <button disabled class="bg-gray-700 text-gray-400 px-4 py-2 rounded cursor-not-allowed opacity-50 text-xs font-bold uppercase">Expired</button>
                                        <?php else: ?>
                                            <a href="viewcontest.php?name=<?= urlencode($row['name']) ?>&course=<?= urlencode($row['course']) ?>"
                                               class="bg-brand-green hover:bg-green-600 text-white font-bold px-4 py-2 rounded transition text-xs uppercase shadow-lg shadow-green-900/20">
                                                Enter
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

    </main>

    <?php include 'footer.php' ?>

</body>
</html>