<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// 1. Backend Logic
require_once 'auth.php';    
require_once 'problem.php';
require_once 'contest.php';

// Check for 'code' in URL
if (!isset($_GET['code'])) {
    die("Error: No problem code specified in URL.");
}

$problemCode = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['code']);
$problem = Problem::getByCode($problemCode);


$courseCode = isset($_GET['course']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['course']) : '';

if (!$problem) {
    die("Error: Problem code '{$problemCode}' not found.");
}

// Get Contest Context (Optional but recommended)
$contestName = isset($_GET['contest']) ? preg_replace('/[^a-zA-Z0-9_\- ]/', '', urldecode($_GET['contest'])) : null;

$contestId = null;
$antiCheatEnabled = 0;

if ($contestName) {
    $contestObj = Contest::getByNameAndCourse($contestName,$courseCode);
    if ($contestObj) {
        $contestId = $contestObj['id'];
        $antiCheatEnabled = isset($contestObj['anti_cheat']) ? (int)$contestObj['anti_cheat'] : 0;
    }
}

// Extract Data
$fileCode = $problem['code'];
$problemId= $problem['id'];
$problemTitle = $problem['title'];
$problemLevel = $problem['level'];
$problemForbidden_keyword = $problem['forbidden_keyword'];
$problemInputType = isset($problem['input_type']) ? $problem['input_type'] : 'arg';

// --- Load Files Server-Side ---
$descPath = "problemset/".$fileCode."/" . $fileCode . ".html";
$pyPath   = "problemset/".$fileCode."/"  . $fileCode . ".py";
$templatePyPath   = "problemset/".$fileCode."/"  . $fileCode . "_Template.py";

// Read Description
if (file_exists($descPath)) {
    $descContent = file_get_contents($descPath);
} else {
    $descContent = "<div class='text-brand-red p-4'>Description file (<b>$fileCode.html</b>) not found.</div>";
}

// Read Description
if (file_exists($templatePyPath)) {
    $templateContent = file_get_contents($templatePyPath);
} else {
    $templateContent = "def solve():";
}


// Read Python Grader Code
if (file_exists($pyPath)) {
    $graderContent = file_get_contents($pyPath);
} else {
    $graderContent = "";
}

// --- NEW LOGIC: Load Previous Submission ---
$submittedContent = ""; // Default empty

if (isset($_SESSION['student_id']) && $contestName) {
    $studentId = $_SESSION['student_id'];

    // Sanitize Names for Path
    $safeContest = preg_replace('/[^a-zA-Z0-9_\- ]/', '', $contestName);
    $safeContest = trim($safeContest);
    $safeStudent = preg_replace('/[^a-zA-Z0-9_\-]/', '', $studentId);

    // Path: contest_upload/[ContestName]/[StudentID]/[ProblemCode].py
    $pySubmittedPath = "contest_upload/" .$courseCode ."_". $safeContest . "/" . $safeStudent . "/" . $safeStudent ."_". $fileCode . ".py";

    if (file_exists($pySubmittedPath)) {
        $submittedContent = file_get_contents($pySubmittedPath);
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($problemTitle) ?> - GCIOJ IDE</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/skulpt@1.2.0/dist/skulpt.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/skulpt@1.2.0/dist/skulpt-stdlib.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', hover: '#3e3e3e', text: '#eff1f6', muted: '#9ca3af' },
                        brand: { orange: '#ffa116', green: '#2cbb5d', red: '#ef4444', blue: '#00CED1' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'], mono: ['Roboto Mono', 'monospace'] }
                }
            }
        }
    </script>
    <style>
        .resizer-vertical { width: 6px; background: #1a1a1a; cursor: col-resize; z-index: 10; border-left: 1px solid #374151; border-right: 1px solid #374151; }
        .resizer-horizontal { height: 6px; background: #1a1a1a; cursor: row-resize; z-index: 10; border-top: 1px solid #374151; border-bottom: 1px solid #374151; width: 100%; }
        .resizer-vertical:hover, .resizer-horizontal:hover { background: #ffa116; border-color: #ffa116; }
        .skulpt-pass { color: #0f0; font-weight: 600; }
        .skulpt-fail { color: #ef4444; font-weight: 600; }
        .skulpt-header { color: #9ca3af; margin-top: 10px; display: block; border-top: 1px solid #374151; padding-top: 10px;}
        .diff-Easy { color: #2cbb5d; background: rgba(44, 187, 93, 0.15); }
        .diff-Medium { color: #ffc01e; background: rgba(255, 192, 30, 0.15); }
        .diff-Hard { color: #ef4444; background: rgba(239, 68, 68, 0.15); }
        code {
    color: #00FFFF; /* Cyan */
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
}
    #output {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #0f0; /* Classic Green Terminal Text */
            background-color: #1e1e1e;
        }
    </style>


</head>
<body class="bg-dark-bg text-dark-text font-sans h-screen flex flex-col overflow-hidden">

<?php include_once 'nav.php'; ?>
<?php if ($antiCheatEnabled): ?>
<div class="bg-yellow-500/10 border-b border-yellow-500/30 text-yellow-500 px-4 py-2 text-sm flex items-center justify-center gap-2 w-full">
    <i class="fas fa-shield-alt"></i>
    <span><strong>考試監控中 | 防作弊系統已啟動：</strong>測驗期間請勿切換分頁或離開瀏覽器，系統將全程監控異常行為。</span>
</div>
<?php endif; ?>

<div class="flex flex-grow overflow-hidden relative w-full">

    <div id="problem-panel" class="bg-dark-surface border-r border-gray-700 flex flex-col w-[35%] min-w-[250px]">
        <div class="px-5 py-3 border-b border-gray-700 flex justify-between items-center bg-dark-surface shrink-0">
            <?php if($contestName): ?>
            <a href="viewcontest.php?name=<?= urlencode($contestName) ?>&course=<?= urlencode($courseCode) ?>"  class="text-sm text-dark-muted hover:text-white flex items-center gap-1 transition">&larr; Contest</a>
            <?php else: ?>
                <a href="problemset.php" class="text-sm text-dark-muted hover:text-white flex items-center gap-1 transition">&larr; Problems</a>
            <?php endif; ?>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium diff-<?= htmlspecialchars($problemLevel) ?>"><?= htmlspecialchars($problemLevel) ?></span>
        </div>
        <div id="problem-desc-container" class="flex-grow overflow-y-auto p-6 text-sm leading-relaxed space-y-4">
            <h1 class="text-2xl font-bold text-white mb-4"><?= htmlspecialchars($problemTitle) ?></h1>
            <div class="prose prose-invert max-w-none text-dark-text text-sm select-none"><?= $descContent ?></div>
        </div>
    </div>

    <div class="resizer-vertical" id="drag-v"></div>

    <div id="ide-container" class="flex-grow flex flex-col min-w-[400px] bg-dark-surface">

        <div class="h-12 border-b border-gray-700 flex justify-between items-center px-4 bg-dark-surface shrink-0">
            <div class="font-mono text-sm text-dark-muted flex items-center gap-2">
    <span>Python 3</span>
    <span id="cheat-warning" class="hidden ml-3 bg-red-500/20 text-red-500 px-2 py-0.5 rounded text-xs font-bold border border-red-500/50 flex items-center gap-1">
    <i class="fas fa-exclamation-triangle text-red-500"></i> 違規切換次數：<span id="cheat-count-display">0</span>
</span>
</div>

            <div class="flex gap-2">
                <button onclick="resetCode()" class="bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold py-1.5 px-3 rounded transition flex items-center gap-2 border border-gray-600">
                   <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74-2.74L3 12"></path></svg>
                   Reset Code
                </button>

                <button id="btn-run" onclick="runCode()" class="bg-brand-green hover:bg-green-600 text-white text-xs font-bold py-1.5 px-4 rounded transition flex items-center gap-2 shadow-lg shadow-green-500/20">
                   <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                   Run
                </button>


                <button id="btn-submit" onclick="submitCode()" class="bg-brand-orange hover:bg-orange-600 text-white text-xs font-bold py-1.5 px-4 rounded transition flex items-center gap-2 shadow-lg shadow-orange-500/20">
               <i class="fas fa-upload text-[10px]"></i>
               Submit
            </button>
            </div>
        </div>

        <div id="editor-wrapper" class="relative h-[60%] min-h-[100px]">
            <div id="editor" class="w-full h-full text-sm font-mono"></div>
        </div>

        <div class="resizer-horizontal" id="drag-h"></div>

        <div id="console-panel" class="flex-grow flex flex-col bg-dark-surface min-h-[100px]">
            <div class="flex border-b border-gray-700 bg-dark-hover/30 shrink-0">
                <div class="px-4 py-2 text-xs font-medium text-white border-b-2 border-brand-orange">Terminal | Screen | Console Output</div>
            </div>
            <div id="output" class="flex-grow p-4 font-mono text-xs overflow-y-auto whitespace-pre-wrap text-dark-text bg-[#1e1e1e]">
                <span class="text-dark-muted">Click 'Run' to see results...</span>
            </div>
        </div>
    </div>
</div>

<script>
    // --- 0. ANTI-CHEAT TRACKER ---
    const isAntiCheatEnabled = <?= json_encode((bool)$antiCheatEnabled) ?>;
    const isContestSession = <?= json_encode((bool)$contestId) ?>;
    const LS_KEY = 'gcioj_cheat_count';
    
    let cheatCount = 0;
    if (isContestSession) {
        if (isAntiCheatEnabled) {
            cheatCount = parseInt(localStorage.getItem(LS_KEY) || "0");
        } else {
            localStorage.removeItem(LS_KEY); // Reset when opening a non-anti-cheat contest
        }
    }

    let isNavigating = false;
    window.addEventListener('beforeunload', () => { isNavigating = true; });
    document.addEventListener('click', (e) => {
        if (e.target.closest('a')) isNavigating = true;
    });

    let lastCheatTime = 0;
    const cheatWarning = document.getElementById('cheat-warning');
    const cheatCounter = document.getElementById('cheat-count-display');

    // Init UI if count exists
    if (cheatCount > 0 && isAntiCheatEnabled) {
        cheatWarning.classList.remove('hidden');
        cheatCounter.innerText = cheatCount;
    }

    function handleCheat() {
        if (!isAntiCheatEnabled || isNavigating) return;

        const now = Date.now();
        if (now - lastCheatTime < 1000) return; // 1-second debounce
        lastCheatTime = now;
        
        cheatCount++;
        localStorage.setItem(LS_KEY, cheatCount);
        cheatWarning.classList.remove('hidden');
        cheatCounter.innerText = cheatCount;
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            handleCheat();
        } else {
            isNavigating = false; // Reset flag when user returns to page
        }
    });

    window.addEventListener('blur', () => {
        handleCheat();
    });

    window.addEventListener('focus', () => {
        isNavigating = false; // Reset flag when window regains focus
    });

    // --- 1. CONFIG & CACHED ELEMENTS ---
    const config = {
        teacherCode: <?= json_encode($graderContent) ?>,
        problemCode: <?= json_encode($fileCode) ?>,
        problemId: <?= json_encode($problemId) ?>,
        inputType: <?= json_encode($problemInputType) ?>,
        contestId: <?= json_encode($contestId) ?>,
        courseCode: <?= json_encode($courseCode) ?>,
        contestName: <?= json_encode($contestName) ?>,
        forbidden: <?= json_encode($problemForbidden_keyword) ?> || ""
    };

    let submittedCode = <?= json_encode($submittedContent) ?>;
    const defaultCode = <?= json_encode($templateContent ?? "def solve():\n    pass") ?>;

    const el = {
        terminal: document.getElementById("output"),
        btnRun: document.getElementById("btn-run"),
        btnSubmit: document.getElementById("btn-submit"),
        problemPanel: document.getElementById('problem-panel'),
        ideContainer: document.getElementById('ide-container'),
        editorWrapper: document.getElementById('editor-wrapper'),
        consolePanel: document.getElementById('console-panel')
    };

    // --- 2. EDITOR SETUP ---
    const editor = ace.edit("editor");
    editor.setTheme("ace/theme/twilight");
    editor.session.setMode("ace/mode/python");
    editor.setFontSize(14);
    editor.setShowPrintMargin(false);
    editor.setValue(submittedCode?.trim() ? submittedCode : defaultCode, -1);

    // --- 3. UTILITIES ---
    
    function updateTerminal(text, isAppend = true) {
        if (!isAppend) el.terminal.innerHTML = '';
        
        // Split text by lines to style them individually
        const lines = text.split('\n');
        
        lines.forEach(line => {
            if (line.trim() === "") return;
    
            const div = document.createElement("div");
            div.innerText = line;
            
            // 1. Red for Errors and "Wrong Answer"
            if (/Wrong Answer|WRONG|Error|Exception|failed/.test(line)) {
                div.className = "skulpt-fail"; 
            } 
            // 2. Green for Success and "Pass" counts
            else if (/Accepted|PASS|Pass:/.test(line)) {
                div.className = "skulpt-pass";
            }
            // 3. Gray/Orange for headers
            else if (line.includes("---") || line.includes("Test 1")) {
                div.className = "skulpt-header";
            }
    
            el.terminal.appendChild(div);
        });
        
        el.terminal.scrollTop = el.terminal.scrollHeight;
    }

    function validateCode(code) {
        // Recursive check
        if (config.forbidden.trim() !== "") {
            const keywords = config.forbidden.split(',').map(s => s.trim()).filter(Boolean).join('|');
            if (new RegExp(`\\b(${keywords})\\b`).test(code)) {
                return "Error: you must use recursive function to solve this problem";
            }
        }
        // Built-in check
        const forbiddenBuiltins = code.match(/\b(sum|min|max|sort|lambda|set|replace)\s*\(/);
        if (forbiddenBuiltins) {
            return `Error: Usage of built-in function '${forbiddenBuiltins[1]}()' is not allowed.`;
        }
        return null;
    }

    // --- 4. CORE EXECUTION ---
    let isExecuting = false; // Guard against concurrent execution

    async function executePython(isSubmit = false) {
        if (isExecuting) return; // Prevent double-run / double-submit
        isExecuting = true;

        const studentCode = editor.getValue();
        const error = validateCode(studentCode);

        if (error) {
            updateTerminal(error, false);
            if (isSubmit) submitResult(studentCode, `\nCompile Error\nRuntime Error: ${error}`);
            isExecuting = false;
            return;
        }

        // UI Feedback: Disable both buttons
        const originalRunText = el.btnRun.innerHTML;
        const originalSubmitText = el.btnSubmit.innerHTML;
        el.btnRun.disabled = true;
        el.btnSubmit.disabled = true;
        if (isSubmit) {
            el.btnSubmit.innerHTML = 'Submitting...';
        } else {
            el.btnRun.innerHTML = 'Running...';
        }
        el.terminal.innerHTML = '';

        const finalProgram = isSubmit 
            ? `
import sys
def __teacher_print(*args, **kwargs):
    sys.stdout.write(' '.join(map(str, args)) + '\\n')
def print(*args, **kwargs):
    pass\n` + studentCode + `\n` + config.teacherCode.replace(/\bprint\s*\(/g, '__teacher_print(')
            : studentCode;

        let fullOutput = "";
        let hasError = false;

        // 1. Silent Pre-run to determine outcome
        Sk.configure({
            output: (text) => { fullOutput += text; },
            read: (x) => {
                if (!Sk.builtinFiles?.files[x]) throw `File not found: '${x}'`;
                return Sk.builtinFiles.files[x];
            }
        });

        try {
            await Sk.misceval.asyncToPromise(() => Sk.importMainWithBody("<stdin>", false, finalProgram, true));
            if (fullOutput.includes("WRONG") || fullOutput.includes("Wrong Answer") || fullOutput.includes("FAIL")) {
                hasError = true;
            }
        } catch (err) {
            hasError = true;
            fullOutput += `\nRuntime Error: ${err.toString()}`;
        }
        
        if (isSubmit) {
            const runCount = 30; 
            const stopAt = hasError ? Math.floor(Math.random() * Math.min(runCount, 19)) + 1 : runCount;
            const totalWaitTime = Math.floor(Math.random() * 2000) + 3000; 
            const interval = totalWaitTime / stopAt;
        
            for (let i = 1; i <= stopAt; i++) {
                el.terminal.innerHTML = `<div class="text-brand-orange">🔍 Checking Test Case ${i}...</div>`;
                await new Promise(resolve => setTimeout(resolve, interval));
            }
        }
        
        // Display Final Results
        el.terminal.innerHTML = ''; 
        updateTerminal(fullOutput, true);

        if (isSubmit) submitResult(studentCode, fullOutput);

        // Restore buttons
        el.btnRun.innerHTML = originalRunText;
        el.btnSubmit.innerHTML = originalSubmitText;
        el.btnRun.disabled = false;
        el.btnSubmit.disabled = false;
        isExecuting = false;
    }

    // --- 5. API HANDLERS ---
    function submitResult(code, output) {
        if (code === submittedCode) { console.log("Code is unchanged. Submission canceled."); return; }
        if (!config.contestId) return;

        fetch('submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                code, output,
                problem_code: config.problemCode,
                contest_id: config.contestId,
                course_code: config.courseCode,
                problem_id: config.problemId,
                contest_name: config.contestName,
                cheat_count: cheatCount
            })
        })
        .then(res => res.json())
        .then(data => { submittedCode = code; console.log("Saved to DB:", data); })
        .catch(err => console.error('Submit Error:', err));
    }

    // --- 6. EVENT LISTENERS ---
    window.runCode = () => executePython(false);
    window.submitCode = () => executePython(true);
    window.resetCode = () => {
        if(confirm("Reset to default?")) editor.setValue(defaultCode, -1);
    };

    // --- 7. SECURITY & RESIZING ---
    // (Resizer logic remains standard as requested, but uses the 'el' cache)
    
    // --- 7. RESIZERS & SECURITY ---
    // (Existing resizer logic kept intact)
    const resizerV = document.getElementById('drag-v');
    const leftPanel = document.getElementById('problem-panel');
    const rightContainer = document.getElementById('ide-container');
    let isResizingV = false;
    resizerV.addEventListener('mousedown', function(e) { isResizingV = true; document.body.style.cursor = 'col-resize'; leftPanel.style.userSelect = 'none'; rightContainer.style.userSelect = 'none'; editor.container.style.pointerEvents = 'none'; });
    const resizerH = document.getElementById('drag-h');
    const editorWrapper = document.getElementById('editor-wrapper');
    const consolePanel = document.getElementById('console-panel');
    let isResizingH = false;
    resizerH.addEventListener('mousedown', function(e) { isResizingH = true; document.body.style.cursor = 'row-resize'; editorWrapper.style.userSelect = 'none'; consolePanel.style.userSelect = 'none'; editor.container.style.pointerEvents = 'none'; });
    let rafV;
    let rafH;
    document.addEventListener('mousemove', function(e) {
        if (isResizingV) {
            if (rafV) cancelAnimationFrame(rafV);
            rafV = requestAnimationFrame(() => {
                let newWidth = (e.clientX / window.innerWidth) * 100;
                if (newWidth > 15 && newWidth < 85) { leftPanel.style.width = newWidth + '%'; }
                editor.resize();
            });
        }
        if (isResizingH) {
            if (rafH) cancelAnimationFrame(rafH);
            rafH = requestAnimationFrame(() => {
                const ideRect = document.getElementById('ide-container').getBoundingClientRect();
                const containerHeight = ideRect.height;
                const relativeY = e.clientY - ideRect.top;
                let newHeightPercentage = (relativeY / containerHeight) * 100;
                 if (newHeightPercentage > 20 && newHeightPercentage < 85) { editorWrapper.style.height = newHeightPercentage + '%'; }
                 editor.resize();
            });
        }
    });
    document.addEventListener('mouseup', function(e) {
        if (isResizingV || isResizingH) {
            isResizingV = false; isResizingH = false; document.body.style.cursor = 'default';
            leftPanel.style.userSelect = 'auto'; rightContainer.style.userSelect = 'auto';
            editorWrapper.style.userSelect = 'auto'; consolePanel.style.userSelect = 'auto';
            editor.container.style.pointerEvents = 'auto';
        }
    });
    
    // 1. Disable internal Ace commands for Copy, Cut, and Paste
editor.commands.addCommand({
    name: "disableClipboard",
    bindKey: {
        win: "Ctrl-C|Ctrl-X|Ctrl-V",
        mac: "Command-C|Command-X|Command-V"
    },
    exec: () => { 
        console.log("Clipboard action blocked.");
        return true; 
    },
    readOnly: false 
});

// 2. Disable browser-level events on the editor container
const editorEl = document.getElementById('editor');

['copy', 'cut', 'paste'].forEach(eventType => {
    editorEl.addEventListener(eventType, e => {
        e.preventDefault();
        e.stopPropagation();
        if (eventType === 'paste') alert("Pasting is disabled.");
    }, true);
});

// 3. Disable right-click specifically on the editor to prevent 'Paste' via menu
document.addEventListener('contextmenu', event => event.preventDefault());
</script>

</body>
</html>