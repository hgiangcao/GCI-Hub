<?php
// 1. Backend Logic
require_once 'auth.php';    
require_once 'problem.php';

// Check for 'code' in URL
if (!isset($_GET['code'])) {
    die("Error: No problem code specified in URL (e.g., ?code=3263_CONDOU).");
}

$problemCode = $_GET['code'];
$problem = Problem::getByCode($problemCode);

if (!$problem) {
    die("Error: Problem code '{$problemCode}' not found in database.");
}

// Extract Data
$fileCode = $problem['code']; 
$problemTitle = $problem['title'];
$problemLevel = $problem['level'];

// Handle 'input_type' gracefully (Use 'arg' if column doesn't exist yet)
$problemInputType = isset($problem['input_type']) ? $problem['input_type'] : 'arg';

// --- Load Files Server-Side ---
$descPath = "problemset/" . $fileCode . ".html"; 
$pyPath   = "problemset/" . $fileCode . ".py";

// Read Description
if (file_exists($descPath)) {
    $descContent = file_get_contents($descPath);
} else {
    $descContent = "<div style='color:#ef4444; padding:20px;'>Description file (<b>$fileCode.html</b>) not found in problemset folder.</div>";
}

// Read Python Grader Code
if (file_exists($pyPath)) {
    $graderContent = file_get_contents($pyPath);
} else {
    $graderContent = ""; // Empty string if missing
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($problemTitle) ?> - GCIOJ IDE</title>
    <link href="https://fonts.googleapis.com/css2?family=inter:wght@400;500;600&family=Roboto+Mono&display=swap" rel="stylesheet">
    
    <style>
        /* --- LEETCODE DARK THEME VARIABLES --- */
        :root {
            --bg-main: #1a1a1a;
            --bg-panel-dark: #262626;
            --bg-panel-light: #333333;
            --border-color: #3a3a3a;
            --text-primary: #eff2f6;
            --text-secondary: #9ca3af;
            --lc-green: #28c76f;
            --lc-green-hover: #3dd681;
            --lc-red: #ef4444;
            --resizer-bg: #1a1a1a;
            --resizer-hover: #28c76f;
        }

        body { 
            margin: 0; padding: 0; 
            height: 100vh; width: 100vw; 
            overflow: hidden; 
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-primary);
            display: flex;
        }

        .main-container { display: flex; width: 100%; height: 100%; }

        /* --- LEFT PANEL --- */
        #problem-panel {
            width: 35%; min-width: 250px;
            background-color: var(--bg-panel-dark);
            border-right: 1px solid var(--border-color);
            display: flex; flex-direction: column;
        }

        .panel-header-custom {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex; justify-content: space-between; align-items: center;
        }
        .back-btn { text-decoration: none; color: var(--text-secondary); font-size: 14px; }
        .back-btn:hover { color: var(--text-primary); }

        .difficulty-badge {
            font-size: 12px; padding: 2px 8px; border-radius: 12px; margin-left: 10px; font-weight: 600;
        }
        .diff-Easy { color: var(--lc-green); background: rgba(40, 199, 111, 0.2); }
        .diff-Medium { color: #ffc01e; background: rgba(255, 192, 30, 0.2); }
        .diff-Hard { color: var(--lc-red); background: rgba(239, 68, 68, 0.2); }

        .problem-content {
            padding: 20px; font-size: 12px;overflow-y: auto; line-height: 1.2; color: var(--text-secondary); flex-grow: 1;
        }
        .problem-content h1, .problem-content h2 { color: var(--text-primary); margin-top: 0; }
        .problem-content pre, .problem-content code {
            background: var(--bg-panel-light); padding: 2px 4px; border-radius: 4px; 
            font-family: 'Roboto Mono', monospace; font-size: 0.9em; color: var(--text-primary);
        }
        .problem-content pre { padding: 15px; display: block; overflow-x: auto; }

        /* --- RESIZERS --- */
        .resizer-vertical {
            width: 6px; background-color: var(--resizer-bg); cursor: col-resize; z-index: 10;
            border-left: 1px solid var(--border-color); border-right: 1px solid var(--border-color);
        }
        .resizer-horizontal {
            height: 6px; background-color: var(--resizer-bg); cursor: row-resize; z-index: 10;
            border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); width: 100%;
        }
        .resizer-vertical:hover, .resizer-horizontal:hover { background-color: var(--resizer-hover); }

        /* --- RIGHT PANEL --- */
        #ide-container { flex-grow: 1; display: flex; flex-direction: column; height: 100%; min-width: 400px; }

        .toolbar {
            padding: 8px 16px; background-color: var(--bg-panel-dark);
            border-bottom: 1px solid var(--border-color);
            display: flex; justify-content: space-between; align-items: center; height: 40px;
        }
        .toolbar-title { font-weight: 600; color: var(--text-primary); font-size: 14px; }

        #btn-run { 
            background-color: var(--lc-green); color: white; border: none; 
            padding: 6px 16px; border-radius: 4px; font-weight: 500; font-size: 13px;
            cursor: pointer; display: flex; align-items: center; gap: 5px;
        }
        #btn-run:hover { background-color: var(--lc-green-hover); }

        #editor-wrapper { height: 60%; min-height: 100px; position: relative; }
        #editor { width: 100%; height: 100%; font-size: 12px; font-family: 'Roboto Mono', monospace; }

        #console-panel { flex-grow: 1; background-color: var(--bg-panel-dark); display: flex; flex-direction: column; min-height: 100px; }
        
        .console-tabs { display: flex; background-color: var(--bg-panel-light); border-bottom: 1px solid var(--border-color); }
        .tab { padding: 8px 16px; font-size: 13px; cursor: pointer; color: var(--text-secondary); border-bottom: 2px solid transparent; }
        .tab.active { color: var(--text-primary); border-bottom: 2px solid var(--lc-green); background-color: var(--bg-panel-dark); }

        #output { 
            background: var(--bg-panel-dark); color: var(--text-primary); 
            padding: 15px; flex-grow: 1; font-family: 'Roboto Mono', monospace; 
            white-space: pre-wrap; overflow-y: auto; font-size: 12px; border: none;
        }
        
        .skulpt-pass { color: var(--lc-green); font-weight: 600; }
        .skulpt-fail { color: var(--lc-red); font-weight: 600; }
        .skulpt-header { color: var(--text-secondary); margin-top: 10px; display: block; border-top: 1px solid var(--border-color); padding-top: 10px;}
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/skulpt@1.2.0/dist/skulpt.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/skulpt@1.2.0/dist/skulpt-stdlib.js"></script>
</head>
<body>

<div class="main-container">
    <div id="problem-panel">
        <div class="panel-header-custom">
            <div><a href="problemset.php" class="back-btn">&larr; All Problems</a></div>
            <span class="difficulty-badge diff-<?= htmlspecialchars($problemLevel) ?>">
                <?= htmlspecialchars($problemLevel) ?>
            </span>
        </div>
        <div id="problem-desc-container" class="problem-content">
            <?= $descContent ?> 
        </div>
    </div>

    <div class="resizer-vertical" id="drag-v"></div>

    <div id="ide-container">
        <div class="toolbar">
            <div class="toolbar-title"><?= htmlspecialchars($problemTitle) ?></div>
            <button id="btn-run" onclick="runCode()">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
               Run & Grade
            </button>
        </div>
        <div id="editor-wrapper">
            <div id="editor"></div>
        </div>
        <div class="resizer-horizontal" id="drag-h"></div>
        <div id="console-panel">
            <div class="console-tabs">
                <div class="tab active">Console Output</div>
            </div>
            <div id="output">Result will appear here...</div>
        </div>
    </div>
</div>

<script>
    // --- 1. PASS PHP VARIABLES TO JS ---
    const teacherCode = <?= json_encode($graderContent) ?>;
    const problemCode = <?= json_encode($fileCode) ?>;
    // Add quotes using json_encode so it becomes "arg" or "list" in JS
    const problemInputType = <?= json_encode($problemInputType) ?>; 

    // --- 2. SETUP EDITOR ---
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/twilight"); 
    editor.session.setMode("ace/mode/python");
    editor.setFontSize(12);
    editor.setShowPrintMargin(false); 

    // Set default starter code
    editor.setValue(`# Write your code for ${problemCode} here
def solve(${problemInputType}):
    return None
`, -1);

    // --- 3. RESIZING LOGIC ---
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
            const relativeY = e.clientY - 40; 
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

    // --- 4. SECURITY ---
    document.getElementById('editor').addEventListener('contextmenu', function(e) { e.preventDefault(); return false; }, false);
    editor.on("paste", function(e) { e.text = ""; alert("Security Alert: Pasting is disabled."); });
    editor.commands.addCommand({ name: "blockCopy", bindKey: {win: "Ctrl-C|Ctrl-X|Ctrl-V", mac: "Command-C|Command-X|Command-V"}, exec: function(editor) { return true; } });

    // --- 5. GRADING LOGIC ---
    function outf(text) { 
        var mypre = document.getElementById("output"); 
        var span = document.createElement("span");
        
        if (text.includes("PASS")) { span.className = "skulpt-pass"; span.innerText = text; } 
        else if (text.includes("WRONG ANSWER")) { span.className = "skulpt-fail"; span.innerText = text; } 
        else if (text.includes("ERROR")) { span.className = "skulpt-fail"; span.innerText = text; } 
        else if (text.includes("Final Score") || text.includes("Starting Auto-Grader")) { span.className = "skulpt-header"; span.innerText = text; } 
        else { span.innerText = text; }

        mypre.appendChild(span);
        mypre.scrollTop = mypre.scrollHeight;
    } 

    function builtinRead(x) {
        if (Sk.builtinFiles === undefined || Sk.builtinFiles["files"][x] === undefined) throw "File not found: '" + x + "'";
        return Sk.builtinFiles["files"][x];
    }

    function runCode() { 
        var studentCode = editor.getValue();
        var finalProgram = studentCode + "\n" + teacherCode;
        
        var mypre = document.getElementById("output"); 
        mypre.innerHTML = ''; 
        
        Sk.pre = "output";
        Sk.configure({output:outf, read:builtinRead}); 

        Sk.misceval.asyncToPromise(function() {
            return Sk.importMainWithBody("<stdin>", false, finalProgram, true);
        }).then(function(mod) {
            console.log('success');
        }, function(err) {
            outf("Runtime Error: " + err.toString());
        });
    }
</script>

</body>
</html>