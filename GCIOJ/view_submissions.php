<?php
// view_submissions.php
session_start();
require_once 'check_admin.php'; 
require_once 'db.php';
require_once 'contest.php';
require_once 'problem.php';

$db = DB::connect();

// 1. Get List of Contests
$contests = Contest::getAll();

// --- Rule: Default to first items if none selected (Step 322) ---
$selectedContestId = isset($_GET['contest_id']) ? (int)$_GET['contest_id'] : 0;
if ($selectedContestId === 0 && !empty($contests)) {
    $selectedContestId = (int)$contests[0]['id'];
}

$selectedStudentId = isset($_GET['student_id']) ? $_GET['student_id'] : '';

$selectedContest = null;
$students = [];
$problems = [];
$submissionsArr = [];

if ($selectedContestId > 0) {
    $selectedContest = Contest::getById($selectedContestId);
    
    // 2. Get Students WHO SUBMITTED to this Contest (Rule: Step 328)
    $stmt = $db->prepare("
        SELECT DISTINCT s.id as db_id, s.student_id, s.name
        FROM student s
        JOIN submission sub ON s.id = sub.student_id
        WHERE sub.contest_id = ?
        ORDER BY s.student_id ASC
    ");
    $stmt->execute([$selectedContestId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Rule: Default to first student if none selected (Step 322) ---
    if (empty($selectedStudentId) && !empty($students)) {
        $selectedStudentId = $students[0]['student_id'];
    }

    // 3. Get Problems for this Contest
    $problems = Contest::getProblems($selectedContestId);
    
    // 4. If student is selected, load their code files
    if ($selectedStudentId) {
        $courseCodeRaw = $selectedContest['course'];
        $contestNameRaw = $selectedContest['name'];
        $courseCode = preg_replace('/[^a-zA-Z0-9_\-]/', '', $courseCodeRaw);
        $contestName = preg_replace('/[^a-zA-Z0-9_\- ]/', '', $contestNameRaw);

        foreach ($problems as $p) {
            $problemCode = $p['code'];
            $dirPath = "contest_upload/{$courseCode}_{$contestName}/{$selectedStudentId}";
            $filePath = "{$dirPath}/{$selectedStudentId}_{$problemCode}.py";
            
            $code = "";
            if (file_exists($filePath)) {
                $code = file_get_contents($filePath);
            }
            
            $submissionsArr[] = [
                'problem_id' => $p['id'],
                'problem_title' => $p['title'],
                'problem_code' => $problemCode,
                'code' => $code,
                'file_exists' => file_exists($filePath)
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Review: <?= $selectedStudentId ?: 'Admin Submissions' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Ace Editor -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
    
    <!-- Skulpt -->
    <script src="https://cdn.jsdelivr.net/npm/skulpt@1.2.0/dist/skulpt.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/skulpt@1.2.0/dist/skulpt-stdlib.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#0f0f12', sidebar: '#16161a', surface: '#1e1e24', hover: '#2a2a33', text: '#eff1f6', muted: '#9ca3af' },
                        brand: { orange: '#ffa116', green: '#2cbb5d', red: '#ef4444' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'], mono: ['Roboto Mono', 'monospace'] }
                }
            }
        }
    </script>
    <style>
        .ace_editor { border-radius: 8px; border: 1px solid #374151; }
        .output-div { font-family: 'Roboto Mono', monospace; min-height: 80px; max-height: 200px; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
        ::-webkit-scrollbar-track { background: transparent; }
    </style>
</head>
<body class="bg-dark-bg text-dark-text font-sans h-screen flex flex-col overflow-hidden">

    <?php include_once 'nav.php'; ?>

    <div class="flex flex-grow overflow-hidden">
        
        <!-- SIDEBAR 1: CONTESTS -->
        <aside class="w-64 bg-dark-sidebar border-r border-gray-800 flex flex-col shrink-0">
            <div class="p-4 border-b border-gray-800 flex items-center gap-2">
                <i class="fas fa-trophy text-brand-orange text-xs"></i>
                <h2 class="text-[10px] font-bold text-dark-muted uppercase tracking-[0.2em]">Select Contest</h2>
            </div>
            <div class="flex-grow overflow-y-auto pt-2">
                <?php foreach ($contests as $c): ?>
                    <a href="?contest_id=<?= $c['id'] ?>" 
                       class="block px-4 py-3 text-sm transition-all <?= $selectedContestId == $c['id'] ? 'bg-brand-orange text-black font-extrabold shadow-lg shadow-orange-500/20' : 'text-dark-muted hover:bg-dark-hover hover:text-white' ?>">
                        <div class="truncate"><?= htmlspecialchars($c['name']) ?></div>
                        <div class="text-[10px] opacity-70 font-mono"><?= htmlspecialchars($c['course'] ?: 'UNSET') ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>

        <!-- SIDEBAR 2: STUDENTS -->
        <aside class="w-72 bg-dark-sidebar/50 border-r border-gray-800 flex flex-col shrink-0">
            <div class="p-4 border-b border-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-users text-brand-green text-xs"></i>
                    <h2 class="text-[10px] font-bold text-dark-muted uppercase tracking-[0.2em]">Select Student</h2>
                </div>
                <span class="text-[10px] text-dark-muted font-bold"><?= count($students) ?></span>
            </div>
            <div class="flex-grow overflow-y-auto pt-2">
                <?php if ($selectedContestId): ?>
                    <?php foreach ($students as $s): ?>
                        <a href="?contest_id=<?= $selectedContestId ?>&student_id=<?= $s['student_id'] ?>" 
                           class="block px-4 py-3 text-sm transition-all group <?= $selectedStudentId == $s['student_id'] ? 'bg-dark-hover border-l-4 border-brand-orange' : 'hover:bg-dark-hover/50' ?>">
                            <div class="flex justify-between items-center mb-0.5">
                                <span class="font-bold font-mono <?= $selectedStudentId == $s['student_id'] ? 'text-brand-orange' : 'text-gray-300' ?>">
                                    <?= htmlspecialchars($s['student_id']) ?>
                                </span>
                            </div>
                            <div class="text-[11px] <?= $selectedStudentId == $s['student_id'] ? 'text-white' : 'text-dark-muted' ?> truncate">
                                <?= htmlspecialchars($s['name']) ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-6 text-center text-dark-muted italic text-xs">Pick a contest first...</div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- MAIN PANEL: SUBMISSIONS -->
        <main class="flex-grow bg-dark-bg p-8 overflow-y-auto relative">
            <?php if ($selectedStudentId): ?>
                <div class="mb-10 flex justify-between items-end border-b border-gray-800 pb-6">
                    <div>
                        <span class="text-[10px] font-bold text-brand-orange uppercase tracking-widest mb-1 block">Reviewing Submissions for</span>
                        <h1 class="text-3xl font-bold text-white"><?= htmlspecialchars($selectedStudentId) ?> <span class="text-dark-muted text-xl font-normal ml-2">/ <?= htmlspecialchars($students[array_search($selectedStudentId, array_column($students, 'student_id'))]['name'] ?? '') ?></span></h1>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-dark-muted">Contest: <span class="text-white font-bold"><?= htmlspecialchars($selectedContest['name']) ?></span></div>
                        <div class="text-xs text-dark-muted">Course: <span class="text-white font-bold"><?= htmlspecialchars($selectedContest['course']) ?></span></div>
                    </div>
                </div>

                <div class="space-y-12">
                    <?php foreach ($submissionsArr as $sub): ?>
                        <div class="bg-dark-surface border border-gray-800 rounded-2xl overflow-hidden shadow-2xl transition hover:border-gray-700">
                            <!-- Problem Header -->
                            <div class="px-6 py-4 bg-gray-900 flex justify-between items-center">
                                <div>
                                    <h2 class="text-lg font-bold text-white"><?= htmlspecialchars($sub['problem_title']) ?></h2>
                                    <p class="text-[10px] text-dark-muted font-mono uppercase tracking-tight"><?= htmlspecialchars($sub['problem_code']) ?></p>
                                </div>
                                <?php if ($sub['file_exists']): ?>
                                    <button onclick="runCode('<?= $sub['problem_code'] ?>')" 
                                            class="bg-brand-green hover:bg-green-600 text-white px-5 py-2 rounded-lg font-bold text-xs transition flex items-center gap-2 shadow-lg shadow-green-500/20 active:scale-95">
                                        <i class="fas fa-play"></i> Run Code
                                    </button>
                                <?php else: ?>
                                    <span class="text-dark-muted text-[10px] font-bold bg-dark-bg px-3 py-1.5 rounded-full border border-gray-800 italic">
                                        <i class="fas fa-ban mr-1"></i> No Submission
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Content Grid -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-px bg-gray-800">
                                <div class="bg-dark-surface p-6">
                                    <label class="text-[9px] font-bold text-dark-muted uppercase tracking-[0.2em] mb-3 block">Source (Ace)</label>
                                    <div id="editor-<?= $sub['problem_code'] ?>" class="h-[400px] w-full"><?= htmlspecialchars($sub['code']) ?></div>
                                </div>
                                
                                <div class="bg-dark-surface p-6 flex flex-col">
                                    <label class="text-[9px] font-bold text-dark-muted uppercase tracking-[0.2em] mb-3 block">Output (Skulpt)</label>
                                    <div id="output-<?= $sub['problem_code'] ?>" 
                                         class="output-div flex-grow bg-black p-4 text-brand-green text-xs overflow-y-auto border border-gray-950 rounded-lg whitespace-pre-wrap leading-relaxed">
                                        <span class="text-gray-800"># Waiting for execution...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="h-full flex flex-col items-center justify-center text-center">
                    <div class="w-20 h-20 bg-dark-surface rounded-full flex items-center justify-center mb-6 text-dark-muted text-3xl opacity-20">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 tracking-tight">No Selection</h3>
                    <p class="text-dark-muted text-sm max-w-xs leading-relaxed">Please choose a contest and student from the sidebar to review submissions.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        const editors = {};
        document.addEventListener("DOMContentLoaded", function() {
            <?php foreach ($submissionsArr as $sub): ?>
                <?php if ($sub['file_exists']): ?>
                    (function() {
                        const pId = "<?= $sub['problem_code'] ?>";
                        const ed = ace.edit("editor-" + pId);
                        ed.setTheme("ace/theme/twilight");
                        ed.session.setMode("ace/mode/python");
                        ed.setReadOnly(true);
                        ed.setOptions({
                            fontSize: "13px",
                            showPrintMargin: false,
                            highlightActiveLine: false,
                            highlightGutterLine: false,
                            useSoftTabs: true,
                            tabSize: 4
                        });
                        editors[pId] = ed;
                    })();
                <?php endif; ?>
            <?php endforeach; ?>
        });

        function runCode(pId) {
            const code = editors[pId].getValue();
            const outputEl = document.getElementById("output-" + pId);
            outputEl.innerHTML = '';
            
            Sk.pre = "output-" + pId;
            Sk.configure({
                output: function(text) {
                    const node = document.createElement("span");
                    node.innerText = text;
                    outputEl.appendChild(node);
                    outputEl.scrollTop = outputEl.scrollHeight;
                },
                read: function(x) {
                    if (Sk.builtinFiles === undefined || Sk.builtinFiles["files"][x] === undefined)
                        throw "File not found: '" + x + "'";
                    return Sk.builtinFiles["files"][x];
                }
            });

            Sk.misceval.asyncToPromise(function() {
                return Sk.importMainWithBody("<stdin>", false, code, true);
            }).then(null, function(err) {
                const node = document.createElement("span");
                node.className = "text-brand-red font-bold";
                node.innerText = "\n" + err.toString();
                outputEl.appendChild(node);
                outputEl.scrollTop = outputEl.scrollHeight;
            });
        }
    </script>
</body>
</html>
