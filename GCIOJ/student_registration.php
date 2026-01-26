<?php
// --- CONFIGURATION ---
session_start();
require_once 'auth.php';
require_once 'check_admin.php';
require_once 'student.php';
require_once 'course.php';
require_once 'db.php';

// Database Connection
$host = 'localhost';
$dbname = 'db_gcioj';
$user = 'root';
$pass = '';
$pdo = DB::connect();

$message = "";
$error = "";

// --- HANDLE SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $raw_data = $_POST['student_data']; // Changed variable name

    if (empty($course_id) || empty($raw_data)) {
        $error = "Please select a course and enter student data.";
    } else {
        $lines = explode("\n", str_replace("\r", "", $raw_data));
        $created = 0;
        $registered = 0;
        $skipped = 0; // Already registered

        // 1. Check if student exists (by external student_id)
        $stmt_check = $pdo->prepare("SELECT id FROM student WHERE student_id = ?");

        // 2. Create Student (Assuming DB column for name is 'name')
        $stmt_create = $pdo->prepare("INSERT INTO student (student_id, name) VALUES (?, ?)");

        // 3. Register to Course
        $stmt_reg = $pdo->prepare("INSERT IGNORE INTO registration (student_id, course_id) VALUES (?, ?)");

        $pdo->beginTransaction();

        try {
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // Split string by comma or whitespace. Limit to 2 parts (ID, Name)
                // Example: "U1001, 王小明" or "U1001 王小明"
                $parts = preg_split('/[\s,]+/', $line, 2);

                if (count($parts) < 2) continue; // Skip invalid lines

                $sid = trim($parts[0]);
                $name = trim($parts[1]);
                $internal_id = null;

                // A. Check Existence
                $stmt_check->execute([$sid]);
                $student = $stmt_check->fetch();

                if ($student) {
                    $internal_id = $student['id'];
                } else {
                    // B. Create New Student
                    $stmt_create->execute([$sid, $name]);
                    $internal_id = $pdo->lastInsertId();
                    $created++;
                }

                // C. Register to Course
                $stmt_reg->execute([$internal_id, $course_id]);
                if ($stmt_reg->rowCount() > 0) {
                    $registered++;
                } else {
                    $skipped++;
                }
            }
            $pdo->commit();
            $message = "Process Complete: Created $created new students. Registered $registered to course.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

// --- FETCH COURSES ---
$courses = $pdo->query("SELECT id, code, year, name FROM course ORDER BY year DESC, code ASC")->fetchAll();
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
                                <?= htmlspecialchars($c['code'] . " - " . $c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm text-gray-400 mb-2">Student Data</label>
                    <textarea name="student_data" rows="8" required placeholder="U1001, 王小明&#10;U1002, 李四"
                              class="w-full bg-dark-input border border-gray-600 rounded p-2 text-white font-mono focus:border-brand-orange outline-none"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Format per line: <code>Student_ID, Name</code> (separated by comma or space)</p>
                </div>

                <button type="submit" class="w-full bg-brand-orange hover:bg-orange-600 text-white font-bold py-2 rounded transition">
                    Register & Create Students
                </button>

            </form>
        </div>
    </div>

</body>
</html>