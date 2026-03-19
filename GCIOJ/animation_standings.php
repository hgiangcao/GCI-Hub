<?php
include_once('db.php');
$contestId = $_GET['contestID'] ?? 0;
$courseName = $_GET['course'] ?? 0;
$db = DB::connect();

$stmt = $db->prepare("SELECT * FROM contest WHERE id = ?");
$stmt->execute([$contestId]);
$contest = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contest) die("Contest not found.");

// 1. Scalers
$stmtProb = $db->prepare("SELECT COUNT(*) FROM contest_problem WHERE contest_id = ?");
$stmtProb->execute([$contestId]);
$problemCount = $stmtProb->fetchColumn();

// 2. Timeline Range
$stmtLine = $db->prepare("SELECT id, student_id, problem_id, status FROM submission WHERE contest_id = ? ORDER BY id ASC");
$stmtLine->execute([$contestId]);
$allSubs = $stmtLine->fetchAll(PDO::FETCH_ASSOC);

$minId = PHP_INT_MAX; $maxId = 0;
foreach ($allSubs as $sub) {
    $minId = min($minId, $sub['id']); 
    $maxId = max($maxId, $sub['id']);
}
$idRange = max(1, $maxId - $minId);

// 3. Process Final States
$studentFinalState = [];
$acceptedTracker = [];

foreach ($allSubs as $sub) {
    $sid = $sub['student_id'];
    $pid = $sub['problem_id'];

    if (!isset($studentFinalState[$sid])) {
        $studentFinalState[$sid] = ['solved' => 0, 'last_id' => $minId];
    }

    if ($sub['status'] === 'Accepted' && !isset($acceptedTracker[$sid][$pid])) {
        $acceptedTracker[$sid][$pid] = true;
        $studentFinalState[$sid]['solved']++;
        $studentFinalState[$sid]['last_id'] = $sub['id'];
    }
}

// 4. Labels
$stmtS = $db->prepare("SELECT id, student_id FROM student");
$stmtS->execute();
$idMap = $stmtS->fetchAll(PDO::FETCH_KEY_PAIR);

// SVG Layout
$svgWidth = 1200;
$baseHeight = 100;
$svgHeight = ($problemCount * $baseHeight) + 100;
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>The Mountain - Summit</title>
</head>
<body class="bg-[#1a1a1a] text-white p-10 font-sans">

    <div class="max-w-[1400px] mx-auto">
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold">The Mountain Summit: <?= htmlspecialchars($contest['name']) ?></h1>
            <a href="standings.php?contestID=<?= $contestId ?>&course=<?= $courseName ?>" class="text-sm bg-gray-800 px-4 py-2 rounded hover:bg-gray-700 transition border border-gray-600">Standings</a>
        </div>

        <div class="bg-[#282828] border border-gray-700 rounded-2xl p-12 shadow-2xl relative">
            <svg viewBox="-50 -50 <?= $svgWidth + 100 ?> <?= $svgHeight + 100 ?>" class="w-full h-auto overflow-visible">
                
                <?php for ($i = 0; $i <= $problemCount; $i++): 
                    $y = ($problemCount - $i) * $baseHeight;
                ?>
                    <line x1="0" y1="<?= $y ?>" x2="<?= $svgWidth ?>" y2="<?= $y ?>" stroke="#374151" stroke-width="1" stroke-dasharray="8" />
                    <text x="-20" y="<?= $y + 5 ?>" fill="#9ca3af" font-size="14" text-anchor="end" font-weight="bold"><?= $i ?> Solved</text>
                <?php endfor; ?>

                <line x1="0" y1="<?= $problemCount * $baseHeight ?>" x2="<?= $svgWidth ?>" y2="<?= $problemCount * $baseHeight ?>" stroke="#4b5563" stroke-width="3" />

                <?php foreach ($studentFinalState as $dbId => $data): 
                    // X = Chronological Progress (Left to Right)
                    $x = (($data['last_id'] - $minId) / $idRange) * $svgWidth;
                    
                    // Y = Solve Level + Penalty (Earlier finish = Higher position)
                    $penalty = (($data['last_id'] - $minId) / $idRange) * ($baseHeight * 0.7);
                    $y = (($problemCount - $data['solved']) * $baseHeight) + $penalty;
                    
                    $studentLabel = $idMap[$dbId] ?? "User $dbId";
                    $isTopTier = ($data['solved'] == $problemCount && $problemCount > 0);
                    $dotColor = $isTopTier ? '#ffa116' : '#2cbb5d';
                ?>
                    <g class="cursor-help transition-all duration-300 hover:opacity-80">
                        <circle cx="<?= $x ?>" cy="<?= $y ?>" r="7" fill="<?= $dotColor ?>" stroke="#1a1a1a" stroke-width="2" />
                        <text x="<?= $x ?>" y="<?= $y - 15 ?>" fill="white" font-size="12" font-family="monospace" text-anchor="middle" font-weight="bold">
                            <?= $studentLabel ?>
                        </text>
                        <title><?= $studentLabel ?>: Solved <?= $data['solved'] ?></title>
                    </g>
                <?php endforeach; ?>

                <text x="0" y="<?= ($problemCount * $baseHeight) + 40 ?>" fill="#9ca3af" font-size="12" font-family="monospace">CONTEST START</text>
                <text x="<?= $svgWidth ?>" y="<?= ($problemCount * $baseHeight) + 40 ?>" fill="#9ca3af" font-size="12" font-family="monospace" text-anchor="end">CONTEST END</text>
            </svg>
        </div>
        
        <div class="mt-10 grid grid-cols-2 gap-4 text-sm text-gray-400">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-brand-green"></div>
                <span>Student currently at this solved level.</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-brand-orange"></div>
                <span>Student completed all problems (Winner).</span>
            </div>
        </div>
    </div>

</body>
</html>