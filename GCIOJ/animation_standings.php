<?php


    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    session_start();
    require_once 'auth.php';
    include_once('db.php');


    $courseName = $_GET['course'] ?? 0;
    $contestId = $_GET['contestID'] ?? 0;
    $db = DB::connect();

    $stmt = $db->prepare("SELECT * FROM contest WHERE id = ?");
    $stmt->execute([$contestId]);
    $contest = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contest) die("Contest not found.");

    $stmt = $db->prepare("
        SELECT s.student_id, s.name, s.avatar_img,
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

    $backUrl = ($contest['is_active'] == 1) 
        ? "viewcontest.php?name=" . urlencode($contest['name']) . "&course=" . urlencode($courseName)
        : "index.php"; // Change 'home.php' to your actual home filename
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Stairway - <?= htmlspecialchars($contest['name']) ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <style>
            :root { --brick-w: 100px; --brick-h: 38px; --avatar-size: 64px; }
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

                .world { 
        position: absolute; 
        width: 70vw; 
        /* Reduced height slightly and increased bottom offset to ensure clearance */
        height: 60vh; 
        left: 15vw;
        bottom: 100px; /* Shifted up from 80px */
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



    /* Ensure the row sits flush on the bricks */
    .student-row {
        display: flex; 
        align-items: flex-end; /* Ground avatars to the bottom */
        justify-content: center;
        margin-bottom: 0;      /* Removed negative margin */
        width: var(--row-width);
        min-height: 90px;      /* Space for the 2x avatar + jump */
        position: relative;
    }


                /* Animation for jumping - slowed down */
    @keyframes jump {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-12px); }
    }

    /* Animation for horizontal wandering - slowed down */
    @keyframes wander {
        0% { left: 0; transform: scaleX(-1); } 
        49% { transform: scaleX(-1); }
        50% { left: var(--max-move); transform: scaleX(1); }
        99% { transform: scaleX(1); }
        100% { left: 0; transform: scaleX(-1); }
    }
.user-circle {
    width: var(--avatar-size); 
    height: var(--avatar-size);
    position: absolute;
    bottom: 0;
    display: flex; 
    align-items: center; 
    justify-content: center;
    /* Apply both jump and wander */
    animation: 

        wander var(--wander-speed) ease-in-out infinite,
         jump var(--jump-speed) ease-in-out infinite;
    animation-delay: var(--delay);
}

/* "Me" avatar: Keep jump, remove wander to stay in middle */
.user-circle.is-me {
    --avatar-size: 100px; 
    left: 50% !important;
    transform: translateX(-50%);
    animation: jump var(--jump-speed) ease-in-out infinite; 
    z-index: 100;
}
    .user-circle img, .user-circle div {
      
    }

       .brick-row { 
        display: flex;
        position: relative;
        /* Ensure no gaps at the flex level */
        gap: 0; 
        padding: 0;
        margin: 0;
        filter: drop-shadow(0 7px 0px rgba(0, 0, 0, 0.25));
          z-index: 0;
    }

    .brick { 
        width: var(--brick-w); 
        height: var(--brick-h); 
        background: url('imgs/brick.png') no-repeat center/cover;
        image-rendering: pixelated; 
        /* Force 1px overlap to prevent sub-pixel rendering gaps */
        margin-right: -1px; 
        flex-shrink: 0;
         z-index: 0;
    }
            .label { 
                font-size: 15px; 
                color: cyan; 
                margin-top: 5px; 
                font-weight: bold;
            }
        </style>
    </head>
    <body>

        <?php include_once 'nav.php'; ?>

                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex gap-2">
                        <a href="<?= $backUrl ;?>"
                           class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded border border-gray-600 transition text-sm font-bold">
                            ← Back to Contest
                        </a>
                    </div>
                </div>


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
        
        // Reduced speed: Jump (1.5s - 2.5s), Wander (8s - 15s)
        $jumpSpeed = (rand(15, 25) / 10) . 's';
        $wanderSpeed = (rand(150, 200) / 10) . 's';
        $delay = '-' . (rand(0, 100) / 10) . 's';
        
        $maxMove = ($rowWidth - 40) . 'px';

        $meStyle = $isMe 
            ? " z-index: 100;" 
            : "z-index: 10;";
    ?>
        <div class="user-circle <?= $isMe ? 'is-me' : '' ?>" 
         style="<?= $meStyle ?> 
                --jump-speed: <?= $jumpSpeed ?>; 
                --wander-speed: <?= $wanderSpeed ?>; 
                --delay: <?= $delay ?>; 
                --max-move: <?= $maxMove ?>;">
            
            <?php if(!empty($s['avatar_img'])): ?>
                <img src="avt_img/<?= htmlspecialchars($s['avatar_img']) ?>" class="h-full w-full object-cover">
            <?php else: ?>
                <div class="bg-slate-700 h-full w-full flex items-center justify-center border border-white/20">
                    <span class="text-white"><?= htmlspecialchars(mb_substr($s['name'] ?? 'U', 0, 1)) ?></span>
                </div>
            <?php endif; ?>

            <?php if($isMe): ?>
                <div class="absolute -top-4 bg-yellow-400 text-black text-[10px] px-1  font-black uppercase"><?= htmlspecialchars($s['student_id']) ?></div>
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