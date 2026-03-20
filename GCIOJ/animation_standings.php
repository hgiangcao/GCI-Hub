<?php
session_start();
require_once 'auth.php';
include_once('db.php');

$contestId = $_GET['contestID'] ?? 0;
$db = DB::connect();

$stmt = $db->prepare("SELECT * FROM contest WHERE id = ?");
$stmt->execute([$contestId]);
$contest = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contest) die("Contest not found.");

$stmt = $db->prepare("
    SELECT s.student_id, s.name, 
    COUNT(DISTINCT CASE WHEN sub.status = 'Accepted' THEN sub.problem_id END) as solved
    FROM student s
    JOIN registration r ON s.id = r.student_id
    JOIN course c ON r.course_id = c.id
    JOIN contest con ON con.course = CONCAT(c.code, '_', c.year)
    LEFT JOIN submission sub ON s.id = sub.student_id AND sub.contest_id = con.id
    WHERE con.id = ?
    GROUP BY s.id
    ORDER BY solved ASC, s.student_id ASC 
");
$stmt->execute([$contestId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$studentsByLevel = [];
foreach ($students as $s) { $studentsByLevel[$s['solved']][] = $s; }

$stmtP = $db->prepare("SELECT COUNT(*) FROM contest_problem WHERE contest_id = ?");
$stmtP->execute([$contestId]);
$totalProblems = (int)$stmtP->fetchColumn();
$displayLevels = max($totalProblems, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stairway - <?= htmlspecialchars($contest['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --brick-w: 100px; --brick-h: 38px; --avatar-size: 40px; }
body { 
    margin: 0; 
    padding: 0;
    width: 100vw;
    height: 100vh;
    overflow: hidden; 
    font-family: 'Inter', sans-serif; 
    background-color: #020617; 
    position: relative;
}

/* Background Layer constrained to 100% screen */
body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: url('bg3.jpg') no-repeat center center;
    background-size: 100% 100%; /* Forces image to be exactly screen size */
    filter: blur(5px) brightness(1.0);
    z-index: -1;
    /* Removed scale(1.1) to prevent it being "bigger" than the screen */
}

            /* The world container stays at 70% width as requested */
            .world { 
                position: absolute; 
                width: 70vw; 
                height: 70vh; 
                left: 15vw;
                bottom: 80px;
                z-index: 10;
            }

        .header-banner {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 65px;
            background: rgba(15, 23, 42, 0.75); /* Made more transparent */
            backdrop-filter: blur(12px);
            display: flex;

            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        
        .level-platform {
            position: absolute;
            bottom: var(--y); 
            left: var(--x);
            display: flex; 
            flex-direction: column;
            transform: translateX(-50%);
            align-items: center;
        }

        .student-row {
            display: flex; 
            align-items: flex-end;
            justify-content: center;
            margin-bottom: -2px;
            width: var(--row-width);
            min-height: 45px;
        }

        .user-circle {
            width: var(--avatar-size); 
            height: var(--avatar-size);
            border-radius: 50%; 
            border: 2px solid #fff;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: 9px; 
            font-weight: 800;
            margin-right: var(--overlap);
            flex-shrink: 0;
            transition: 0.2s;
            box-shadow: 0 4px 8px rgba(0,0,0,0.5);
        }
        
        .user-circle:hover { transform: translateY(-8px); z-index: 100; filter: brightness(1.2); }

.brick-row { 
            display: flex;
            position: relative;
            /* Drop-shadow outside of individual brick containers */
            filter: drop-shadow(0 12px 0px rgba(0, 0, 0, 0.25));
        }

        .brick { 
            width: var(--brick-w); height: var(--brick-h); 
            background: url('imgs/brick.png') no-repeat center/contain;
            image-rendering: pixelated; 
        }

        .label { 
            font-size: 10px; 
            color: #64748b; 
            margin-top: 5px; 
            font-weight: bold;
        }
    </style>
</head>
<body>

<header class="header-banner">
    <div class="flex items-center gap-4">
        <a href="standings.php?contestID=<?= $contestId ?>" class="bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded text-xs font-bold transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> STANDINGS
        </a>
        <h1 class="text-lg font-black text-green-400 tracking-wider uppercase">
            <?= htmlspecialchars($contest['name']) ?>
        </h1>
    </div>
</header>

<div class="world">
    <?php 
    for ($level = 0; $level <= $displayLevels; $level++): 
        $levelStudents = $studentsByLevel[$level] ?? [];
        $count = count($levelStudents);
        
        $numBricks = max(2, min(6, ceil($count / 6))); 
        $rowWidth = $numBricks * 100;

        // Dynamic Overlap
        $overlap = -15; 
        if ($count > 1) {
            $spacing = ($rowWidth - 40) / ($count - 1);
            $overlap = min(0, $spacing - 40); 
        }

        // Map X from 5% to 95% to ensure right-side padding for platforms
        $x = ($level / $displayLevels) * 90 + 5;
        $y = ($level / $displayLevels) * 100;
    ?>
        <div class="level-platform" style="--x: <?= $x ?>%; --y: <?= $y ?>%;">
            <div class="student-row" style="--row-width: <?= $rowWidth ?>px; --overlap: <?= $overlap ?>px;">
                <?php foreach ($levelStudents as $s): 

                    $isMe = (isset($_SESSION['student_id']) && $_SESSION['student_id'] == $s['student_id']);
                    
                    // Elevated style for current user, normal for others
                    $meStyle = $isMe 
                        ? "transform: translateY(-10px); z-index: 100; border-color: #fbbf24; box-shadow: 0 0 15px #fbbf24;" 
                        : "z-index: 10;";
                ?>
                    <div class="user-circle" 
                         style="filter: hue-rotate(<?= crc32($s['student_id']) % 360 ?>deg); <?= $meStyle ?>">
                        
                        <?php if($isMe): ?>
                            <span><?= htmlspecialchars(substr($s['student_id'], -3)) ?></span>
                            <div class="absolute -top-5 text-[9px] font-black text-yellow-400">YOU</div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>

            <div class="brick-row">
                <?php for ($i = 0; $i < $numBricks; $i++): ?><div class="brick"></div><?php endfor; ?>
            </div>
            
            <div class="label">LVL <?= $level ?></div>
        </div>
    <?php endfor; ?>
</div>

</body>
</html>