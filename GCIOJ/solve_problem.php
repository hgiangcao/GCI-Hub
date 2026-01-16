<?php
// 1. Backend Logic
require_once 'auth.php';    
require_once 'problem.php';
require_once 'contest.php';

// Check for 'code' in URL
if (!isset($_GET['code'])) {
    die("Error: No problem code specified in URL.");
}

$problemCode = $_GET['code'];
$problem = Problem::getByCode($problemCode);


$courseCode = $_GET['course'];

if (!$problem) {
    die("Error: Problem code '{$problemCode}' not found.");
}

// Get Contest Context (Optional but recommended)
$contestName = isset($_GET['contest']) ? urldecode($_GET['contest']) : null;

$contestId = null;

if ($contestName) {
    $contestObj = Contest::getByNameAndCourse($contestName,$courseCode);
    if ($contestObj) $contestId = $contestObj['id'];
}

// Extract Data
$fileCode = $problem['code'];
$problemId= $problem['id'];
$problemTitle = $problem['title'];
$problemLevel = $problem['level'];
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
                        brand: { orange: '#ffa116', green: '#2cbb5d', red: '#ef4444' }
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
        .skulpt-pass { color: #2cbb5d; font-weight: 600; }
        .skulpt-fail { color: #ef4444; font-weight: 600; }
        .skulpt-header { color: #9ca3af; margin-top: 10px; display: block; border-top: 1px solid #374151; padding-top: 10px;}
        .diff-Easy { color: #2cbb5d; background: rgba(44, 187, 93, 0.15); }
        .diff-Medium { color: #ffc01e; background: rgba(255, 192, 30, 0.15); }
        .diff-Hard { color: #ef4444; background: rgba(239, 68, 68, 0.15); }
        code {
    color: #00FFFF; /* Cyan */
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
}
    </style>
</head>
<body class="bg-dark-bg text-dark-text font-sans h-screen flex flex-col overflow-hidden">

<?php include_once 'nav.php'; ?>

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
            <div class="prose prose-invert max-w-none text-dark-text text-sm"><?= $descContent ?></div>
        </div>
    </div>

    <div class="resizer-vertical" id="drag-v"></div>

    <div id="ide-container" class="flex-grow flex flex-col min-w-[400px] bg-dark-surface">

        <div class="h-12 border-b border-gray-700 flex justify-between items-center px-4 bg-dark-surface shrink-0">
            <div class="font-mono text-sm text-dark-muted flex items-center gap-2"><span>Python 3</span></div>

            <div class="flex gap-2">
                <button onclick="resetCode()" class="bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold py-1.5 px-3 rounded transition flex items-center gap-2 border border-gray-600">
                   <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74-2.74L3 12"></path></svg>
                   Reset Code
                </button>

                <button id="btn-run" onclick="runCode()" class="bg-brand-green hover:bg-green-600 text-white text-xs font-bold py-1.5 px-4 rounded transition flex items-center gap-2 shadow-lg shadow-green-500/20">
                   <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                   Run
                </button>


                <button id="btn-run" onclick="submitCode()" class="bg-brand-orange hover:bg-orange-600 text-white text-xs font-bold py-1.5 px-4 rounded transition flex items-center gap-2 shadow-lg shadow-orange-500/20">
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
    // --- 1. VARIABLES FROM PHP ---
    const teacherCode = <?= json_encode($graderContent) ?>;
    const problemCode = <?= json_encode($fileCode) ?>;
    const problemId = <?= json_encode($problemId) ?>;
    const problemInputType = <?= json_encode($problemInputType) ?>;
    const contestId = <?= json_encode($contestId) ?>;
    const courseCode = <?= json_encode($courseCode) ?>;
    const contestName = <?= json_encode($contestName) ?>;

    // NEW: Previous submission content from PHP
    const submittedCode = <?= json_encode($submittedContent) ?>;

    // --- 2. DEFAULT CODE TEMPLATE ---
    var default_code = <?php echo json_encode($templateContent ?? ""); ?>;

    console.log(default_code);

    // --- 3. EDITOR SETUP ---
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/twilight");
    editor.session.setMode("ace/mode/python");
    editor.setFontSize(14);
    editor.setShowPrintMargin(false);

    // --- 4. INIT EDITOR CONTENT ---
    if (submittedCode && submittedCode.trim() !== "") {
        editor.setValue(submittedCode, -1);
    } else {
        editor.setValue(default_code, -1);
    }

    // --- 5. RESET FUNCTION ---
    function resetCode() {
        if(confirm("Are you sure you want to reset your code to default? Any unsaved changes will be lost.")) {
            editor.setValue(default_code, -1);
        }
    }

    // --- 6. GRADING & SUBMISSION LOGIC ---
    let outputBuffer = "";


function outf_std(text) {
    outputBuffer += text;
    var mypre = document.getElementById("output");
    var span = document.createElement("span");

    // --- Styling Logic ---
    if (text.includes("Accepted") || text.includes("PASS")) {
        span.className = "skulpt-pass";
    } else if (text.includes("Wrong Answer") || text.includes("WRONG") || text.includes("Error") || text.includes("Exception")) {
        span.className = "skulpt-fail";
    } else if (text.includes("Final Score")) {
        span.className = "skulpt-header";
    }

    span.innerText = text;
    mypre.appendChild(span);
    mypre.scrollTop = mypre.scrollHeight;

}

function outf(text) {
    // 1. Update the global buffer
    outputBuffer += text;

    // 2. Extract strictly the last 2 lines
    // (trim() removes trailing empty lines so the result isn't just blank space)
    var lines = outputBuffer.trim().split('\n');
    var lastTwo = lines.slice(-2);

    // 3. Clear the display
    var mypre = document.getElementById("output");
    mypre.innerHTML = '';

    // 4. Re-render only those last 2 lines
    lastTwo.forEach(function(line) {
        if (!line) return; // Skip empty lines if necessary

        var div = document.createElement("div"); // Use div for automatic line breaking
        div.innerText = line;

        // --- Re-apply Styling Logic to the Line ---
        if (line.includes("Accepted") || line.includes("PASS")) {
            div.className = "skulpt-pass";
        } else if (line.includes("Wrong Answer") || line.includes("WRONG") || line.includes("Error") || line.includes("Exception")) {
            div.className = "skulpt-fail";
        } else if (line.includes("Final Score")) {
            div.className = "skulpt-header";
        }

        mypre.appendChild(div);
    });
}

    function builtinRead(x) {
        if (Sk.builtinFiles === undefined || Sk.builtinFiles["files"][x] === undefined) throw "File not found: '" + x + "'";
        return Sk.builtinFiles["files"][x];
    }

    function submitCode() {
        var studentCode = editor.getValue();
        var finalProgram = studentCode + "\n" + teacherCode;

        var mypre = document.getElementById("output");
        mypre.innerHTML = '';
        outputBuffer = "";

        Sk.pre = "output";
        Sk.configure({output:outf, read:builtinRead});

        var btn = document.getElementById("btn-run");
        var originalText = btn.innerHTML;
        btn.innerHTML = "Running...";
        btn.disabled = true;
        btn.classList.add("opacity-50");

        Sk.misceval.asyncToPromise(function() {
            return Sk.importMainWithBody("<stdin>", false, finalProgram, true);
        }).then(function(mod) {
            console.log('Execution success');
            submitResult(studentCode, outputBuffer);
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.classList.remove("opacity-50");
        }, function(err) {
            outf("\nRuntime Error: " + err.toString());
            submitResult(studentCode, outputBuffer + "\nRuntime Error: " + err.toString());
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.classList.remove("opacity-50");
        });
    }

    function runCode() {
            var studentCode = editor.getValue();
            var finalProgram = studentCode ;

            var mypre = document.getElementById("output");
            mypre.innerHTML = '';
            outputBuffer = "";

            Sk.pre = "output";
            Sk.configure({output:outf_std, read:builtinRead});

            var btn = document.getElementById("btn-run");
            var originalText = btn.innerHTML;
            btn.innerHTML = "Running...";
            btn.disabled = true;
            btn.classList.add("opacity-50");

            Sk.misceval.asyncToPromise(function() {
                return Sk.importMainWithBody("<stdin>", false, finalProgram, true);
            }).then(function(mod) {
                console.log('Execution success');
                submitResult(studentCode, outputBuffer);
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.classList.remove("opacity-50");
            }, function(err) {
                outf_std("\nRuntime Error: " + err.toString());
                submitResult(studentCode, outputBuffer + "\nRuntime Error: " + err.toString());
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.classList.remove("opacity-50");
            });
        }

    function submitResult(code, output) {
        if (!contestId) {
            console.log("Practice mode: Result not saved to DB.");
            return;
        }
        fetch('submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                code: code,
                output: output,
                problem_code: problemCode,
                contest_id: contestId,
                course_code: courseCode,
                problem_id: problemId,
                contest_name: contestName
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log("Saved to DB:", data);
        })
        .catch((error) => { console.error('Error:', error); });
    }

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
    document.addEventListener('mousemove', function(e) {
        if (isResizingV) {
            let newWidth = (e.clientX / window.innerWidth) * 100;
            if (newWidth > 15 && newWidth < 85) { leftPanel.style.width = newWidth + '%'; }
            editor.resize();
        }
        if (isResizingH) {
            const containerHeight = document.getElementById('ide-container').clientHeight;
            const relativeY = e.clientY - 48;
            let newHeightPercentage = (relativeY / containerHeight) * 100;
             if (newHeightPercentage > 20 && newHeightPercentage < 85) { editorWrapper.style.height = newHeightPercentage + '%'; }
             editor.resize();
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
    document.getElementById('editor').addEventListener('contextmenu', function(e) { e.preventDefault(); return false; }, false);
    editor.on("paste", function(e) { e.text = ""; alert("Security Alert: Pasting is disabled."); });
    editor.commands.addCommand({ name: "blockCopy", bindKey: {win: "Ctrl-C|Ctrl-X|Ctrl-V", mac: "Command-C|Command-X|Command-V"}, exec: function(editor) { return true; } });
</script>

</body>
</html>