<?php
// 1. Backend Logic
require_once 'auth.php'; // Handles session_start()
require_once 'contest.php';
require_once 'submission.php';

// 2. Specific Contest ID 24 for Practice
$contestId = 24;

$contest = Contest::getById($contestId);

if (!$contest) {
    include 'nav.php';
    die("<div class='p-10 text-white text-center'>Error: Practice Contest (ID 24) not found.</div>");
}

// B. Fetch Problems
$problems = Contest::getProblems($contest['id']);

// Helper for Letter Indexing (A, B, C...)
function getProblemLetter($index) {
    $letter = '';
    while ($index >= 0) {
        $letter = chr(65 + ($index % 26)) . $letter;
        $index = intval($index / 26) - 1;
    }
    return $letter;
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Practice - GCIOJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', hover: '#3e3e3e', text: '#eff1f6', muted: '#9ca3af' },
                        brand: { orange: '#ffa116', green: '#2cbb5d', red: '#ef4444', yellow: '#ffc01e', blue: '#008BFF' }
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

        <div class="bg-dark-surface border border-gray-700 rounded-lg p-6 mb-8 shadow-lg text-center">
            <h1 class="text-3xl font-bold text-brand-orange mb-2"><i class="fas fa-code mr-2"></i>Practice Mode</h1>
            <!-- <p class="text-dark-muted">Hone your skills with these practice problems.</p> -->
        </div>

        <div class="bg-dark-surface rounded-lg border border-gray-700 overflow-hidden shadow-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-dark-muted text-xs uppercase border-b border-gray-700">
                        <th class="px-6 py-4 font-medium w-16">#</th>
                        <th class="px-6 py-4 font-medium">Problem Name</th>
                        <th class="px-6 py-4 font-medium w-32 text-center">Output Type</th>
                        <th class="px-6 py-4 font-medium w-48 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 text-sm">
                    <?php if (count($problems) > 0): ?>
                        <?php foreach ($problems as $index => $row):
                                $link_solve_problem =  ($row['output_type'] == 'screen') ? 'solve_problem_terminal.php' : 'solve_problem.php' ;
                                $isSubmitted = false;
                                if (isset($_SESSION['user_id'])) {
                                    $isSubmitted = Submission::hasSubmitted($_SESSION['user_id'], $contest['id'] , $row['id']);
                                    if ($isSubmitted)
                                        $submissionStatus = Submission::getSubmissionStatus($_SESSION['user_id'], $contest['id'] , $row['id']);
                                    else
                                         $submissionStatus = "Not Submitted";
                                }
                            ?>
                            <tr class="hover:bg-dark-hover transition group">
                                <td class="px-6 py-4">
                                   <span class="font-mono font-bold text-brand-orange text-xl block">
                                       <?= getProblemLetter($index) ?>
                                   </span>
                                </td>
                                <td class="px-6 py-4">
                                     <a href="<?= $link_solve_problem ?>?code=<?= $row['code'] ?>&contest=<?= urlencode($contest['name']) ?>&course=PZ_0"
                                       class="font-medium text-white group-hover:text-brand-orange transition text-lg block">
                                        <?= htmlspecialchars($row['title']) ?>
                                    </a>
                                </td>

                                <td class="px-6 py-4 text-center">
        <?php 
        $isFunction = ($row['output_type'] === 'value');
        $colorClass = $isFunction 
            ? 'text-green-400 bg-green-400/10 border-green-400/20' 
            : 'text-orange-400 bg-orange-400/10 border-orange-400/20';
        $labelText = $isFunction ? 'Function' : 'Screen';
        ?>
        <span class="px-2 py-1 rounded border text-xs font-black tracking-wider <?= $colorClass ?>">
            <?= $labelText ?>
        </span>
    </td>

                                <td class="px-6 py-4 text-center">
                                    <?php if ($isSubmitted && strcmp($submissionStatus,"Accepted")==0): ?>
                                        <a href="<?= $link_solve_problem ?>?code=<?= $row['code'] ?>&contest=<?= urlencode($contest['name']) ?>"
                                           class="inline-block bg-green-900/30 border border-green-600 text-green-400 hover:bg-green-900/50 px-4 py-2 rounded text-xs font-bold transition">
                                             <?= htmlspecialchars($submissionStatus) ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= $link_solve_problem ?>?code=<?= $row['code'] ?>&contest=<?= urlencode($contest['name']) ?>"
                                           class="inline-block bg-red-900/30 border border-red-600 text-red-400 hover:bg-red-900/50 px-4 py-2 rounded text-xs font-bold transition">
                                             <?= htmlspecialchars($submissionStatus) ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-dark-muted">
                                <p class="text-lg">No problems added to practice yet.</p>
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
