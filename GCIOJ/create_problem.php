<?php
// --- 1. CONTROLLER LOGIC ---
session_start();
require_once 'auth.php';

require_once 'check_admin.php';
require_once 'problem.php';

global $pdo;
if (!isset($pdo)) { require_once 'db.php'; } // Ensure DB connection exists

$nextIdPreview = Problem::get_next_id();
$nextIdStr = sprintf('%03d', $nextIdPreview);

$message = "";
$error = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // A. Generate Problem Code: G{ID}_{RANDOM}
        $rawTitle = trim($_POST['title']);
        $cleanTitle = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($rawTitle));
        if (strlen($cleanTitle) < 6) $cleanTitle .= str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
        $suffix = substr(str_shuffle($cleanTitle), 0, 6);

        // Re-query ID to be safe against concurrency
        $realNextId = $nextIdPreview;
        $code = "G" . sprintf('%03d', $realNextId) . "_" . $suffix;

        // B. Prepare Data (with Defaults)
        $data = [
            'code'          => $code,
            'title'         => $rawTitle,
            'level'         => $_POST['level'],
            'input_type'    => $_POST['input_type'], // varchar(10)
            'output_type'   => $_POST['output_type'], // enum
            'grading_type'  => $_POST['grading_type'], // enum
            'time_limit'    => (int)$_POST['time_limit_ms'],
            'memory_limit'  => (int)$_POST['memory_limit_mb'],

            // Handle Optionals
            'leetcode_id'   => !empty($_POST['leetcode_id']) ? trim($_POST['leetcode_id']) : 'None',
            'leetcode_link' => !empty($_POST['leetcode_link']) ? trim($_POST['leetcode_link']) : 'None',
            'tag'           => !empty($_POST['tag']) ? trim($_POST['tag']) : 'number'
    ];

    // C. Validation
    if (empty($data['title'])) {
        $error = "Title is required.";
    } else {
        // D. File Operations
        $dir = "problemset/" . $code . "/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        // Save Editors Content
        file_put_contents($dir . $code . ".html", $_POST['html_desc']);
        file_put_contents($dir . $code . ".py", $_POST['python_grader']);
        file_put_contents($dir . $code . "_Template.py", $_POST['python_template']);

        // E. Database Insert
        try {
            Problem::create(
                $data['code'], $data['title'], $data['level'], $data['input_type'],
                $data['leetcode_id'], $data['leetcode_link'], $data['output_type'],
                $data['grading_type'], $data['time_limit'], $data['memory_limit'], $data['tag']
            );
            $message = "Problem created! Code: <strong>{$code}</strong>";

            // Refresh Next ID for the UI
            $nextIdPreview++;
            $nextIdStr = sprintf('%03d', $nextIdPreview);
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
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
                        dark: { bg: '#121212', surface: '#1e1e1e', border: '#333333', text: '#e0e0e0', muted: '#888888' },
                        brand: { orange: '#ffa116', green: '#2cbb5d' }
                    }
                }
            }
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen p-6">

    <div class="max-w-[1400px] mx-auto">

        <div class="flex justify-between items-center mb-6 pb-4 border-b border-dark-border">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <span class="text-brand-orange">Create Problem</span>
                    <span class="text-xs bg-dark-border text-dark-muted px-2 py-1 rounded font-mono">Next ID: <?= $nextIdStr ?></span>
                </h1>
                <p class="text-dark-muted text-sm mt-1">Files will be generated in <code class="text-brand-orange">problemset/G<?= $nextIdStr ?>_XXXXXX/</code></p>
            </div>
            <a href="dashboard.php" class="text-sm text-dark-muted hover:text-white transition">Back to Dashboard</a>
        </div>

        <?php if ($message): ?> <div class="bg-green-900/30 text-green-400 p-3 rounded mb-6 border border-green-800"><?= $message ?></div> <?php endif; ?>
        <?php if ($error): ?> <div class="bg-red-900/30 text-red-400 p-3 rounded mb-6 border border-red-800"><?= $error ?></div> <?php endif; ?>

        <form method="POST" id="problemForm" class="grid grid-cols-1 xl:grid-cols-12 gap-6 h-full">

            <div class="xl:col-span-3 space-y-5">

                <div class="bg-dark-surface p-4 rounded border border-dark-border">
                    <h3 class="text-sm font-bold text-brand-orange mb-3 uppercase tracking-wider">Identity</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Title</label>
                            <input type="text" name="title" required placeholder="e.g. Max Flow" class="w-full bg-dark-bg border border-dark-border rounded px-3 py-2 text-sm focus:border-brand-orange outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-dark-muted block mb-1">Level</label>
                                <select name="level" class="w-full bg-dark-bg border border-dark-border rounded px-2 py-2 text-sm">
                                    <option value="Easy">Easy</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Hard">Hard</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-dark-muted block mb-1">Tags</label>
                                <input type="text" name="tag" placeholder="DP, Graph" class="w-full bg-dark-bg border border-dark-border rounded px-2 py-2 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-dark-surface p-4 rounded border border-dark-border">
                    <h3 class="text-sm font-bold text-brand-orange mb-3 uppercase tracking-wider">Grading Config</h3>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-dark-muted block mb-1">Time (ms)</label>
                                <input type="number" name="time_limit_ms" value="1000" class="w-full bg-dark-bg border border-dark-border rounded px-2 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-dark-muted block mb-1">Mem (MB)</label>
                                <input type="number" name="memory_limit_mb" value="256" class="w-full bg-dark-bg border border-dark-border rounded px-2 py-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Input Variable Name</label>
                            <input type="text" name="input_type" value="arr" placeholder="e.g. nums" class="w-full bg-dark-bg border border-dark-border rounded px-3 py-2 text-sm font-mono">
                            <p class="text-[10px] text-dark-muted mt-1">Used in template generation.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-dark-muted block mb-1">Output Type</label>
                                <select name="output_type" class="w-full bg-dark-bg border border-dark-border rounded px-2 py-2 text-sm">
                                    <option value="value">Return Value</option>
                                    <option value="screen">Print Screen</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-dark-muted block mb-1">Grading</label>
                                <select name="grading_type" class="w-full bg-dark-bg border border-dark-border rounded px-2 py-2 text-sm">
                                    <option value="test">Std I/O</option>
                                    <option value="algorithm">Function</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-dark-surface p-4 rounded border border-dark-border">
                    <h3 class="text-sm font-bold text-brand-orange mb-3 uppercase tracking-wider">Reference</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Leetcode ID</label>
                            <input type="text" name="leetcode_id" placeholder="Optional" class="w-full bg-dark-bg border border-dark-border rounded px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Leetcode Link</label>
                            <input type="text" name="leetcode_link" placeholder="Optional" class="w-full bg-dark-bg border border-dark-border rounded px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <button type="submit" onclick="submitForm()" class="w-full py-3 bg-brand-orange hover:bg-orange-600 text-white font-bold rounded shadow-lg transition">
                    Create Problem
                </button>
            </div>

            <div class="xl:col-span-9 flex flex-col h-[85vh] bg-dark-surface rounded border border-dark-border">

                <div class="flex border-b border-dark-border bg-dark-bg/50">
                    <button type="button" onclick="switchTab('desc')" id="btn-desc" class="px-6 py-3 text-sm font-medium text-brand-orange border-b-2 border-brand-orange transition">
                        1. Description (HTML)
                    </button>
                    <button type="button" onclick="switchTab('template')" id="btn-template" class="px-6 py-3 text-sm font-medium text-dark-muted hover:text-white transition">
                        2. Starter Code (User)
                    </button>
                    <button type="button" onclick="switchTab('grader')" id="btn-grader" class="px-6 py-3 text-sm font-medium text-dark-muted hover:text-white transition">
                        3. Auto Grader (System)
                    </button>
                </div>

                <div class="flex-grow relative bg-[#141414]">
                    <div id="container-desc" class="absolute inset-0">
                        <div id="editor-html" class="h-full w-full"></div>
                    </div>
                    <div id="container-template" class="absolute inset-0 hidden">
                        <div id="editor-template" class="h-full w-full"></div>
                    </div>
                    <div id="container-grader" class="absolute inset-0 hidden">
                        <div id="editor-grader" class="h-full w-full"></div>
                    </div>
                </div>
            </div>

            <textarea name="html_desc" id="textarea-html" class="hidden"></textarea>
            <textarea name="python_template" id="textarea-template" class="hidden"></textarea>
            <textarea name="python_grader" id="textarea-grader" class="hidden"></textarea>
        </form>
    </div>

    <script>
        // --- Editor Setup ---
        function initAce(id, mode, content) {
            var editor = ace.edit(id);
            editor.setTheme("ace/theme/twilight");
            editor.session.setMode("ace/mode/" + mode);
            editor.setFontSize(14);
            editor.setShowPrintMargin(false);
            editor.setValue(content, -1);
            return editor;
        }

        const defaultHtml = `<div class="text-gray-300 space-y-4">\n  <h3>Problem Description</h3>\n  <p>Write a description...</p>\n  \n  <div class="bg-gray-800 p-3 rounded">\n    <strong>Example 1:</strong>\n    <pre>Input: nums = [2,7,11,15]\nOutput: 9</pre>\n  </div>\n</div>`;
        const defaultTemplate = `class Solution:\n    def solve(self, arr):\n        # Write your code here\n        pass`;
        const defaultGrader = `import sys\n\n# Import the user's solution\nfrom Solution import Solution\n\ndef auto_grade():\n    sol = Solution()\n    score = 0\n    \n    # Test Case 1\n    if sol.solve([1,2]) == 3:\n        score += 50\n        \n    print(f"Score: {score}")\n\nif __name__ == "__main__":\n    auto_grade()`;

        var editorHtml = initAce("editor-html", "html", defaultHtml);
        var editorTemplate = initAce("editor-template", "python", defaultTemplate);
        var editorGrader = initAce("editor-grader", "python", defaultGrader);

        // --- Tabs ---
        function switchTab(tab) {
            ['desc', 'template', 'grader'].forEach(t => {
                document.getElementById('container-' + t).classList.add('hidden');
                document.getElementById('btn-' + t).classList.remove('text-brand-orange', 'border-b-2', 'border-brand-orange');
                document.getElementById('btn-' + t).classList.add('text-dark-muted');
            });
            document.getElementById('container-' + tab).classList.remove('hidden');
            const btn = document.getElementById('btn-' + tab);
            btn.classList.remove('text-dark-muted');
            btn.classList.add('text-brand-orange', 'border-b-2', 'border-brand-orange');
        }

        // --- Submit ---
        function submitForm() {
            document.getElementById('textarea-html').value = editorHtml.getValue();
            document.getElementById('textarea-template').value = editorTemplate.getValue();
            document.getElementById('textarea-grader').value = editorGrader.getValue();
        }
    </script>
</body>
</html>