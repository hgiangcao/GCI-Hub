<?php
session_start();
require_once 'auth.php';
include_once('db.php');
// require_once 'check_admin.php';

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

// 1. Get Problems
$stmt = $db->prepare("
    SELECT p.id, p.code, p.title
    FROM problem p
    JOIN contest_problem cp ON p.id = cp.problem_id
    WHERE cp.contest_id = ?
    ORDER BY cp.problem_order ASC, p.id ASC
");
$stmt->execute([$contestId]);
$problems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Get Students
$stmt = $db->prepare("
    SELECT DISTINCT s.id, s.student_id, s.name
    FROM student s
    JOIN registration r ON s.id = r.student_id
    JOIN course c ON r.course_id = c.id
    JOIN contest con ON con.course = CONCAT(c.code, '_', c.year)
    WHERE con.id = ?
    ORDER BY s.student_id ASC
");
$stmt->execute([$contestId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Get Submission Map & Ranking Data
$stmt = $db->prepare("
    SELECT 
        student_id, 
        problem_id, 
        COUNT(id) as sub_count,
        MAX(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) as is_solved,
        MAX(CASE WHEN status = 'Accepted' THEN id ELSE 0 END) as max_solved_id
    FROM submission
    WHERE contest_id = ?
    GROUP BY student_id, problem_id
");
$stmt->execute([$contestId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$submissionMap = [];
$rankData = [];

foreach ($rows as $row) {
    $sid = $row['student_id'];
    
    $submissionMap[$sid][$row['problem_id']] = [
        'count' => $row['sub_count'],
        'solved' => ($row['is_solved'] == 1)
    ];

    if (!isset($rankData[$sid])) {
        $rankData[$sid] = ['solved' => 0, 'total_sub' => 0, 'latest_id' => 0];
    }
    
    if ($row['is_solved'] == 1) {
        $rankData[$sid]['solved']++;
        $rankData[$sid]['latest_id'] = max($rankData[$sid]['latest_id'], (int)$row['max_solved_id']);
        // Only count submissions for problems that were eventually solved
        $rankData[$sid]['total_sub'] += $row['sub_count'];
    }
}

// 4. Sort students
usort($students, function($a, $b) use ($rankData) {
    $statA = $rankData[$a['id']] ?? ['solved' => 0, 'latest_id' => 0];
    $statB = $rankData[$b['id']] ?? ['solved' => 0, 'latest_id' => 0];

    if ($statA['solved'] !== $statB['solved']) {
        return $statB['solved'] - $statA['solved'];
    }
    return $statA['latest_id'] - $statB['latest_id'];
});

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
    <title>Standings - <?= htmlspecialchars($contest['name']) ?></title>
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
    <?php if (isset($contest['anti_cheat']) && $contest['anti_cheat']): ?>
    <div class="bg-yellow-500/10 border-b border-yellow-500/30 text-yellow-500 px-4 py-2 text-sm flex items-center justify-center gap-2 w-full">
        <i class="fas fa-shield-alt"></i>
        <span><strong>考試監控中 | 防作弊系統已啟動：</strong>測驗期間請勿切換分頁或離開瀏覽器，系統將全程監控異常行為。</span>
    </div>
    <?php endif; ?>

    <main class="flex-grow max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

        <div class="bg-dark-surface border border-gray-700 rounded-lg p-6 mb-8 shadow-lg">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Standings: <?= htmlspecialchars($contest['name']) ?> - <span ><?= htmlspecialchars($contest['course']) ?></h1>
                
                    <p class="mt-4 flex flex-wrap gap-4 text-sm font-bold">
                  <a href="standings.php?contestID=<?= $contest['id'] ?>&course=<?= $courseName ?>" class="text-brand-orange hover:underline decoration-2 underline-offset-4"><i class="fas fa-list-ol mr-1"></i>Standings</a>
                  <a href="animation_standings.php?contestID=<?= $contest['id'] ?>&course=<?= $courseName ?>" class="text-brand-blue hover:underline decoration-2 underline-offset-4"><i class="fa-solid fa-bolt-lightning"></i>Funny</a>
                  <?php
                    if (isset($_SESSION['student_id']) && $_SESSION['student_id'] == 'chgiang') {
                        echo "<a href='timeline.php?contestID={$contest['id']}&course={$courseName}' class='text-brand-yellow hover:underline decoration-2 underline-offset-4'><i class='fas fa-history mr-1'></i>Timeline</a>";
                        echo "<a href='tracking_behaviour.php?contestID={$contest['id']}&course={$courseName}' class='text-brand-green hover:underline decoration-2 underline-offset-4'><i class='fas fa-chart-line mr-1'></i>Tracking</a>";
                        
                    }
                    ?>
                </p>
                </div>
                <div class="flex gap-2">
                    <a href="animation_standings.php?contestID=<?= $contest['id'] ?>&course=<?= $courseName ?>" 
                       class="px-4 py-2 bg-brand-green hover:bg-green-600 text-white rounded border border-green-700 transition text-sm font-bold flex items-center gap-2">
                        <i class="fas fa-gamepad"></i> Visual Standings
                    </a>
                    <a href="viewcontest.php?name=<?= urlencode($contest['name']) ?>&course=<?= urlencode($courseName) ?>"
                       class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded border border-gray-600 transition text-sm font-bold">
                        ← Back to Problems
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-dark-surface rounded-lg border border-gray-700 overflow-x-auto shadow-lg">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-800 text-dark-muted text-xs uppercase border-b border-gray-700">
                        <th class="px-4 py-4 font-medium w-16 text-center">Rank</th>
                        <th class="px-4 py-4 font-medium w-32">Student ID</th>
                        <th class="px-4 py-4 font-medium w-24 text-center border-r border-gray-700">Solved</th>
                        <th class="px-4 py-4 font-medium w-24 text-center border-r border-gray-700">Submissions</th>
                        <?php foreach ($problems as $index => $p): ?>
                            <th class="px-4 py-4 font-medium text-center min-w-[80px]" title="<?= htmlspecialchars($p['title']) ?>">
                                <span class="font-mono text-brand-orange text-lg"><?= getProblemLetter($index) ?></span>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 text-sm">
                    <?php
                    $rank = 1;
                    foreach ($students as $s):
                        $stats = $rankData[$s['id']] ?? ['solved' => 0, 'total_sub' => 0];
                        $isMe = (isset($_SESSION['student_id']) && $_SESSION['student_id'] == $s['student_id']);
                    ?>
                        <tr class="hover:bg-dark-hover transition">
                            <td class="px-4 py-3 text-center text-dark-muted font-mono"><?= $rank++ ?></td>
                            <td class="px-4 py-3">
                                 <?php if ($isMe): ?>
                                        <div class="font-bold text-brand-orange flex items-center gap-2">
                                            <i class="fas fa-user-circle"></i>
                                            <?= htmlspecialchars($s['student_id']) ?> (You)
                               </div>
                            <?php else: ?>
                             <div class="text-dark-muted italic">Student <?= $rank - 1 ?></div>
                         <?php endif; ?>
                        </td>
                            <td class="px-4 py-3 text-center font-bold text-white border-r border-gray-700 bg-gray-800/20">
                                <?= $stats['solved'] ?>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-white border-r border-gray-700 bg-gray-800/20">
                                <?= $stats['total_sub'] ?>
                            </td>

                            <?php foreach ($problems as $p): 
                                $data = $submissionMap[$s['id']][$p['id']] ?? null;
                                $styleClass = 'text-dark-muted opacity-30';
                                if ($data) {
                                    $styleClass = $data['solved'] 
                                        ? 'bg-green-900/30 text-green-400 border-green-900/50' 
                                        : 'bg-red-900/30 text-red-400 border-red-900/50';
                                }
                            ?>
                                <td class="px-2 py-3 text-center border-l border-gray-700/50">
                                    <?php if ($data): ?>
                                        <div class="inline-flex items-center justify-center w-8 h-8 rounded border <?= $styleClass ?> font-bold text-sm">
                                            <?= $data['count'] ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-dark-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="<?= count($problems) + 4 ?>" class="px-6 py-8 text-center text-dark-muted">
                                No students registered for this course yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>