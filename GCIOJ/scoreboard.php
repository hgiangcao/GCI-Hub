<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once('db.php');
$db = DB::connect();

// 1. Fetch all available courses for the selector
// Change this line:
$stmtCourses = $db->prepare("SELECT * FROM course WHERE id = ?");
$allCourses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

// 2. Identify current course from URL (Default to 2)
$courseId = $_GET['course_id'] ?? 2;
$currentCourseId = $courseId; 

// 3. Fetch Course Details
$stmt = $db->prepare("SELECT * FROM course WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    die("<div class='p-10 text-white text-center text-3xl'>Course not found.</div>");
}

// 4. Fetch all Contests for this course (Columns)
$stmt = $db->prepare("SELECT id, name FROM contest WHERE course_id = ? ORDER BY id ASC");
$stmt->execute([$courseId]);
$contests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Fetch all Students in this course (Rows)
$stmt = $db->prepare("
    SELECT s.id, s.student_id, s.name 
    FROM student s
    JOIN registration r ON s.id = r.student_id
    WHERE r.course_id = ?
    ORDER BY s.student_id ASC
");
$stmt->execute([$courseId]);
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
$stmt->execute([$courseId]);
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
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Course Scoreboard - <?= htmlspecialchars($course['course_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', hover: '#3e3e3e', text: '#eff1f6', muted: '#9ca3af' },
                        brand: { orange: '#ffa116', green: '#2cbb5d' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-dark-text min-h-screen">

    <nav class="max-w-[95%] mx-auto px-4 mt-8">
        <div class="flex flex-wrap gap-2 items-center bg-dark-surface p-3 rounded-lg border border-gray-700 shadow-md">
            <span class="text-dark-muted text-xs uppercase font-bold mr-2 px-2">Select Course:</span>
            <?php foreach ($allCourses as $c): 
                $isActive = ($c['id'] == $currentCourseId);
                $btnClass = $isActive 
                    ? "bg-brand-orange text-black border-brand-orange" 
                    : "bg-gray-800 text-dark-text border-gray-600 hover:bg-dark-hover";
            ?>
                <a href="?course_id=<?= $c['id'] ?>" 
                   class="px-4 py-2 rounded border transition text-sm font-bold <?= $btnClass ?>">
                    <?= htmlspecialchars($c['code']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <main class="max-w-[95%] mx-auto py-8">
        <div class="bg-dark-surface border border-gray-700 rounded-lg p-6 mb-8 shadow-lg">
            <h1 class="text-3xl font-bold text-white">Course Scoreboard</h1>
            <p class="text-dark-muted">Course: <span class="text-brand-orange"><?= htmlspecialchars($course['name']) ?></span></p>
        </div>

        <div class="bg-dark-surface rounded-lg border border-gray-700 overflow-x-auto shadow-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-dark-muted text-xs uppercase border-b border-gray-700">
                        <th class="px-4 py-4 text-center w-16">Rank</th>
                        <th class="px-4 py-4">Student</th>
                        <th class="px-4 py-4 text-center border-r border-gray-700 w-24">Total</th>
                        <?php foreach ($contests as $c): ?>
                            <th class="px-4 py-4 text-center min-w-[120px] font-mono text-brand-orange">
                                <?= htmlspecialchars($c['name']) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 text-sm">
                    <?php $rank = 1; foreach ($students as $s): 
                        $total = getTotalSolved($s['student_id'], $contests, $scores);
                    ?>
                        <tr class="hover:bg-dark-hover transition">
                            <td class="px-4 py-3 text-center text-dark-muted font-mono"><?= $rank++ ?></td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-white"><?= htmlspecialchars($s['student_id']) ?></div>
                                <div class="text-xs text-dark-muted"><?= htmlspecialchars($s['name']) ?></div>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-white border-r border-gray-700 bg-gray-800/20">
                                <?= $total ?>
                            </td>
                            <?php foreach ($contests as $c): 
                                $val = $scores[$s['student_id']][$c['id']] ?? 0;
                            ?>
                                <td class="px-4 py-3 text-center">
                                    <?php if ($val > 0): ?>
                                        <div class="inline-flex items-center justify-center px-3 py-1 rounded border border-green-900/50 bg-green-900/30 text-green-400 font-bold">
                                            <?= $val ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-dark-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>