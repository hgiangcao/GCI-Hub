<?php
// --- 1. CONTROLLER LOGIC ---
session_start();
require_once 'auth.php';
require_once 'course.php'; // Assumes Class Course exists with static CRUD methods

$message = "";
$error = "";
$editMode = false;
$courseToEdit = null;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // A. DELETE
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (Course::delete($_POST['id'])) {
            $message = "Course deleted successfully.";
        } else {
            $error = "Failed to delete course.";
        }
    }

    // B. CREATE / UPDATE
    elseif (isset($_POST['save_course'])) {
        $name = $_POST['name'];
        $code = $_POST['code'];
        $year = intval($_POST['year']);
        $semester = $_POST['semester'];
        $department = $_POST['department'];

        if (isset($_POST['course_id']) && !empty($_POST['course_id'])) {
            // Update
            if (Course::update($_POST['course_id'], $name,$code, $year, $semester, $department)) {
                $message = "Course updated successfully.";
            } else {
                $error = "Failed to UPDATE course.";
            }
        } else {
            // Create
            if (Course::create($name,$code, $year, $semester, $department)) {
                $message = "Course created successfully.";
            } else {
                $error = "Failed to create course.";
            }
        }
    }
}

// Handle "Edit" Mode
if (isset($_GET['edit_id'])) {
    $courseToEdit = Course::getById($_GET['edit_id']);
    if ($courseToEdit) $editMode = true;
}

// Fetch Data
$courses = Course::getAll();
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Course Manager - GCIOJ</title>
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
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen p-6">

    <div class="max-w-7xl mx-auto">

        <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-700">
            <div>
                <h1 class="text-3xl font-bold text-brand-orange">Course Manager</h1>
                <p class="text-dark-muted mt-1">Manage academic courses, years, and departments.</p>
            </div>
            <a href="dashboard.php" class="text-dark-muted hover:text-white">&larr; Back to Dashboard</a>
        </div>

        <?php if ($message): ?> <div class="bg-green-900/50 text-green-300 p-4 rounded mb-6 border border-green-700"><?= $message ?></div> <?php endif; ?>
        <?php if ($error): ?> <div class="bg-red-900/50 text-red-300 p-4 rounded mb-6 border border-red-700"><?= $error ?></div> <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-1 space-y-8">
                <div class="bg-dark-surface p-6 rounded-lg border border-gray-700 shadow-lg">
                    <h2 class="text-xl font-bold mb-4 text-white">
                        <?= $editMode ? 'Edit Course' : 'Create New Course' ?>
                    </h2>

                    <form method="POST" action="course_management.php">
                        <input type="hidden" name="save_course" value="1">
                        <input type="hidden" name="course_id" value="<?= $editMode ? $courseToEdit['id'] : '' ?>">

                        <div class="space-y-4">

                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Course Name</label>
                                <input type="text" name="name" required class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 focus:border-brand-orange outline-none text-white"
                                       value="<?= $editMode ? htmlspecialchars($courseToEdit['name']) : '' ?>" placeholder="e.g. Introduction to Programming">
                            </div>
                             <div>
                                <label class="block text-sm text-dark-muted mb-1">Course Code</label>
                                <input type="text" name="code" required class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 focus:border-brand-orange outline-none text-white"
                                       value="<?= $editMode ? htmlspecialchars($courseToEdit['code']) : '' ?>" placeholder="e.g. APP">
                            </div>
                            <div>
                                <label class="block text-sm text-dark-muted mb-1">Department</label>
                                <input type="text" name="department" required class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 focus:border-brand-orange outline-none text-white"
                                       value="<?= $editMode ? htmlspecialchars($courseToEdit['department']) : '' ?>" placeholder="e.g. Computer Science">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm text-dark-muted mb-1">Year</label>
                                    <input type="number" name="year" required class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white"
                                           value="<?= $editMode ? htmlspecialchars($courseToEdit['year']) : date('Y')-1911 ?>">
                                </div>
                                <div>
                                    <label class="block text-sm text-dark-muted mb-1">Semester</label>
                                    <select name="semester" class="w-full bg-dark-bg border border-gray-600 rounded px-3 py-2 text-white">
                                        <option value="Fall" <?= ($editMode && $courseToEdit['semester'] == 'Fall') ? 'selected' : '' ?>>Fall</option>
                                        <option value="Spring" <?= ($editMode && $courseToEdit['semester'] == 'Spring') ? 'selected' : '' ?>>Spring</option>
                                        <option value="Summer" <?= ($editMode && $courseToEdit['semester'] == 'Summer') ? 'selected' : '' ?>>Summer</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="mt-6 flex gap-2">
                            <button type="submit" class="flex-1 bg-brand-orange hover:bg-orange-600 text-white font-bold py-2 px-4 rounded transition">
                                <?= $editMode ? 'UPDATE Course' : 'Create Course' ?>
                            </button>
                            <?php if ($editMode): ?>
                                <a href="course_management.php" class="bg-gray-600 hover:bg-gray-500 text-white py-2 px-4 rounded transition">Cancel</a>
                            <?php endif; ?>
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
                                <th class="px-6 py-4 font-medium">Course Name</th>
                                <th class="px-6 py-4 font-medium">Department</th>
                                <th class="px-6 py-4 font-medium">Year / Sem</th>
                                <th class="px-6 py-4 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 text-sm">
                            <?php if (count($courses) > 0): ?>
                                <?php foreach ($courses as $row): ?>
                                <tr class="hover:bg-dark-hover transition">
                                    <td class="px-6 py-4 text-dark-muted"><?= $row['id'] ?></td>
                                    <td class="px-6 py-4 font-bold text-white">
                                        <?= htmlspecialchars($row['name']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-300">
                                        <?= htmlspecialchars($row['department']) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-300 border border-gray-600">
                                            <?= $row['year'] ?> - <?= $row['semester'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="course_management.php?edit_id=<?= $row['id'] ?>" class="text-brand-orange hover:text-white transition">Edit</a>
                                        <form method="POST" action="course_management.php" class="inline-block" onsubmit="return confirm('Delete this course?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="text-brand-red hover:text-red-400 transition ml-2">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-dark-muted">No courses found. Create one to get started.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</body>
</html>