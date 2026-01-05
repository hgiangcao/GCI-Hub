<?php
// --- 1. CONTROLLER ---
session_start();
require_once 'auth.php'; // Ensure admin is logged in
require_once 'student.php';

$message = "";
$error = "";
$editMode = false;
$studentToEdit = null;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // A. DELETE
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (Student::delete($_POST['id'])) {
            $message = "Student deleted successfully.";
        } else {
            $error = "Failed to delete student.";
        }
    }

    // B. SAVE (Create or Update)
    elseif (isset($_POST['save_student'])) {
        $sid = trim($_POST['student_id']);
        $name = trim($_POST['username']);
        $pass = trim($_POST['password']);
        $class = trim($_POST['class']);
        $db_id = $_POST['db_id']; // Hidden ID field

        if (empty($sid) || empty($name)) {
            $error = "Student ID and Name are required.";
        } else {
            if (!empty($db_id)) {
                // UPDATE
                if (Student::update($db_id, $sid, $name, $name,  $class,$pass)) {
                    $message = "Student updated successfully.";
                } else {
                    $error = "Failed to update student.";
                }
            } else {
                // CREATE
                if (empty($pass)) $pass = "123456"; // Default password if empty on create
                if (Student::create($sid, $name, $name,  $class,$pass)){
                    $message = "Student added successfully.";
                } else {
                    $error = "Failed to add student. ID might already exist.";
                }
            }
        }
    }
}

// Handle Edit Mode (Get data to fill form)
if (isset($_GET['edit_id'])) {
    $studentToEdit = Student::getById($_GET['edit_id']);
    if ($studentToEdit) $editMode = true;
}

// Fetch All Students
$students = Student::getAll();
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Student Management - GCIOJ</title>
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
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen p-6">

    <div class="max-w-7xl mx-auto">

        <div class="flex justify-between items-center mb-8 border-b border-gray-700 pb-4">
            <div>
                <h1 class="text-3xl font-bold text-brand-orange">Student Management</h1>
                <p class="text-dark-muted mt-1">Add, edit, or remove students.</p>
            </div>
            <a href="index.php" class="text-dark-muted hover:text-white transition">&larr; Dashboard</a>
        </div>

        <?php if ($message): ?> <div class="bg-green-900/50 text-green-300 p-4 rounded mb-6 border border-green-700"><?= $message ?></div> <?php endif; ?>
        <?php if ($error): ?> <div class="bg-red-900/50 text-red-300 p-4 rounded mb-6 border border-red-700"><?= $error ?></div> <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-1">
                <div class="bg-dark-surface p-6 rounded-lg border border-gray-700 shadow-lg sticky top-6">
                    <h2 class="text-xl font-bold text-white mb-4"><?= $editMode ? 'Edit Student' : 'Add New Student' ?></h2>

                    <form method="POST">
                        <input type="hidden" name="save_student" value="1">
                        <input type="hidden" name="db_id" value="<?= $editMode ? $studentToEdit['id'] : '' ?>">

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Student ID</label>
                                <input type="text" name="student_id" required
                                       class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white focus:border-brand-orange outline-none"
                                       value="<?= $editMode ? htmlspecialchars($studentToEdit['student_id']) : '' ?>"
                                       placeholder="e.g. U123456">
                            </div>

                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Name / Username</label>
                                <input type="text" name="username" required
                                       class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white focus:border-brand-orange outline-none"
                                       value="<?= $editMode ? htmlspecialchars($studentToEdit['username']) : '' ?>"
                                       placeholder="e.g. John Doe">
                            </div>

                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Class</label>
                                <input type="text" name="class"
                                       class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white focus:border-brand-orange outline-none"
                                       value="<?= $editMode ? htmlspecialchars($studentToEdit['class']) : '' ?>"
                                       placeholder="e.g. CS101">
                            </div>

                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Password</label>
                                <input type="text" name="password"
                                       class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white focus:border-brand-orange outline-none"
                                       placeholder="<?= $editMode ? 'Leave empty to keep current' : 'Default: 123456' ?>">
                            </div>
                        </div>

                        <div class="mt-6 flex gap-2">
                            <button type="submit" class="flex-1 bg-brand-orange hover:bg-orange-600 text-white font-bold py-2 px-4 rounded transition">
                                <?= $editMode ? 'Update' : 'Add Student' ?>
                            </button>
                            <?php if ($editMode): ?>
                                <a href="StudentManagement.php" class="bg-gray-600 hover:bg-gray-500 text-white py-2 px-4 rounded transition text-center">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-dark-surface rounded-lg border border-gray-700 overflow-hidden shadow-lg">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-800 text-dark-muted text-xs uppercase border-b border-gray-700">
                                    <th class="px-6 py-4">ID</th>
                                    <th class="px-6 py-4">Student ID</th>
                                    <th class="px-6 py-4">Name</th>
                                    <th class="px-6 py-4">Class</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700 text-sm">
                                <?php if (count($students) > 0): ?>
                                    <?php foreach ($students as $stu): ?>
                                    <tr class="hover:bg-dark-hover transition">
                                        <td class="px-6 py-4 text-dark-muted">#<?= $stu['id'] ?></td>
                                        <td class="px-6 py-4 font-mono text-brand-orange"><?= htmlspecialchars($stu['student_id']) ?></td>
                                        <td class="px-6 py-4 font-medium text-white"><?= htmlspecialchars($stu['name']) ?></td>
                                        <td class="px-6 py-4 text-gray-400"><?= htmlspecialchars($stu['class']) ?></td>
                                        <td class="px-6 py-4 text-right space-x-2">
                                            <a href="StudentManagement.php?edit_id=<?= $stu['id'] ?>" class="text-brand-orange hover:text-white transition">Edit</a>

                                            <form method="POST" class="inline-block" onsubmit="return confirm('Delete <?= htmlspecialchars($stu['username']) ?>? This will delete all their submissions.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $stu['id'] ?>">
                                                <button type="submit" class="text-brand-red hover:text-red-400 transition ml-2">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="px-6 py-8 text-center text-dark-muted">No students found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>