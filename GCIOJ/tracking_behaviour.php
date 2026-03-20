<?php
require_once 'auth.php';
require_once 'db.php';
require_once 'contest.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$contestId = $_GET['contestID'] ?? 0;
$courseName = $_GET['course'] ?? '';
$db = DB::connect();

// 1. Fetch Contest Details
$stmt = $db->prepare("SELECT * FROM contest WHERE id = ?");
$stmt->execute([$contestId]);
$contest = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contest) {
    include 'nav.php';
    die("<div class='p-10 text-white text-center'>Error: Contest not found.</div>");
}

// 2. Fetch Students and their stats
// We use a LEFT JOIN to include students who haven't submitted anything
$sql = "
    SELECT 
        s.id, 
        s.student_id, 
        s.name,
        COALESCE(sub.total_submissions, 0) as total_submissions,
        COALESCE(sub.solved_problems, 0) as solved_problems,
        COALESCE(sub.max_violation, 0) as max_violation
    FROM student s
    JOIN registration r ON s.id = r.student_id
    JOIN course c ON r.course_id = c.id
    JOIN contest con ON con.course = CONCAT(c.code, '_', c.year)
    LEFT JOIN (
        SELECT 
            student_id, 
            COUNT(id) as total_submissions,
            COUNT(DISTINCT CASE WHEN status = 'Accepted' THEN problem_id END) as solved_problems,
            MAX(count_cheat) as max_violation
        FROM submission
        WHERE contest_id = ?
        GROUP BY student_id
    ) sub ON s.id = sub.student_id
    WHERE con.id = ?
    ORDER BY s.student_id ASC
";
$stmt = $db->prepare($sql);
$stmt->execute([$contestId, $contestId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Tracking Behaviour - <?= htmlspecialchars($contest['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', hover: '#3e3e3e', text: '#eff1f6', muted: '#9ca3af' },
                        brand: { orange: '#ffa116', green: '#2cbb5d', red: '#ef4444', blue: '#3b82f6' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'], mono: ['Roboto Mono', 'monospace'] }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen flex flex-col">

    <?php include_once 'nav.php'; ?>

    <main class="flex-grow max-w-[70%] mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

        <div class="bg-dark-surface border border-gray-700 rounded-lg p-6 mb-8 shadow-lg">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2"><i class="fas fa-chart-line mr-2 text-brand-orange"></i>Tracking Behaviour: <?= htmlspecialchars($contest['name']) ?></h1>
                    <p class="text-dark-muted text-sm">Monitoring student activity and violations for <span class="text-brand-orange"><?= htmlspecialchars($contest['course']) ?></span></p>
                </div>
                <div>
                    <a href="viewcontest.php?name=<?= urlencode($contest['name']) ?>&course=<?= urlencode($courseName) ?>"
                       class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded border border-gray-600 transition text-sm font-bold">
                        ← Back to Contest
                    </a>
                </div>
            </div>
        </div>

        <!-- Grid of Students (6 per row on large screens) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($students as $s): 
                // Determine violation color/icon
                $v = (int)$s['max_violation'];
                $cardClass = 'bg-dark-surface border-gray-700';
                $iconClass = 'text-dark-muted';
                $icon = 'fa-user-secret'; // Default
                
                if ($v < 5) {
                    $cardClass = 'bg-green-950/20 border-green-700/50';
                    $iconClass = 'text-brand-green';
                    $icon = 'fa-check-circle';
                } elseif ($v <= 10) {
                    $cardClass = 'bg-orange-950/20 border-orange-700/50';
                    $iconClass = 'text-brand-orange';
                    $icon = 'fa-eye';
                } else {
                    $cardClass = 'bg-red-950/20 border-red-700/50';
                    $iconClass = 'text-brand-red';
                    $icon = 'fa-exclamation-triangle';
                }
            ?>
                <div class="border rounded-xl p-4 shadow-lg hover:brightness-110 transition-all group <?= $cardClass ?>">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-3">
                            <i class="fas <?= $icon ?> <?= $iconClass ?> text-2xl"></i>
                        </div>
                        <h3 class="text-sm font-mono font-bold text-white mb-4"><?= htmlspecialchars($s['student_id']) ?></h3>
                        
                        <div class="grid grid-cols-1 gap-2 w-full">
                            <div class="flex justify-between items-center bg-black/30 rounded px-2 py-1">
                                <span class="text-[10px] uppercase font-bold text-dark-muted">Solved</span>
                                <span class="text-xs font-bold text-brand-green"><?= $s['solved_problems'] ?> | <?=$s['total_submissions'] ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center bg-black/30 rounded px-2 py-1">
                                <span class="text-[10px] uppercase font-bold text-dark-muted">Violations</span>
                                <span class="text-xs font-bold <?= $iconClass ?>"><?= $v ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($students)): ?>
                <div class="col-span-full py-12 text-center text-dark-muted">
                    <i class="fas fa-users-slash text-4xl mb-4 opacity-20"></i>
                    <p class="text-lg">No students registered for this course yet.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <?php include 'footer.php'; ?>

</body>
</html>
