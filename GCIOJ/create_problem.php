<?php
// --- 1. CONTROLLER LOGIC ---
session_start();
require_once 'auth.php';
require_once 'check_admin.php';
require_once 'problem.php';

global $pdo;
if (!isset($pdo)) { require_once 'db.php'; }

// --- A. AJAX HANDLER: Load Problem Data ---
// Only trigger if 'action' is explicitly 'fetch'
if (isset($_GET['action']) && $_GET['action'] === 'fetch' && isset($_GET['id'])) {
    $prob = Problem::getById($_GET['id']);

    if ($prob) {
        $dir = "problemset/" . $prob['code'] . "/";
        // Read Files
        $prob['html_content'] = file_exists($dir . $prob['code'] . ".html") ? file_get_contents($dir . $prob['code'] . ".html") : "";
        $prob['py_grader']    = file_exists($dir . $prob['code'] . ".py") ? file_get_contents($dir . $prob['code'] . ".py") : "";
        $prob['py_template']  = file_exists($dir . $prob['code'] . "_Template.py") ? file_get_contents($dir . $prob['code'] . "_Template.py") : "";

        echo json_encode(['status' => 'success', 'data' => $prob]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Not found']);
    }
    exit; // Stop execution here for AJAX
}

// --- B. Page Init ---
$nextIdPreview = Problem::get_next_id();
$nextIdStr = sprintf('%03d', $nextIdPreview);
$message = "";
$error = "";

// Fetch list for dropdown
$allProblems = Problem::getAll();

// Check if an ID was passed in the URL (e.g. from the list page)
$preselectedId = isset($_GET['id']) && !isset($_GET['action']) ? $_GET['id'] : '';

// --- C. Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pId = $_POST['problem_id'] ?? '';
    $editMode = !empty($pId);

    if ($editMode) {
        // --- UPDATE MODE (FILES ONLY) ---
        $existing = Problem::getById($pId);
        if ($existing) {
            $code = $existing['code'];
            $dir = "problemset/" . $code . "/";

            file_put_contents($dir . $code . ".html", $_POST['html_desc']);
            file_put_contents($dir . $code . ".py", $_POST['python_grader']);
            file_put_contents($dir . $code . "_Template.py", $_POST['python_template']);

            $message = "Files updated for <strong>$code</strong>. Database details remained unchanged.";
        } else {
            $error = "Problem not found.";
        }

    } else {
        // --- CREATE MODE (DB + FILES) ---
        $rawTitle = trim($_POST['title']);
        $cleanTitle = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($rawTitle));
        if (strlen($cleanTitle) < 6) $cleanTitle .= str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
        $suffix = substr(str_shuffle($cleanTitle), 0, 6);
        $code = "G" . sprintf('%03d', $nextIdPreview) . "_" . $suffix;

        if (empty($rawTitle)) {
            $error = "Title is required for new problems.";
        } else {
            $dir = "problemset/" . $code . "/";
            if (!file_exists($dir)) { mkdir($dir, 0777, true); }

            file_put_contents($dir . $code . ".html", $_POST['html_desc']);
            file_put_contents($dir . $code . ".py", $_POST['python_grader']);
            file_put_contents($dir . $code . "_Template.py", $_POST['python_template']);

            try {
                Problem::create(
                    $code, $rawTitle, $_POST['level'], $_POST['input_type'],
                    $_POST['leetcode_id'] ?? '', $_POST['leetcode_link'] ?? '',
                    $_POST['output_type'], $_POST['grading_type'],
                    (int)$_POST['time_limit_ms'], (int)$_POST['memory_limit_mb'], $_POST['tag']
                );
                $message = "Problem created! Code: <strong>{$code}</strong>";
                $nextIdPreview++;
                $nextIdStr = sprintf('%03d', $nextIdPreview);
                $allProblems = Problem::getAll(); // Refresh list
            } catch (Exception $e) {
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Manage Problem - GCIOJ</title>
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
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <span class="text-brand-orange">Manage Problem</span>
                </h1>

                <select id="problemSelect" onchange="loadProblem(this.value, true)" class="bg-dark-surface border border-dark-border text-sm rounded px-3 py-1 focus:border-brand-orange outline-none ml-4 max-w-[300px]">
                    <option value="">+ Create New Problem (Next: G<?= $nextIdStr ?>)</option>
                    <?php foreach ($allProblems as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($preselectedId == $p['id']) ? 'selected' : '' ?>>
                            <?= $p['code'] ?> - <?= htmlspecialchars($p['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <a href="dashboard.php" class="text-sm text-dark-muted hover:text-white transition">Back to Dashboard</a>
        </div>

        <?php if ($message): ?> <div class="bg-green-900/30 text-green-400 p-3 rounded mb-6 border border-green-800"><?= $message ?></div> <?php endif; ?>
        <?php if ($error): ?> <div class="bg-red-900/30 text-red-400 p-3 rounded mb-6 border border-red-800"><?= $error ?></div> <?php endif; ?>

        <form method="POST" id="problemForm" class="grid grid-cols-1 xl:grid-cols-12 gap-6 h-full">

            <input type="hidden" name="problem_id" id="problem_id" value="<?= $preselectedId ?>">

            <div class="xl:col-span-3 space-y-5">
                <div class="bg-dark-surface p-4 rounded border border-dark-border relative">
                    <div id="meta-overlay" class="hidden absolute inset-0 bg-dark-bg/80 z-10 flex items-center justify-center text-xs text-dark-muted cursor-not-allowed">
                        <span>Database fields locked in Edit Mode</span>
                    </div>

                    <h3 class="text-sm font-bold text-brand-orange mb-3 uppercase tracking-wider">Identity</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Title</label>
                            <input type="text" name="title" id="inp_title" required class="w-full bg-dark-bg border border-dark-border rounded px-3 py-2 text-sm focus:border-brand-orange outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-dark-muted block mb-1">Level</label>
                                <select name="level" id="inp_level" class="w-full bg-dark-bg border border-dark-border rounded px-2 py-2 text-sm">
                                    <option value="Easy">Easy</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Hard">Hard</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-dark-muted block mb-1">Tags</label>
                                <input type="text" name="tag" id="inp_tag" class="w-full bg-dark-bg border border-dark-border rounded px-2 py-2 text-sm">
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
                                <input type="number" name="time_limit_ms" id="inp_time" value="1000" class="w-full bg-dark-bg border border-dark-border rounded px-2 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-dark-muted block mb-1">Mem (MB)</label>
                                <input type="number" name="memory_limit_mb" id="inp_mem" value="256" class="w-full bg-dark-bg border border-dark-border rounded px-2 py-2 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Input Variable</label>
                            <input type="text" name="input_type" id="inp_input" value="arr" class="w-full bg-dark-bg border border-dark-border rounded px-3 py-2 text-sm font-mono">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-dark-muted block mb-1">Output</label>
                                <select name="output_type" id="inp_output" class="w-full bg-dark-bg border border-dark-border rounded px-2 py-2 text-sm">
                                    <option value="value">Return Value</option>
                                    <option value="screen">Print Screen</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-dark-muted block mb-1">Grading</label>
                                <select name="grading_type" id="inp_grading" class="w-full bg-dark-bg border border-dark-border rounded px-2 py-2 text-sm">
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
                            <input type="text" name="leetcode_id" id="inp_lid" class="w-full bg-dark-bg border border-dark-border rounded px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Leetcode Link</label>
                            <input type="text" name="leetcode_link" id="inp_llink" class="w-full bg-dark-bg border border-dark-border rounded px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <button type="submit" id="submitBtn" onclick="submitForm()" class="w-full py-3 bg-brand-orange hover:bg-orange-600 text-white font-bold rounded shadow-lg transition">
                    Create Problem
                </button>
            </div>

            <div class="xl:col-span-9 flex flex-col h-[85vh] bg-dark-surface rounded border border-dark-border">
                <div class="flex border-b border-dark-border bg-dark-bg/50">
                    <button type="button" onclick="switchTab('desc')" id="btn-desc" class="px-6 py-3 text-sm font-medium text-brand-orange border-b-2 border-brand-orange transition">1. Description (HTML)</button>
                    <button type="button" onclick="switchTab('template')" id="btn-template" class="px-6 py-3 text-sm font-medium text-dark-muted hover:text-white transition">2. Starter Code (User)</button>
                    <button type="button" onclick="switchTab('grader')" id="btn-grader" class="px-6 py-3 text-sm font-medium text-dark-muted hover:text-white transition">3. Auto Grader (System)</button>
                </div>

                <div class="flex-grow relative bg-[#141414]">
                    <div id="container-desc" class="absolute inset-0"><div id="editor-html" class="h-full w-full"></div></div>
                    <div id="container-template" class="absolute inset-0 hidden"><div id="editor-template" class="h-full w-full"></div></div>
                    <div id="container-grader" class="absolute inset-0 hidden"><div id="editor-grader" class="h-full w-full"></div></div>
                </div>
            </div>

            <textarea name="html_desc" id="textarea-html" class="hidden"></textarea>
            <textarea name="python_template" id="textarea-template" class="hidden"></textarea>
            <textarea name="python_grader" id="textarea-grader" class="hidden"></textarea>
        </form>
    </div>

    <script>
        // --- Editors ---
        function initAce(id, mode, content) {
            var editor = ace.edit(id);
            editor.setTheme("ace/theme/twilight");
            editor.session.setMode("ace/mode/" + mode);
            editor.setFontSize(14);
            editor.setShowPrintMargin(false);
            editor.setValue(content, -1);
            return editor;
        }

        const defaultHtml = `<div class="text-gray-300 space-y-4">\n  <h3>Problem Description</h3>\n  <p>Write a description...</p>\n</div>`;
        const defaultTemplate = `class Solution:\n    def solve(self, arr):\n        # Write your code here\n        pass`;
        const defaultGrader = `import sys\nfrom Solution import Solution\n\ndef auto_grade():\n    sol = Solution()\n    score = 0\n    if sol.solve([1,2]) == 3:\n        score += 50\n    print(f"Score: {score}")\n\nif __name__ == "__main__":\n    auto_grade()`;

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

        function submitForm() {
            document.getElementById('textarea-html').value = editorHtml.getValue();
            document.getElementById('textarea-template').value = editorTemplate.getValue();
            document.getElementById('textarea-grader').value = editorGrader.getValue();
        }

        // --- Load Data ---
        function loadProblem(id, updateUrl = false) {
            const inputs = ['inp_title', 'inp_level', 'inp_tag', 'inp_time', 'inp_mem', 'inp_input', 'inp_output', 'inp_grading', 'inp_lid', 'inp_llink'];

            // 1. Update URL to match selection (e.g. ?id=5)
            if (updateUrl) {
                const newUrl = id ? '?id=' + id : '?';
                window.history.pushState({path: newUrl}, '', newUrl);
            }

            if (!id) {
                // CREATE MODE
                document.getElementById('problemForm').reset();
                document.getElementById('problem_id').value = "";
                document.getElementById('submitBtn').innerText = "Create Problem";
                document.getElementById('meta-overlay').classList.add('hidden'); // Unlock fields

                inputs.forEach(i => document.getElementById(i).disabled = false);

                editorHtml.setValue(defaultHtml, -1);
                editorTemplate.setValue(defaultTemplate, -1);
                editorGrader.setValue(defaultGrader, -1);
                return;
            }

            // EDIT MODE
            fetch('?action=fetch&id=' + id)
                .then(response => response.json())
                .then(res => {
                    if(res.status === 'success') {
                        const d = res.data;
                        document.getElementById('problem_id').value = d.id;
                        document.getElementById('submitBtn').innerHTML = "Update Files Only (<strong>" + d.code + "</strong>)";

                        // Populate but Lock Fields
                        document.getElementById('inp_title').value = d.title;
                        document.getElementById('inp_level').value = d.level;
                        document.getElementById('inp_tag').value = d.tag;
                        document.getElementById('inp_time').value = d.time_limit_ms;
                        document.getElementById('inp_mem').value = d.memory_limit_mb;
                        document.getElementById('inp_input').value = d.input_type;
                        document.getElementById('inp_output').value = d.output_type;
                        document.getElementById('inp_grading').value = d.grading_type;
                        document.getElementById('inp_lid').value = d.Leetcode_ID;
                        document.getElementById('inp_llink').value = d.Leetcode_link;

                        // Visual Lock
                        document.getElementById('meta-overlay').classList.remove('hidden');
                        inputs.forEach(i => document.getElementById(i).disabled = true);

                        // Editors
                        editorHtml.setValue(d.html_content, -1);
                        editorTemplate.setValue(d.py_template, -1);
                        editorGrader.setValue(d.py_grader, -1);
                    }
                });
        }

        // --- Init on Page Load ---
        document.addEventListener('DOMContentLoaded', () => {
            const initialId = "<?= $preselectedId ?>";
            if (initialId) {
                loadProblem(initialId, false);
            }
        });
    </script>
</body>
</html>