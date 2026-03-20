<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once('db.php');
$db = DB::connect();

// 1. Fetch all available courses for the selector
$allCourses = $db->query("SELECT * FROM course ORDER BY year DESC, semester ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);

if (empty($allCourses)) {
    die("<div class='p-10 text-white text-center text-3xl'>No courses found.</div>");
}

// 2. Identify current course from URL (Default to first course)
$courseId = $_GET['course_id'] ?? $allCourses[0]['id'];
$currentCourseId = (int)$courseId;

// 3. Fetch Course Details
$stmt = $db->prepare("SELECT * FROM course WHERE id = ?");
$stmt->execute([$currentCourseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    die("<div class='p-10 text-white text-center text-3xl'>Course not found.</div>");
}

// 4. Fetch all Contests for this course (Columns)
$stmt = $db->prepare("SELECT id, name FROM contest WHERE course_id = ? ORDER BY id ASC");
$stmt->execute([$currentCourseId]);
$contests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Fetch all Students in this course (Rows)
$stmt = $db->prepare("
    SELECT s.id, s.student_id, s.name 
    FROM student s
    JOIN registration r ON s.id = r.student_id
    WHERE r.course_id = ?
    ORDER BY s.student_id ASC
");
$stmt->execute([$currentCourseId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Fetch Score Data: [student_id][contest_id] => count_accepted
$stmt = $db->prepare("
    SELECT 
        s.student_id as s_key, 
        sub.contest_id, 
        COUNT(DISTINCT sub.problem_id) as solved_count
    FROM submission sub
    JOIN student s ON sub.student_id = s.id
    WHERE sub.status = 'Accepted' 
      AND sub.contest_id IN (SELECT id FROM contest WHERE course_id = ?)
    GROUP BY s.id, sub.contest_id
");
$stmt->execute([$currentCourseId]);
$scores = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $scores[$row['s_key']][$row['contest_id']] = $row['solved_count'];
}

// Helper for total solved across all contests
function getTotalSolved($sid, $contests, $scores) {
    $total = 0;
    foreach ($contests as $c) {
        $total += $scores[$sid][$c['id']] ?? 0;
    }
    return $total;
}

// Sort students by total solved (Desc)
usort($students, function($a, $b) use ($contests, $scores) {
    return getTotalSolved($b['student_id'], $contests, $scores) <=> getTotalSolved($a['student_id'], $contests, $scores);
});

// Helper: Format course label
function formatCourseLabel($c) {
    return htmlspecialchars($c['code'] . '_' . $c['year']);
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Course Scoreboard - <?= htmlspecialchars($course['name']) ?></title>
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

    <main class="flex-grow max-w-[95%] mx-auto px-4 py-8 w-full">

        <!-- Header -->
        <div class="bg-dark-surface border border-gray-700 rounded-lg p-6 mb-6 shadow-lg">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white">📊 Course Scoreboard</h1>
                    <p class="text-dark-muted mt-1">Course: <span class="text-brand-orange font-bold"><?= htmlspecialchars($course['name']) ?></span>
                        <span class="text-gray-500 ml-2">(<?= htmlspecialchars($course['code'] . ' — ' . $course['year'] . ' ' . $course['semester']) ?>)</span>
                    </p>
                </div>
                <div class="text-sm text-dark-muted">
                    <span class="text-white font-bold"><?= count($students) ?></span> students · 
                    <span class="text-white font-bold"><?= count($contests) ?></span> contests
                </div>
            </div>
        </div>

        <!-- Course Selector -->
        <div class="bg-dark-surface border border-gray-700 rounded-lg p-4 mb-6 shadow-lg">
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-dark-muted text-xs uppercase font-bold mr-2 px-1 flex items-center gap-1">
                    <i class="fas fa-graduation-cap"></i> Select Course:
                </span>
                <?php foreach ($allCourses as $c): 
                    $isActive = ($c['id'] == $currentCourseId);
                    $btnClass = $isActive 
                        ? "bg-brand-orange text-black border-brand-orange shadow-md shadow-orange-500/20" 
                        : "bg-gray-800 text-dark-text border-gray-600 hover:bg-dark-hover hover:border-gray-500";
                ?>
                    <a href="?course_id=<?= $c['id'] ?>" 
                       class="px-4 py-2 rounded-lg border transition text-sm font-bold <?= $btnClass ?>">
                        <?= formatCourseLabel($c) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Scoreboard Table -->
        <?php if (empty($contests)): ?>
            <div class="bg-dark-surface border border-gray-700 rounded-lg p-12 text-center text-dark-muted shadow-lg">
                <i class="fas fa-inbox text-4xl mb-4 block"></i>
                <p class="text-lg">No contests found for this course.</p>
            </div>
        <?php else: ?>
        <div class="bg-dark-surface rounded-lg border border-gray-700 overflow-x-auto shadow-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-dark-muted text-xs uppercase border-b border-gray-700">
                        <th class="px-4 py-4 text-center w-16 sticky left-0 bg-gray-800 z-10">Rank</th>
                        <th class="px-4 py-4 sticky left-16 bg-gray-800 z-10 min-w-[180px]">Student</th>
                        <th class="px-4 py-4 text-center border-r border-gray-600 w-20 sticky left-[244px] bg-gray-800 z-10">Total</th>
                        <?php foreach ($contests as $c): ?>
                            <th class="px-4 py-4 text-center min-w-[100px] font-mono text-brand-orange">
                                <?= htmlspecialchars($c['name']) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 text-sm">
                    <?php $rank = 1; foreach ($students as $s): 
                        $total = getTotalSolved($s['student_id'], $contests, $scores);
                        $isTop3 = $rank <= 3 && $total > 0;
                        $medalIcon = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : ''));
                    ?>
                        <tr class="hover:bg-dark-hover transition <?= $isTop3 ? 'bg-yellow-900/5' : '' ?>">
                            <td class="px-4 py-3 text-center font-mono sticky left-0 bg-dark-surface z-10 <?= $isTop3 ? 'bg-yellow-900/5' : '' ?>">
                                <?php if ($isTop3): ?>
                                    <span class="text-lg"><?= $medalIcon ?></span>
                                <?php else: ?>
                                    <span class="text-dark-muted"><?= $rank ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 sticky left-16 bg-dark-surface z-10 <?= $isTop3 ? 'bg-yellow-900/5' : '' ?>">
                                <div class="font-bold text-white text-xs"><?= htmlspecialchars($s['student_id']) ?></div>
                                <div class="text-[10px] text-dark-muted truncate max-w-[150px]"><?= htmlspecialchars($s['name'] ?? '') ?></div>
                            </td>
                            <td class="px-4 py-3 text-center font-bold border-r border-gray-600 sticky left-[244px] bg-dark-surface z-10 <?= $isTop3 ? 'bg-yellow-900/5' : '' ?>">
                                <span class="<?= $total > 0 ? 'text-white' : 'text-dark-muted' ?>"><?= $total ?></span>
                            </td>
                            <?php foreach ($contests as $c): 
                                $val = $scores[$s['student_id']][$c['id']] ?? 0;
                            ?>
                                <td class="px-4 py-3 text-center">
                                    <?php if ($val > 0): ?>
                                        <div class="inline-flex items-center justify-center px-3 py-1 rounded border border-green-900/50 bg-green-900/30 text-green-400 font-bold text-xs">
                                            <?= $val ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-dark-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php $rank++; endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>