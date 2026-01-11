<?php
// --- CONFIGURATION ---
session_start();
require_once 'auth.php';       // Verify Login
require_once 'check_admin.php'; // Verify Admin Access
require_once 'student.php'; // Verify Admin Access
require_once 'course.php'; // Assumes Class Course exists with static CRUD methods


// Database Connection
$host = 'localhost';
$dbname = 'db_gcioj';
$user = 'root';
$pass = '';
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$message = "";
$error = "";

// --- HANDLE SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $raw_ids = $_POST['student_ids'];

    if (empty($course_id) || empty($raw_ids)) {
        $error = "Please select a course and enter student IDs.";
    } else {
        $student_codes = explode("\n", str_replace("\r", "", $raw_ids));
        $success = 0;
        $fail = 0;

        // Prepare statements
        $stmt_get_stu = $pdo->prepare("SELECT id FROM student WHERE student_id = ?"); // Assuming column is 'student_id' based on previous context
        $stmt_register = $pdo->prepare("INSERT IGNORE INTO registration (student_id, course_id) VALUES (?, ?)");

        foreach ($student_codes as $code) {
            $code = trim($code);
            if (empty($code)) continue;

            // 1. Find Student Internal ID
            $stmt_get_stu->execute([$code]);
            $student = $stmt_get_stu->fetch();

            if ($student) {
                // 2. Register to Course
                $stmt_register->execute([$student['id'], $course_id]);
                if ($stmt_register->rowCount() > 0) {
                    $success++;
                } else {
                    $fail++; // Already registered
                }
            } else {
                $fail++; // Student ID not found
            }
        }
        $message = "Process complete: $success registered, $fail failed/skipped.";
    }
}

// --- FETCH COURSES ---
$courses = $pdo->query("SELECT id, name,code,year, name FROM course ORDER BY year")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Register Students</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', input: '#202020' },
                        brand: { orange: '#ffa116' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-gray-200 font-sans min-h-screen p-6">

    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6 border-b border-gray-700 pb-4">
            <h1 class="text-2xl font-bold text-brand-orange">Course Registration</h1>
            <a href="dashboard.php" class="text-gray-400 hover:text-white">&larr; Dashboard</a>
        </div>

        <?php if ($message): ?> <div class="bg-green-900/40 text-green-400 p-3 rounded mb-4 border border-green-800"><?= $message ?></div> <?php endif; ?>
        <?php if ($error): ?>   <div class="bg-red-900/40 text-red-400 p-3 rounded mb-4 border border-red-800"><?= $error ?></div> <?php endif; ?>

        <div class="bg-dark-surface p-6 rounded-lg border border-gray-700 shadow-lg">
            <form method="POST">

                <div class="mb-4">
                    <label class="block text-sm text-gray-400 mb-2">Select Course</label>
                    <select name="course_id" required
                            class="w-full bg-dark-input border border-gray-600 rounded p-2 text-white focus:border-brand-orange outline-none">
                        <option value="">-- Choose a Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>">
                                <?= htmlspecialchars($c['code'] . " - " . $c['year']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm text-gray-400 mb-2">Student IDs (One per line)</label>
                    <textarea name="student_ids" rows="8" required placeholder="U1001&#10;U1002&#10;U1003"
                              class="w-full bg-dark-input border border-gray-600 rounded p-2 text-white font-mono focus:border-brand-orange outline-none"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Students must already exist in the system.</p>
                </div>

                <button type="submit" class="w-full bg-brand-orange hover:bg-orange-600 text-white font-bold py-2 rounded transition">
                    Register Students
                </button>

            </form>
        </div>
    </div>

</body>
</html>