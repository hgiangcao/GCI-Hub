<?php
include_once('db.php');

$contestId = $_GET['contestID'] ?? 0;
$courseName = $_GET['course'] ?? 0;
$db = DB::connect();

// A. Fetch Contest Details
$stmt = $db->prepare("SELECT * FROM contest WHERE id = ?");
$stmt->execute([$contestId]);
$contest = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contest) {
    include 'nav.php';
    die("<div class='p-10 text-white text-center'>Error: Contest not found.</div>");
}

// 1. Get Problems and Create Letter Map
$stmt = $db->prepare("
    SELECT p.id, p.code, p.title
    FROM problem p
    JOIN contest_problem cp ON p.id = cp.problem_id
    WHERE cp.contest_id = ?
    ORDER BY cp.problem_order ASC, p.id ASC
");
$stmt->execute([$contestId]);
$problems = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getProblemLetter($index) {
    return chr(65 + $index);
}

$problemLetterMap = [];
foreach ($problems as $index => $p) {
    $problemLetterMap[$p['id']] = getProblemLetter($index);
}

// 2. Get Students (Registered for this specific contest's course)
$stmt = $db->prepare("
    SELECT DISTINCT s.id, s.student_id, s.name
    FROM student s
    JOIN registration r ON s.id = r.student_id
    JOIN course c ON r.course_id = c.id
    JOIN contest con ON con.course = CONCAT(c.code, '_', c.year)
    WHERE con.id = ?
");
$stmt->execute([$contestId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
$validStudentIds = array_column($students, 'id');
$validStudentIdsMap = array_flip($validStudentIds);

// 3. Get Rank Data (Simplified for Timeline display items)
$stmtRank = $db->prepare("
    SELECT 
        student_id, 
        MAX(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) as is_solved,
        MAX(CASE WHEN status = 'Accepted' THEN id ELSE 0 END) as max_solved_id,
        MAX(count_cheat) as problem_max_cheat
    FROM submission
    WHERE contest_id = ?
    GROUP BY student_id, problem_id
");
$stmtRank->execute([$contestId]);
$rankRows = $stmtRank->fetchAll(PDO::FETCH_ASSOC);

$rankData = [];
foreach ($rankRows as $row) {
    $sid = $row['student_id'];
    if (!isset($rankData[$sid])) {
        $rankData[$sid] = ['solved' => 0, 'latest_id' => 0, 'max_cheat' => 0];
    }
    if ($row['is_solved'] == 1) {
        $rankData[$sid]['solved']++;
        $rankData[$sid]['latest_id'] = max($rankData[$sid]['latest_id'], (int)$row['max_solved_id']);
    }
    $rankData[$sid]['max_cheat'] = max($rankData[$sid]['max_cheat'], (int)($row['problem_max_cheat'] ?? 0));
}

// Sort students by Rank (Matches standings logic)
usort($students, function($a, $b) use ($rankData) {
    $statA = $rankData[$a['id']] ?? ['solved' => 0, 'latest_id' => 0];
    $statB = $rankData[$b['id']] ?? ['solved' => 0, 'latest_id' => 0];
    if ($statA['solved'] !== $statB['solved']) {
        return $statB['solved'] - $statA['solved'];
    }
    return $statA['latest_id'] - $statB['latest_id'];
});

// 4. Get Raw Submissions for Timeline (Time-Based)
$stmtLine = $db->prepare("SELECT id, student_id, problem_id, status, created_at FROM submission WHERE contest_id = ? ORDER BY id ASC");
$stmtLine->execute([$contestId]);
$timelineSubs = $stmtLine->fetchAll(PDO::FETCH_ASSOC);

$minTime = PHP_INT_MAX;
$maxTime = 0;
$studentTimeline = [];

foreach ($timelineSubs as $sub) {
    $ts = strtotime($sub['created_at']);
    // Filter window calculation by valid students (Rule: Step 158)
    if (isset($validStudentIdsMap[$sub['student_id']])) {
        $minTime = min($minTime, $ts);
        $maxTime = max($maxTime, $ts);
    }
    $studentTimeline[$sub['student_id']][] = $sub;
}

// Handle empty data or same-time activity
$contestStart = strtotime($contest['start_time'] ?? 'now');
$contestEnd   = strtotime($contest['end_time'] ?? 'now');
if ($minTime > $maxTime) { $minTime = $contestStart; $maxTime = $contestEnd; }
$timeRange = max(1, $maxTime - $minTime);

// Helper for Duration Formatting
function formatDuration($sec) {
    if ($sec < 60) return $sec . "s";
    $m = floor($sec / 60);
    if ($m < 60) return $m . "m";
    return floor($m / 60) . "h " . ($m % 60) . "m";
}

// Generate intermediate time steps
$stepCount = 5; 
$timeSteps = [];
for ($i = 0; $i <= ($stepCount + 1); $i++) {
    $timeSteps[] = ($timeRange / ($stepCount + 1)) * $i; // Relative seconds
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Timeline - <?= htmlspecialchars($contest['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', hover: '#3e3e3e', text: '#eff1f6', muted: '#9ca3af' },
                        brand: { orange: '#ffa116', green: '#2cbb5d', red: '#ef4444' }
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
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Submission Timeline: <?= htmlspecialchars($contest['name']) ?></h1>
                    <p class="text-dark-muted text-sm">Course: <span class="text-brand-orange"><?= htmlspecialchars($contest['course']) ?></span></p>
                </div>
                <div>
                    <a href="viewcontest.php?name=<?= urlencode($contest['name']) ?>&course=<?= urlencode($courseName) ?>"
                       class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded border border-gray-600 transition text-sm font-bold">
                        ← Back to Problems
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-dark-surface border border-gray-700 rounded-lg p-6 shadow-lg">
            <?php if (empty($timelineSubs)): ?>
                <div class="text-center py-10 text-dark-muted">
                    No submissions have been made yet.
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    
                    <!-- Global Timeline Legend (Top Axis) - Sticky for easier reference (Rule: Step 222 & 231) -->
                    <div class="sticky top-16 z-40 bg-dark-surface flex items-center mb-10 pb-4 border-b border-gray-800 shadow-sm">
                        <div class="w-56 shrink-0 font-bold text-dark-muted text-xs uppercase tracking-wider">Session Duration</div>
                        <div class="flex-grow flex justify-between text-[10px] font-mono px-2 text-dark-muted relative h-4">
                            <?php foreach ($timeSteps as $stepSec): ?>
                                <div class="flex flex-col items-center -translate-x-1/2 ">
                                    <div class="h-2 w-px bg-gray-700 mb-1"></div>
                                    <span><?= formatDuration($stepSec) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php 
                    foreach ($students as $s): 
                        $dbStudentId = $s['id']; 
                        $displayId = $s['student_id']; 
                        $submissions = $studentTimeline[$dbStudentId] ?? [];
                        $solvedCount = $rankData[$dbStudentId]['solved'] ?? 0;
                        $maxCheat = $rankData[$dbStudentId]['max_cheat'] ?? 0;
                    ?>
                        <div class="flex items-center group relative py-2">
                            <div class="w-56 font-mono text-sm text-white shrink-0 truncate flex justify-between pr-4" title="<?= htmlspecialchars($displayId) ?>">
                                <span><?= htmlspecialchars($displayId) ?></span>
                                <span class="text-brand-green font-bold">
                                    [<?=$solvedCount ?> | <?= count($submissions)?> | <span class="<?= $maxCheat > 0 ? 'text-brand-red' : '' ?>"><?= $maxCheat ?></span>]
                                </span>
                            </div>
                            
                            <div class="flex-grow h-2 bg-gray-900 rounded-full relative ml-2 mr-2">
    
                                <?php if (!empty($submissions)): 
                                    $firstTs = strtotime($submissions[0]['created_at']);
                                    $lastTs = strtotime(end($submissions)['created_at']);
                                    
                                    // Handle cases where sub is outside the calculated window (though unlikely here)
                                    $startPct = max(0, (($firstTs - $minTime) / $timeRange)) * 100;
                                    $widthPct = (($lastTs - $firstTs) / $timeRange) * 100;
                                ?>
                                    <div class="absolute top-1/2 -translate-y-1/2 h-1 bg-white/60 rounded-full" 
     style="left: <?= $startPct ?>%; width: <?= $widthPct ?>%;"></div>
                                <?php endif; ?>
                            
                                <?php 
                                $acceptedTracker = []; 
                                foreach ($submissions as $sub): 
                                    $subTs = strtotime($sub['created_at']);
                                    $percent = ( ($subTs - $minTime) / $timeRange ) * 100;
                                    $probLetter = $problemLetterMap[$sub['problem_id']] ?? '?';
                                    
                                    if ($sub['status'] === 'Accepted') {
                                        if (!isset($acceptedTracker[$sub['problem_id']])) {
                                            $colorClass = 'bg-brand-green z-30';
                                            $acceptedTracker[$sub['problem_id']] = true;
                                            $displayStatus = 'Accepted (First)';
                                        } else {
                                            $colorClass = 'bg-green-100 z-20';
                                            $displayStatus = 'Accepted (Repeated)';
                                        }
                                    } else {
                                        $colorClass = 'bg-red-400 z-10';
                                        $displayStatus = $sub['status'];
                                    }
                                ?>
                                    <div class="absolute top-1/2 w-3.5 h-3.5 rounded-full <?= $colorClass ?> hover:scale-150 transition-transform cursor-pointer border-2 border-[#282828]" 
                                         style="left: <?= $percent ?>%; transform: translate(-50%, -50%);"
                                         title="Duration: <?= formatDuration($subTs - $minTime) ?> | Clock: <?= date("H:i:s", $subTs) ?> | Prob: <?= $probLetter ?> | Status: <?= htmlspecialchars($displayStatus) ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>