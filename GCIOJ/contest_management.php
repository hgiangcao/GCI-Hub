<?php
// --- 1. CONTROLLER LOGIC ---
session_start();
require_once 'auth.php'; 
require_once 'contest.php';
require_once 'problem.php';

$message = "";
$error = "";
$editMode = false;
$contestToEdit = null;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. DELETE
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (Contest::delete($_POST['id'])) {
            $message = "Contest deleted successfully.";
        } else {
            $error = "Failed to delete contest.";
        }
    }

    // B. CREATE / UPDATE
    elseif (isset($_POST['save_contest'])) {
        $name = $_POST['name'];
        $course = $_POST['course'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $isPublic = isset($_POST['is_public']) ? 1 : 0;

        if (isset($_POST['contest_id']) && !empty($_POST['contest_id'])) {
            // Update
            if (Contest::update($_POST['contest_id'], $name, $course, $start, $end, $isActive, $isPublic)) {
                $message = "Contest updated successfully.";
            } else {
                $error = "Failed to UPDATE contest.";
            }
        } else {
            // Create
            if (Contest::create($name, $course, $start, $end, $isActive, $isPublic)) {
                $message = "Contest created successfully.";

                // Create Folder in contest_upload
                $safeName = preg_replace('/[^a-zA-Z0-9_\- ]/', '', $name);
                $safeName = trim($safeName);
                $targetDir = "contest_upload/" . $safeName;

                if (!file_exists($targetDir)) {
                    if (!mkdir($targetDir, 0777, true)) {
                        $error = "Contest created, but failed to create upload folder.";
                    }
                }
            } else {
                $error = "Failed to create contest.";
            }
        }
    }

    // C. ADD PROBLEM (Updated to use Name & Code)
    elseif (isset($_POST['add_problem'])) {
        $c_name = $_POST['contest_name_link']; // Now receiving Name
        $p_code = $_POST['problem_code_link']; // Now receiving Code
        $order  = $_POST['problem_order'];

        // 1. Find Contest ID by Name
        $contestObj = Contest::getByName($c_name);
        // 2. Find Problem ID by Code
        $problemObj = Problem::getByCode($p_code);

        if ($contestObj && $problemObj) {
            try {
                // Link them using IDs
                if (Contest::addProblem($contestObj['id'], $problemObj['id'], $order)) {
                    $message = "Problem '{$p_code}' added to contest '{$c_name}'.";
                } else {
                    $error = "Failed to link problem. It might already be added.";
                }
            } catch (Exception $e) { $error = "Error: " . $e->getMessage(); }
        } else {
            $error = "Error: Invalid Contest Name or Problem Code.";
        }
    }
}

// Handle "Edit" Mode
if (isset($_GET['edit_id'])) {
    $contestToEdit = Contest::getById($_GET['edit_id']);
    if ($contestToEdit) $editMode = true;
}

$contests = Contest::getAll();
$allProblems = Problem::getAll();
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Contest Manager - GCIOJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', hover: '#3e3e3e', text: '#eff1f6', muted: '#9ca3af' },
                        brand: { orange: '#ffa116', green: '#2cbb5d', red: '#ef4444' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen p-6">

    <div class="max-w-7xl mx-auto">

        <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-700">
            <div>
                <h1 class="text-3xl font-bold text-brand-orange">Contest Manager</h1>
                <p class="text-dark-muted mt-1">Create contests, manage folders, and assign problems.</p>
            </div>
            <a href="dashboard.php" class="text-dark-muted hover:text-white">&larr; Back to Dashboard</a>
        </div>

        <?php if ($message): ?> <div class="bg-green-900/50 text-green-300 p-4 rounded mb-6 border border-green-700"><?= $message ?></div> <?php endif; ?>
        <?php if ($error): ?> <div class="bg-red-900/50 text-red-300 p-4 rounded mb-6 border border-red-700"><?= $error ?></div> <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-1 space-y-8">

                <div class="bg-dark-surface p-6 rounded-lg border border-gray-700 shadow-lg">
                    <h2 class="text-xl font-bold mb-4 text-white">
                        <?= $editMode ? 'Edit Contest' : 'Create New Contest' ?>
                    </h2>

                    <form method="POST" action="contest_management.php">
                        <input type="hidden" name="save_contest" value="1">
                        <input type="hidden" name="contest_id" value="<?= $editMode ? $contestToEdit['id'] : '' ?>">

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Contest Name</label>
                                <input type="text" name="name" required class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 focus:border-brand-orange outline-none text-white"
                                       value="<?= $editMode ? htmlspecialchars($contestToEdit['name']) : '' ?>" placeholder="e.g. Midterm Exam">
                                <p class="text-xs text-gray-500 mt-1">A folder will be created in /contest_upload with this name.</p>
                            </div>

                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Course / Class</label>
                                <input type="text" name="course" class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 focus:border-brand-orange outline-none text-white"
                                       value="<?= $editMode ? htmlspecialchars($contestToEdit['course']) : '' ?>" placeholder="e.g. CS101">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm text-dark-muted mb-1">Start Time</label>
                                    <input type="datetime-local" name="start_time" required class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white"
                                           value="<?= $editMode ? date('Y-m-d\TH:i', strtotime($contestToEdit['start_time'])) : '' ?>">
                                </div>
                                <div>
                                    <label class="block text-sm text-dark-muted mb-1">End Time</label>
                                    <input type="datetime-local" name="end_time" required class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white"
                                           value="<?= $editMode ? date('Y-m-d\TH:i', strtotime($contestToEdit['end_time'])) : '' ?>">
                                </div>
                            </div>

                            <div class="flex gap-6 mt-2 pt-2 border-t border-gray-700">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" class="w-4 h-4 text-brand-orange bg-dark-bg border-gray-600 rounded"
                                        <?= ($editMode && $contestToEdit['is_active']) ? 'checked' : '' ?>>
                                    <span class="text-white text-sm">Active</span>
                                </label>

                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="is_public" value="1" class="w-4 h-4 text-brand-orange bg-dark-bg border-gray-600 rounded"
                                        <?= ($editMode && $contestToEdit['is_public']) ? 'checked' : '' ?>>
                                    <span class="text-white text-sm">Public</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-2">
                            <button type="submit" class="flex-1 bg-brand-orange hover:bg-orange-600 text-white font-bold py-2 px-4 rounded transition">
                                <?= $editMode ? 'UPDATE contest' : 'Create Contest' ?>
                            </button>
                            <?php if ($editMode): ?>
                                <a href="contest_management.php" class="bg-gray-600 hover:bg-gray-500 text-white py-2 px-4 rounded transition">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="bg-dark-surface p-6 rounded-lg border border-gray-700 shadow-lg">
                    <h2 class="text-xl font-bold mb-4 text-white">Add Problem to Contest</h2>
                    <form method="POST" action="contest_management.php">
                        <input type="hidden" name="add_problem" value="1">
                        <div class="space-y-4">

                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Select Contest</label>
                                <select name="contest_name_link" class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white">
                                    <?php foreach ($contests as $c): ?>
                                        <option value="<?= htmlspecialchars($c['name']) ?>" <?= ($editMode && $contestToEdit['id'] == $c['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['name']) ?> -  <?= htmlspecialchars($c['course']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Select Problem</label>
                                <select name="problem_code_link" class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white">
                                    <?php foreach ($allProblems as $p): ?>
                                        <option value="<?= htmlspecialchars($p['code']) ?>">
                                            [<?= htmlspecialchars($p['code']) ?>] <?= htmlspecialchars($p['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Order</label>
                                <input type="number" name="problem_order" value="0" class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white">
                            </div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded transition">Link Problem</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-dark-surface rounded-lg border border-gray-700 overflow-hidden shadow-lg">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-800 text-dark-muted text-xs uppercase border-b border-gray-700">
                                <th class="px-6 py-4 font-medium">ID</th>
                                <th class="px-6 py-4 font-medium">Name / Course</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 font-medium">Time Range</th>
                                <th class="px-6 py-4 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 text-sm">
                            <?php foreach ($contests as $row): ?>
                            <tr class="hover:bg-dark-hover transition">
                                <td class="px-6 py-4 text-dark-muted"><?= $row['id'] ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-white"><?= htmlspecialchars($row['name']) ?></div>
                                    <div class="text-xs text-dark-muted"><?= htmlspecialchars($row['course']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($row['is_active']): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-900 text-green-300 border border-green-700">Active</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-300 border border-gray-600">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-gray-400">
                                    <div class="text-xs">
                                        Start: <?= $row['start_time'] ?><br>
                                        End: <?= $row['end_time'] ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="contest_management.php?edit_id=<?= $row['id'] ?>" class="text-brand-orange hover:text-white transition">Edit</a>
                                    <form method="POST" action="contest_management.php" class="inline-block" onsubmit="return confirm('Delete this contest?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="text-brand-red hover:text-red-400 transition ml-2">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</body>
</html>