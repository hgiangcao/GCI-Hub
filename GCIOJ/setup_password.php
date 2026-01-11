<?php
// --- CONFIGURATION ---
session_start();
require_once 'student.php'; // Using your provided class

$name="";

function get_list_default_password(){
    $csvFilePath = 'default_pass.csv';
    if (($handle = fopen($csvFilePath, 'r')) !== FALSE) {
      // Loop through each row of the CSV file
        $row = fgetcsv($handle, 1000, ',');


         fclose($handle);
        return $row;
    }

      // Close the CSV file
      fclose($handle);
      return array();
}
$list_password  = get_list_default_password();

$message = "";
$error = "";
$step = 1; // 1: Input, 2: Confirm, 3: Success

// Variables
$id = $_POST['id'] ?? '';
$eng_name = $_POST['eng_name'] ?? '';
$selected_password = $_POST['selected_word'] ?? '';

// --- HANDLE SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // STAGE 1: User selected a word
    if (isset($_POST['select_word_btn'])) {
        $id = strtoupper(trim($_POST['id']));
        $eng_name = trim($_POST['eng_name']);
        $selected_password = $_POST['select_word_btn'];

        if (empty($id) || empty($eng_name)) {
            $error = "Please enter your Student ID and English Name.";
        } else {
            // Check if student exists using your Class
            $existing = Student::getByStudentId($id);
            $name = $existing['name'];
            // Logic: If they exist and have a password that isn't the default "123456" (or empty), block them.
            if ($existing && !empty($existing['password']) && $existing['password'] !== 'pass') {
                $error = "Student <b>" . htmlspecialchars($id) . "-" . htmlspecialchars($name) . "</b> already set password. Contact teacher to reset.";
                $step = 1;
            } else {
                // Good to go
                $step = 2;
            }
        }
    }

    // STAGE 2: User Confirmed
    elseif (isset($_POST['confirm_setup'])) {
        $step = 2; // Default to step 2 in case of error

        // 1. Check if student exists again
        $existing = Student::getByStudentId($id);
        $cn_name = $existing['name'];
        try {
            if ($existing) {
                // UPDATE EXISTING STUDENT
                // Note: update($id, $name, $english_name, $class, $password)
                // We keep their existing 'name' and 'class' to avoid wiping data
                $result = Student::update(
                    $existing['id'],
                    $existing['name'],
                    $eng_name,
                    $existing['class'],
                    $selected_password
                );
            } else {
                // CREATE NEW STUDENT
                // Note: create($student_id, $name, $english_name, $class, $password)
                // We don't have 'class' or 'name' (Chinese) from this simple form, so we use English name for Name and empty string for Class.
                $result = Student::create(
                    $id,
                    $cn_name, // Map English Name to Name
                    $eng_name,
                    '',        // Empty Class
                    $selected_password
                );
            }

            if ($result) {
                // --- NEW: SAVE TO CSV ---
                $csvFile = 'login_info.csv';
                $fileHandle = fopen($csvFile, 'a'); // Open in Append Mode
                if ($fileHandle) {
                    // Add Header if file is empty
                    if (filesize($csvFile) == 0) {
                        fputcsv($fileHandle, ['Student ID', 'Name', 'Password', 'Date']);
                    }
                    // Write Data
                    fputcsv($fileHandle, [$id,$cn_name, $eng_name, $selected_password]);
                    fclose($fileHandle);
                }
                $message = "Success!";
                $step = 3; // Success State
            } else {
                $error = "Database Error: Could not save student.";
            }

        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Setup Password - GCI Exam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', input: '#202020' },
                        brand: { orange: '#ffa116', green: '#2cbb5d' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-gray-200 font-sans min-h-screen p-6 flex items-center justify-center">

    <div class="max-w-3xl w-full bg-dark-surface p-8 rounded-2xl border border-gray-700 shadow-2xl">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-brand-orange mb-2">
                <i class="fa-solid fa-key mr-2"></i> Setup Password
            </h1>
            <p class="text-gray-400 text-sm">GCI Exam Submission System</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-white-900/30 border border-red-700 text-red-400 p-4 rounded-lg mb-6 text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 3): ?>
            <div class="text-center space-y-6">
                <div class="w-20 h-20 bg-green-900/30 rounded-full flex items-center justify-center mx-auto">
                    <i class="fa-solid fa-check text-4xl text-brand-green"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-white">Password Setup Successful</h2>
                    <p class="text-gray-400 mt-2">Username: <span class="text-brand-orange font-mono text-xl font-bold"><?= htmlspecialchars($id) ?></span></p>
                    <p class="text-gray-400 mt-2">Password: <span class="text-brand-orange font-mono text-xl font-bold"><?= htmlspecialchars($selected_password) ?></span></p>
                </div>
                <div class="bg-yellow-900/20 border border-yellow-700/50 p-4 rounded text-yellow-500 text-sm">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Please write this down or take a picture now.
                </div>
                <a href="index.php" class="inline-block bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded transition">Return Home</a>
            </div>

        <?php else: ?>
            <form method="POST">
                <?php if ($step === 2): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                    <input type="hidden" name="eng_name" value="<?= htmlspecialchars($eng_name) ?>">
                    <input type="hidden" name="cn_name" value="<?= htmlspecialchars($cn_name) ?>">
                    <input type="hidden" name="selected_word" value="<?= htmlspecialchars($selected_password) ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Student ID</label>
                        <input type="text" name="id" value="<?= htmlspecialchars($id) ?>" required
                               <?= $step === 2 ? 'readonly' : '' ?>
                               class="w-full bg-dark-input border border-gray-600 rounded px-4 py-3 text-white focus:border-brand-orange outline-none transition <?= $step === 2 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-400 mb-1">English Name</label>
                        <input type="text" name="eng_name" value="<?= htmlspecialchars($eng_name) ?>" required
                               <?= $step === 2 ? 'readonly' : '' ?>
                               class="w-full bg-dark-input border border-gray-600 rounded px-4 py-3 text-white focus:border-brand-orange outline-none transition <?= $step === 2 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    </div>
                                  <div>
                        <label class="block text-sm text-gray-400 mb-1">Name</label>
                        <input type="text" name="cn_name" value="<?= htmlspecialchars($name) ?>" readonly
                               class="w-full bg-gray-500 border border-gray-600 rounded px-4 py-3 text-white ?>">
                    </div>
                </div>

                <?php if ($step === 1): ?>
                    <div class="mb-4 text-center">
                        <p class="text-white font-medium mb-4">Tap a word below to choose it as your password:</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <?php foreach ($list_password as $word): ?>
                                <button type="submit" name="select_word_btn" value="<?= $word ?>"
                                        class="bg-gray-700 hover:bg-brand-orange hover:text-white text-gray-200 py-3 rounded border border-gray-600 transition duration-200 font-semibold">
                                    <?= $word ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($step === 2): ?>
                    <div class="bg-dark-input p-6 rounded-lg border border-gray-600 text-center mb-6">
                        <p class="text-gray-400 mb-2">Selected Password</p>
                        <div class="text-3xl font-bold text-brand-orange tracking-wider mb-6">
                            <?= htmlspecialchars($selected_password) ?>
                        </div>
                        <div class="flex gap-4 justify-center">
                            <a href="setup_password.php" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-2 rounded transition">Change Word</a>
                            <button type="submit" name="confirm_setup" class="bg-brand-green hover:bg-green-600 text-white px-8 py-2 rounded font-bold transition shadow-lg shadow-green-900/20">
                                Confirm & Save
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

            </form>
        <?php endif; ?>

    </div>

</body>
</html>