<?php
// --- 1. CONTROLLER LOGIC ---
session_start();
require_once 'auth.php'; // Ensure admin is logged in
require_once 'problem.php';

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Get Data
    $code = trim($_POST['code']);
    $title = trim($_POST['title']);
    $level = $_POST['level'];
    $inputType = $_POST['input_type'];
    
    $htmlDesc = $_POST['html_desc'];
    $pythonGrader = $_POST['python_grader'];

    // 2. Validation
    if (empty($code) || empty($title)) {
        $error = "Code and Title are required.";
    } else {
        // 3. Create Files
        $dir = "problemset/";
        if (!file_exists($dir)) mkdir($dir, 0777, true);

        // Sanitize Filename (Code)
        $safeCode = preg_replace('/[^a-zA-Z0-9_\-]/', '', $code);
        
        // Save HTML Description
        $htmlPath = $dir . $safeCode . ".html";
        if (file_put_contents($htmlPath, $htmlDesc) === false) {
            $error = "Failed to save HTML file.";
        }
        
        // Save Python Grader
        $pyPath = $dir . $safeCode . ".py";
        if (file_put_contents($pyPath, $pythonGrader) === false) {
            $error = "Failed to save Python file.";
        }

        // 4. Save to Database
        if (!$error) {
            if (Problem::create($safeCode, $title, $level, $inputType)) {
                $message = "Problem '{$title}' created successfully!";
            } else {
                $error = "Database Error: Problem Code might already exist.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Create Problem - GCIOJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', hover: '#3e3e3e', text: '#eff1f6', muted: '#9ca3af', input: '#202020' },
                        brand: { orange: '#ffa116', green: '#2cbb5d', red: '#ef4444' }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'], mono: ['Roboto Mono', 'monospace'] }
                }
            }
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen p-6">

    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-8 border-b border-gray-700 pb-4">
            <div>
                <h1 class="text-3xl font-bold text-brand-orange">Create New Problem</h1>
                <p class="text-dark-muted mt-1">Add a new challenge to the database.</p>
            </div>
            <a href="index.php" class="text-dark-muted hover:text-white transition">&larr; Dashboard</a>
        </div>

        <?php if ($message): ?> <div class="bg-green-900/50 text-green-300 p-4 rounded mb-6 border border-green-700"><?= $message ?></div> <?php endif; ?>
        <?php if ($error): ?> <div class="bg-red-900/50 text-red-300 p-4 rounded mb-6 border border-red-700"><?= $error ?></div> <?php endif; ?>

        <form method="POST" id="problemForm">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-dark-surface p-6 rounded-lg border border-gray-700 shadow-lg">
                        <h2 class="text-xl font-bold text-white mb-4">Basic Info</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Problem Code (Unique)</label>
                                <input type="text" name="code" required class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white focus:border-brand-orange outline-none font-mono" placeholder="G001_SUM">
                            </div>
                            
                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Title</label>
                                <input type="text" name="title" required class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white focus:border-brand-orange outline-none" placeholder="Two Sum">
                            </div>

                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Difficulty</label>
                                <select name="level" class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white">
                                    <option value="Easy">Easy</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Hard">Hard</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Function Input Type</label>
                                <select name="input_type" class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white">
                                    <option value="arg">Single Argument (arg)</option>
                                    <option value="a, b">Two Arguments (a, b)</option>
                                    <option value="nums">List (nums)</option>
                                    <option value="s">String (s)</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Used for the default starter code.</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" onclick="submitForm()" class="w-full py-3 bg-brand-orange hover:bg-orange-600 text-white font-bold rounded shadow-lg transition">
                        Create Problem
                    </button>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-dark-surface p-6 rounded-lg border border-gray-700 shadow-lg">
                        <h2 class="text-xl font-bold text-white mb-2">HTML Description</h2>
                        <p class="text-xs text-dark-muted mb-4">Write the problem description, examples, and constraints using HTML.</p>
                        
                        <div id="editor-html" class="h-64 w-full rounded border border-gray-600"></div>
                        <textarea name="html_desc" id="textarea-html" class="hidden"></textarea>
                    </div>

                    <div class="bg-dark-surface p-6 rounded-lg border border-gray-700 shadow-lg">
                        <h2 class="text-xl font-bold text-white mb-2">Python Auto-Grader</h2>
                        <p class="text-xs text-dark-muted mb-4">Paste the `auto_grade()` function here. Must include test case generation.</p>
                        
                        <div id="editor-python" class="h-96 w-full rounded border border-gray-600"></div>
                        <textarea name="python_grader" id="textarea-python" class="hidden"></textarea>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <script>
        // 1. Initialize HTML Editor
        var editorHtml = ace.edit("editor-html");
        editorHtml.setTheme("ace/theme/twilight");
        editorHtml.session.setMode("ace/mode/html");
        editorHtml.setFontSize(14);
        editorHtml.setValue(`<div class="space-y-4 text-white">
    <p>Describe the problem here...</p>
    
    <div class="mt-6">
        <h3 class="font-bold">Example 1:</h3>
        <code>Input: [1,2], Output: 3</code>
    </div>
</div>`, -1);

        // 2. Initialize Python Editor
        var editorPy = ace.edit("editor-python");
        editorPy.setTheme("ace/theme/twilight");
        editorPy.session.setMode("ace/mode/python");
        editorPy.setFontSize(14);
        editorPy.setValue(`import random
random.seed(42)

def correct_solution(arg):
    return arg

def auto_grade():
    score = 0
    # ... logic here ...
    print(f"Final Score: {score} / 100")

if "solve" in globals():
    try:
        auto_grade()
    except:
        print("Status:", "Compile Error")
`, -1);

        // 3. Form Submission Handler
        function submitForm() {
            // Copy Ace content to hidden textareas so PHP can read it
            document.getElementById('textarea-html').value = editorHtml.getValue();
            document.getElementById('textarea-python').value = editorPy.getValue();
            // Allow form to submit naturally
        }
    </script>

</body>
</html>