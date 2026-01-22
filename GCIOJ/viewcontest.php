<?php
// 1. Backend Logic
require_once 'auth.php'; // Handles session_start()
require_once 'contest.php';
require_once 'submission.php';

// 2. Get Contest by Name
if (!isset($_GET['name'])) {
    die("Error: No contest name specified.");
}

$contestName = urldecode($_GET['name']);
$courseName = urldecode($_GET['course']);


// A. Fetch the Contest Details first (Needed for ID, Times, Active status)
$contest = Contest::getByNameAndCourse($contestName,$courseName);

if (!$contest) {
    include 'nav.php';
    die("<div class='p-10 text-white text-center'>Error: Contest 'htmlspecialchars($contestName)' not found.</div>");
}

// B. Fetch Problems using the Contest ID we just found
$problems = Contest::getProblems($contest['id']);

// 3. Status Logic
$status = ($contest['is_active'] == 1) ? 'Active' : 'Inactive';

// Helper for Letter Indexing (A, B, C...)
function getProblemLetter($index) {
    return chr(65 + $index);
}

// Helper for Difficulty Colors
function getDifficultyColor($level) {
    switch (strtolower($level)) {
        case 'easy':
            return 'text-brand-green';
        case 'medium':
            return 'text-brand-yellow';
        case 'hard':
            return 'text-brand-red';
        default:
            return 'text-dark-muted';
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($contest['name']) ?> - GCIOJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', hover: '#3e3e3e', text: '#eff1f6', muted: '#9ca3af' },
                        brand: { orange: '#ffa116', green: '#2cbb5d', red: '#ef4444', yellow: '#ffc01e' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'], mono: ['Roboto Mono', 'monospace'] }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen flex flex-col">

    <?php include_once 'nav.php'; ?>

    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

        <div class="bg-dark-surface border border-gray-700 rounded-lg p-6 mb-8 shadow-lg">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-3xl font-bold text-white"><?= htmlspecialchars($contest['name']) ?> - <?= htmlspecialchars($contest['course']) ?></h1>
                        <?php if($status == 'Active'): ?>
                            <span class="px-2 py-1 bg-green-900/50 text-brand-green border border-green-700 text-xs rounded font-bold uppercase tracking-wide animate-pulse">Active</span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-gray-700 text-gray-400 border border-gray-600 text-xs rounded font-bold uppercase tracking-wide">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-dark-muted text-sm">
                        <span class="mr-4">📅 Start: <?= $contest['start_time'] ?></span>
                        <span>🏁 End: <?= $contest['end_time'] ?></span>
                    </p>
                <p>
                 <span><a href="standings.php?contestID=<?= $contest['id'] ?>&course=<?= $courseName ?>">Standings</a></span>
                </p>
                </div>
            </div>
        </div>

        <div class="bg-dark-surface rounded-lg border border-gray-700 overflow-hidden shadow-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-dark-muted text-xs uppercase border-b border-gray-700">
                        <th class="px-6 py-4 font-medium w-16">#</th>
                        <th class="px-6 py-4 font-medium">Problem Name</th>
                        <th class="px-6 py-4 font-medium w-32 text-center">Points</th>
                        <th class="px-6 py-4 font-medium w-48 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 text-sm">
                    <?php
                        $n_problem = count($problems) ;
                        if ($n_problem>0)
                            $pts = round(100/$n_problem,2);
                        else
                            $pts = 0;

                        if (count($problems) > 0): ?>
                        <?php foreach ($problems as $index => $row):
                                $link_solve_problem =  ($row['output_type'] == 'screen') ? 'solve_problem_terminal.php' : 'solve_problem.php' ;
                                // Check submission status using Integers (User ID and Contest ID)
                                $isSubmitted = false;
                                if (isset($_SESSION['user_id'])) {
                                    $isSubmitted = Submission::hasSubmitted($_SESSION['user_id'], $contest['id'] , $row['id']);
                                }
                            ?>
                            <tr class="hover:bg-dark-hover transition group">
                                <td class="px-6 py-4">
                                   <span class="font-mono font-bold text-brand-orange text-xl block">
                                       <?= getProblemLetter($index) ?>
                                   </span>
                                </td>
                                <td class="px-6 py-4">
                                     <a href="<?= $link_solve_problem ?>?code=<?= $row['code'] ?>&contest=<?= urlencode($contestName) ?>&course=<?= urlencode($courseName) ?>"
                                       class="font-medium text-white group-hover:text-brand-orange transition text-lg block">
                                        <?= htmlspecialchars($row['title']) ?>
                                    </a>
                                </td>

                                <td class="px-6 py-4 text-center font-bold <?= getDifficultyColor($row['level']) ?>">
                                    <?= $pts ?>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <?php if ($isSubmitted): ?>
                                        <a href="<?= $link_solve_problem ?>?code=<?= $row['code'] ?>&contest=<?= urlencode($contestName) ?>&course=<?= urlencode($courseName) ?>"
                                           class="inline-block bg-green-900/30 border border-green-600 text-green-400 hover:bg-green-900/50 px-4 py-2 rounded text-xs font-bold transition">
                                            Submitted
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= $link_solve_problem ?>?code=<?= $row['code'] ?>&contest=<?= urlencode($contestName) ?>&course=<?= urlencode($courseName) ?>"
                                           class="inline-block bg-red-900/30 border border-red-600 text-red-400 hover:bg-red-900/50 px-4 py-2 rounded text-xs font-bold transition">
                                            Not Submitted
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-dark-muted">
                                <p class="text-lg">No problems added to this contest yet.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

   <?php include 'footer.php' ?>

</body>
</html>