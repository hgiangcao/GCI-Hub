<?php

include_once('db.php');
require_once 'check_admin.php';

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

// 1. Get Problems (Standard)
$stmt = $db->prepare("
    SELECT p.id, p.code, p.title
    FROM problem p
    JOIN contest_problem cp ON p.id = cp.problem_id
    WHERE cp.contest_id = ?
    ORDER BY cp.problem_order ASC, p.id ASC
");
$stmt->execute([$contestId]);
$problems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Get Students (Fixed Duplicates)
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

// 3. Get Submission Counts & Solved Status
// We aggregate by student/problem to get the count and check if ANY submission was accepted.
$stmt = $db->prepare("
    SELECT
        student_id,
        problem_id,
        COUNT(id) as sub_count,
        MAX(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) as is_solved
    FROM submission
    WHERE contest_id = ?
    GROUP BY student_id, problem_id
");
$stmt->execute([$contestId]);

// Map results: [student_id][problem_id] => ['count' => int, 'solved' => bool]
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$submissionMap = [];
foreach ($rows as $row) {
    $submissionMap[$row['student_id']][$row['problem_id']] = [
        'count' => $row['sub_count'],
        'solved' => ($row['is_solved'] == 1)
    ];
}

// Helper: Get Letter A, B, C...
function getProblemLetter($index) {
    return chr(65 + $index);
}

// Helper: Calculate Total Solved for ranking
function countSolved($studentId, $problems, $map) {
    $count = 0;
    foreach ($problems as $p) {
        if (isset($map[$studentId][$p['id']]) && $map[$studentId][$p['id']]['solved']) {
            $count++;
        }
    }
    return $count;
}

// Sort students by solved count (Desc) then ID
usort($students, function($a, $b) use ($problems, $submissionMap) {
    $solvedA = countSolved($a['id'], $problems, $submissionMap);
    $solvedB = countSolved($b['id'], $problems, $submissionMap);
    if ($solvedA == $solvedB) return strcmp($a['student_id'], $b['student_id']);
    return $solvedB - $solvedA;
});

?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Standings - <?= htmlspecialchars($contest['name']) ?></title>
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
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'], mono: ['Roboto Mono', 'monospace'] }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen flex flex-col">

    <?php include_once 'nav.php'; ?>

    <main class="flex-grow max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

        <div class="bg-dark-surface border border-gray-700 rounded-lg p-6 mb-8 shadow-lg">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">
                        Standings: <?= htmlspecialchars($contest['name']) ?>
                    </h1>
                    <p class="text-dark-muted text-sm">
                        Course: <span class="text-brand-orange"><?= htmlspecialchars($contest['course']) ?></span>
                    </p>
                </div>
                <div>
                    <a href="contest_view.php?name=<?= urlencode($contest['name']) ?>&course=<?= urlencode($courseName) ?>"
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
                        <th class="px-4 py-4 font-medium w-64">Student</th>
                        <th class="px-4 py-4 font-medium w-24 text-center border-r border-gray-700">Solved</th>

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
                        $totalSolved = countSolved($s['id'], $problems, $submissionMap);
                    ?>
                        <tr class="hover:bg-dark-hover transition">
                            <td class="px-4 py-3 text-center text-dark-muted font-mono">
                                <?= $rank++ ?>
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-bold text-white"><?= htmlspecialchars($s['student_id']) ?></div>
                                <div class="text-xs text-dark-muted"><?= htmlspecialchars($s['name']) ?></div>
                            </td>

                            <td class="px-4 py-3 text-center font-bold text-white border-r border-gray-700 bg-gray-800/20">
                                <?= $totalSolved ?>
                            </td>

                            <?php foreach ($problems as $p):
                                $data = $submissionMap[$s['id']][$p['id']] ?? null;

                                // Determine styling based on logic:
                                // Accepted -> Green
                                // Not Accepted but Submitted -> Red
                                // No Submission -> Neutral
                                if ($data) {
                                    if ($data['solved']) {
                                        $styleClass = 'bg-green-900/30 text-green-400 border-green-900/50';
                                        $mark = '+';
                                    } else {
                                        $styleClass = 'bg-red-900/30 text-red-400 border-red-900/50';
                                        $mark = '-';
                                    }
                                }
                            ?>
                                <td class="px-2 py-3 text-center border-l border-gray-700/50">
                                    <?php if ($data): ?>
                                        <div class="inline-flex items-center justify-center w-8 h-8 rounded border <?= $styleClass ?> font-bold text-sm">
                                            <?= $mark ?><?= $data['count'] ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-dark-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($students) == 0): ?>
                        <tr>
                            <td colspan="<?= count($problems) + 3 ?>" class="px-6 py-8 text-center text-dark-muted">
                                No students registered for this course yet.
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