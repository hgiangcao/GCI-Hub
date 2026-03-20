<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'auth.php';
require_once 'check_admin.php';
require_once 'problem.php';

global $pdo;
if (!isset($pdo)) { require_once 'db.php'; }

$message = "";
$error = "";
$nextId = Problem::get_next_id();

// --- Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rawTitle = trim($_POST['title'] ?? '');
    $code = (string)$nextId;

    // Map grading_type to output_type
    $gradingType = $_POST['grading_type'] ?? 'algorithm';
    $outputType = ($gradingType === 'algorithm') ? 'value' : 'screen';

    if (empty($rawTitle)) {
        $error = "Title is required.";
    } else {
        try {
            Problem::create(
                $code,
                $rawTitle,
                $_POST['level'] ?? 'Easy',
                $_POST['input_type'] ?? 'arg',
                $_POST['leetcode_id'] ?? '',
                $_POST['leetcode_link'] ?? '',
                $outputType,
                $gradingType,
                (int)($_POST['time_limit_ms'] ?? 1000),
                (int)($_POST['memory_limit_mb'] ?? 256),
                $_POST['tag'] ?? '',
                $_POST['forbidden_keyword'] ?? ''
            );
            $message = "Problem created! Code: <strong>" . htmlspecialchars($code) . "</strong> — <em>" . htmlspecialchars($rawTitle) . "</em>";
            $nextId = Problem::get_next_id(); // Refresh
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
    <title>Quick Create Problem - GCIOJ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#121212', surface: '#1e1e1e', border: '#333333', text: '#e0e0e0', muted: '#888888' },
                        brand: { orange: '#ffa116', green: '#2cbb5d', blue: '#3b82f6', red: '#ef4444', cyan: '#06b6d4' }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .form-input { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .form-input:focus { border-color: #ffa116; box-shadow: 0 0 0 3px rgba(255, 161, 22, 0.15); }
        .type-btn { transition: all 0.2s ease; }
        .type-btn.active { border-color: #ffa116; background: rgba(255, 161, 22, 0.1); color: #ffa116; }
        .type-btn:not(.active):hover { border-color: #555; }
        .optional-section { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
        .optional-section.open { max-height: 600px; }
    </style>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen">

    <?php include_once 'nav.php'; ?>

    <div class="max-w-xl mx-auto p-6 md:p-10">

        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-bolt text-brand-orange"></i>
                    Quick Create
                </h1>
                <p class="text-dark-muted text-xs mt-1">Next ID: <span class="text-brand-orange font-mono font-bold"><?= $nextId ?></span></p>
            </div>
            <a href="dashboard.php" class="text-xs text-dark-muted hover:text-white transition">← Dashboard</a>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="bg-green-900/30 text-green-400 p-3 rounded-lg mb-5 border border-green-800 text-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i> <?= $message ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-900/30 text-red-400 p-3 rounded-lg mb-5 border border-red-800 text-sm flex items-center gap-2">
                <i class="fas fa-times-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" class="space-y-5">

            <!-- MAIN: Title -->
            <div>
                <label class="text-xs text-dark-muted block mb-1.5">Title <span class="text-brand-red">*</span></label>
                <input type="text" name="title" required placeholder="e.g. Two Sum" autofocus
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                       class="form-input w-full bg-dark-surface border border-dark-border rounded-lg px-4 py-3 text-sm outline-none">
            </div>

            <!-- MAIN: Problem Type (combined output + grading) -->
            <div>
                <label class="text-xs text-dark-muted block mb-2">Problem Type</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="setType('algorithm')" id="btn-algorithm"
                            class="type-btn active border border-dark-border rounded-lg p-4 text-center cursor-pointer">
                        <div class="text-lg mb-1">⚙️</div>
                        <div class="text-sm font-semibold">Function</div>
                        <div class="text-[10px] text-dark-muted mt-0.5">Return Value</div>
                    </button>
                    <button type="button" onclick="setType('test')" id="btn-test"
                            class="type-btn border border-dark-border rounded-lg p-4 text-center cursor-pointer">
                        <div class="text-lg mb-1">🖥️</div>
                        <div class="text-sm font-semibold">Std I/O</div>
                        <div class="text-[10px] text-dark-muted mt-0.5">Print Screen</div>
                    </button>
                </div>
                <input type="hidden" name="grading_type" id="inp_grading_type" value="algorithm">
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-3 bg-brand-orange hover:bg-orange-600 text-white font-bold rounded-lg shadow-lg shadow-orange-500/20 transition-all duration-200 hover:-translate-y-0.5 text-sm">
                Create Problem
            </button>

            <!-- Optional Fields Toggle -->
            <div class="pt-2">
                <button type="button" onclick="toggleOptional()" id="toggle-btn"
                        class="w-full text-xs text-dark-muted hover:text-white transition flex items-center justify-center gap-1.5 py-2">
                    <i class="fas fa-chevron-down text-[8px]" id="toggle-icon"></i>
                    More Options
                </button>

                <div id="optional-fields" class="optional-section mt-3 space-y-4">

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Level</label>
                            <select name="level" class="form-input w-full bg-dark-surface border border-dark-border rounded-lg px-3 py-2 text-sm outline-none">
                                <option value="Easy">Easy</option>
                                <option value="Medium">Medium</option>
                                <option value="Hard">Hard</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Tags</label>
                            <input type="text" name="tag" placeholder="array, dp"
                                   class="form-input w-full bg-dark-surface border border-dark-border rounded-lg px-3 py-2 text-sm outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs text-dark-muted block mb-1">Input Variable</label>
                        <input type="text" name="input_type" value="arg"
                               class="form-input w-full bg-dark-surface border border-dark-border rounded-lg px-3 py-2 text-sm font-mono outline-none">
                    </div>

                    <div>
                        <label class="text-xs text-dark-muted block mb-1">Forbidden Keywords</label>
                        <input type="text" name="forbidden_keyword" placeholder="for,while,import"
                               class="form-input w-full bg-dark-surface border border-dark-border rounded-lg px-3 py-2 text-sm font-mono outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Time (ms)</label>
                            <input type="number" name="time_limit_ms" value="1000"
                                   class="form-input w-full bg-dark-surface border border-dark-border rounded-lg px-3 py-2 text-sm outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Memory (MB)</label>
                            <input type="number" name="memory_limit_mb" value="256"
                                   class="form-input w-full bg-dark-surface border border-dark-border rounded-lg px-3 py-2 text-sm outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Leetcode ID</label>
                            <input type="text" name="leetcode_id" placeholder="1"
                                   class="form-input w-full bg-dark-surface border border-dark-border rounded-lg px-3 py-2 text-sm outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-dark-muted block mb-1">Leetcode Link</label>
                            <input type="text" name="leetcode_link" placeholder="https://..."
                                   class="form-input w-full bg-dark-surface border border-dark-border rounded-lg px-3 py-2 text-sm outline-none">
                        </div>
                    </div>

                </div>
            </div>

        </form>
    </div>

    <script>
        function setType(type) {
            document.getElementById('inp_grading_type').value = type;
            document.getElementById('btn-algorithm').classList.toggle('active', type === 'algorithm');
            document.getElementById('btn-test').classList.toggle('active', type === 'test');
        }

        let optionalOpen = false;
        function toggleOptional() {
            optionalOpen = !optionalOpen;
            document.getElementById('optional-fields').classList.toggle('open', optionalOpen);
            document.getElementById('toggle-icon').style.transform = optionalOpen ? 'rotate(180deg)' : '';
            document.getElementById('toggle-btn').querySelector('span')
        }
    </script>

</body>
</html>
