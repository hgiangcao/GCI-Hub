<?php
// 1. Backend Logic
require_once 'auth.php'; // Handles session_start()
require_once 'student.php';
require_once 'submission.php';
require_once 'problem.php';

// 2. Determine which user to show
if (isset($_GET['id'])) {
    $targetId = $_GET['id']; // This expects the Student ID string (e.g. "U12345")
} elseif (isset($_SESSION['student_id'])) {
    $targetId = $_SESSION['student_id'];
} else {
    header("Location: login.php");
    exit;
}

// 3. Fetch Student Data
$student = Student::getByStudentId($targetId);

if (!$student) {
    include_once 'nav.php';
    echo "<div class='text-center text-white mt-20'>User with ID " . htmlspecialchars($targetId) . " not found.</div>";
    exit;
}

// 4. Fetch Statistics
// Note: We need the internal DB ID for some queries if your Submission table uses INT foreign keys.
// Assuming getSolvedStats/getRecent accept the Student String ID based on your previous code, 
// OR you might need to pass $student['id'] (the integer) depending on your Model implementation.
// Below assumes your models handle the ID lookup or join correctly.

$totalProblems = Problem::getCountsByLevel();
$userSolved    = Submission::getSolvedStats($student['id']); // Pass Integer ID if Submission table uses int
$recentSubs    = Submission::getRecent($student['id'], 10);   // Pass Integer ID

// Helper to safely get counts
function getVal($arr, $key) { return isset($arr[$key]) ? $arr[$key] : 0; }

$solvedEasy  = getVal($userSolved, 'Easy');
$solvedMed   = getVal($userSolved, 'Medium');
$solvedHard  = getVal($userSolved, 'Hard');
$totalSolved = $solvedEasy + $solvedMed + $solvedHard;

$totalEasy   = getVal($totalProblems, 'Easy');
$totalMed    = getVal($totalProblems, 'Medium');
$totalHard   = getVal($totalProblems, 'Hard');
$globalTotal = $totalEasy + $totalMed + $totalHard;

// Calculate Percentages (Avoid Division by Zero)
$easyPct  = ($totalEasy > 0)   ? ($solvedEasy / $totalEasy) * 100 : 0;
$medPct   = ($totalMed > 0)    ? ($solvedMed / $totalMed)   * 100 : 0;
$hardPct  = ($totalHard > 0)   ? ($solvedHard / $totalHard) * 100 : 0;
$totalPct = ($globalTotal > 0) ? ($totalSolved / $globalTotal) * 100 : 0;

// Helper for Time Ago
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Just now';
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($student['student_id']) ?> - Profile</title>
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

    <main class="flex-grow max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="bg-dark-surface p-6 rounded-lg border border-gray-700 h-fit shadow-lg">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-20 h-20 rounded-lg bg-gray-700 flex items-center justify-center text-3xl font-bold text-gray-400 border border-gray-600">
                      
                         <?= htmlspecialchars(mb_substr($student['name'], 0, 1, "UTF-8")) ?>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white"><?= htmlspecialchars($student['student_id']) ?></h2>
                        <!-- <p class="text-dark-muted text-sm"><?= htmlspecialchars($displayName) ?></p> -->
                    </div>
                </div>

                <?php if(isset($_SESSION['student_id']) && $_SESSION['student_id'] == $student['student_id']): ?>
                    <button class="w-full py-2 mb-4 bg-brand-green/10 text-brand-green border border-brand-green/30 rounded hover:bg-brand-green/20 transition text-sm font-medium">
                        Edit Profile
                    </button>
                <?php endif; ?>

                <div class="border-t border-gray-700 pt-4 space-y-3 text-sm text-dark-muted">
                    <?php if(!empty($student['class'])): ?>
                    <div class="flex items-center gap-2">
                        <span>🎓</span> <?= htmlspecialchars($student['class']) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($student['email'])): ?>
                    <div class="flex items-center gap-2">
                        <span>📧</span> <?= htmlspecialchars($student['email']) ?>
                    </div>
                    <?php endif; ?>

                    <div class="flex items-center gap-2">
                        <span>📅</span> Joined <?= date("M Y", strtotime($student['created_at'] ?? 'now')) ?>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 space-y-6">

                <div class="bg-dark-surface p-6 rounded-lg border border-gray-700 shadow-lg">
                    <h3 class="font-bold text-dark-muted uppercase text-xs mb-6">Solved Problems</h3>

                    <div class="flex flex-col sm:flex-row items-center gap-10">
                        
                        <div class="relative w-32 h-32 rounded-full flex items-center justify-center"
                             style="background: conic-gradient(#ffa116 <?= $totalPct ?>%, #374151 0%);">
                            <div class="absolute w-28 h-28 bg-dark-surface rounded-full flex flex-col items-center justify-center">
                                <span class="text-2xl font-bold text-white"><?= $totalSolved ?></span>
                                <div class="text-xs text-dark-muted">Solved</div>
                            </div>
                        </div>

                        <div class="flex-1 w-full space-y-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-brand-green w-16 font-medium">Easy</span>
                                <div class="flex-1 mx-3 h-2 bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-brand-green transition-all duration-1000" style="width: <?= $easyPct ?>%"></div>
                                </div>
                                <span class="text-dark-muted w-16 text-right"><?= $solvedEasy ?>/<?= $totalEasy ?></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-brand-yellow w-16 font-medium">Medium</span>
                                <div class="flex-1 mx-3 h-2 bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-brand-yellow transition-all duration-1000" style="width: <?= $medPct ?>%"></div>
                                </div>
                                <span class="text-dark-muted w-16 text-right"><?= $solvedMed ?>/<?= $totalMed ?></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-brand-red w-16 font-medium">Hard</span>
                                <div class="flex-1 mx-3 h-2 bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-brand-red transition-all duration-1000" style="width: <?= $hardPct ?>%"></div>
                                </div>
                                <span class="text-dark-muted w-16 text-right"><?= $solvedHard ?>/<?= $totalHard ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-dark-surface p-6 rounded-lg border border-gray-700 shadow-lg">
                    <h3 class="font-bold text-dark-muted uppercase text-xs mb-4">Recent Submissions</h3>
                    <div class="space-y-0">
                        <?php if (count($recentSubs) > 0): ?>
                            <?php foreach ($recentSubs as $sub):
                                // Determine color/text based on 'status' (Matches submit.php values)
                                $statusLower = strtolower($sub['status']);
                                
                                if (strpos($statusLower, 'accepted') !== false) {
                                    $statusColor = 'text-brand-green';
                                } elseif (strpos($statusLower, 'pass') !== false) {
                                    $statusColor = 'text-brand-green';
                                } elseif (strpos($statusLower, 'wrong') !== false) {
                                    $statusColor = 'text-brand-red';
                                } elseif (strpos($statusLower, 'compile') !== false) {
                                    $statusColor = 'text-brand-yellow';
                                } else {
                                    $statusColor = 'text-gray-400';
                                }
                                
                                $statusText = ucfirst($sub['status']);
                            ?>
                            <div class="flex justify-between items-center text-sm border-b border-gray-700 py-3 last:border-0 hover:bg-dark-hover px-2 -mx-2 rounded transition">
                                <a href="solve_problem.php?code=<?= $sub['code'] ?>" class="font-medium text-white hover:text-brand-orange transition">
                                    <?= htmlspecialchars($sub['title']) ?>
                                </a>
                                <div class="flex items-center gap-4">
                                    <span class="text-dark-muted text-xs hidden sm:block">
                                        <?= time_elapsed_string($sub['created_at']) ?>
                                    </span>
                                    <span class="<?= $statusColor ?> font-bold text-xs w-28 text-right">
                                        <?= $statusText ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-dark-muted text-sm py-4 text-center">No submissions yet.</div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>