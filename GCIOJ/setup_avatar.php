<?php
session_start();
require_once 'student.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";

// --- HANDLE SELECTION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['avatar'])) {
    $selected_avatar = $_POST['avatar'];
    
    // Simple validation: check if file exists
    if (file_exists("avt_img/" . $selected_avatar)) {
        if (Student::updateAvatar($user_id, $selected_avatar)) {
            $_SESSION['avatar_img'] = $selected_avatar;
            $message = "Avatar updated successfully!";
        } else {
            $error = "Failed to update avatar in database.";
        }
    } else {
        $error = "Invalid avatar selection.";
    }
}

// --- LOAD AVATARS ---
$avatars = glob("avt_img/*.png");
// Sort them naturally if they are like (1).png, (2).png...
natsort($avatars);
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Avatar - GCIOJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#1a1a1a', surface: '#282828', hover: '#3e3e3e', input: '#202020' },
                        brand: { orange: '#ffa116', green: '#2cbb5d' }
                    }
                }
            }
        }
    </script>
    <style>
        .avatar-grid {
            display: grid;
            grid-template-columns: repeat(10, minmax(0, 1fr));
            gap: 0.5rem;
        }
        @media (max-width: 1024px) { .avatar-grid { grid-template-columns: repeat(8, minmax(0, 1fr)); } }
        @media (max-width: 768px) { .avatar-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
    </style>
</head>
<body class="bg-dark-bg text-gray-200 font-sans min-h-screen flex flex-col antialiased">

    <?php include_once 'nav.php'; ?>

    <main class="flex-grow p-6 flex flex-col items-center">
        <div class="max-w-5xl w-full bg-dark-surface p-8 rounded-2xl border border-gray-700 shadow-2xl">
            
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-brand-orange mb-2">
                    <i class="fa-solid fa-user-circle mr-2"></i> Select Your Avatar
                </h1>
                <p class="text-gray-400">Choose an image that represents you best.</p>
            </div>

            <?php if ($message): ?>
                <div class="bg-green-900/30 border border-green-700 text-green-400 p-4 rounded-lg mb-6 text-center">
                    <i class="fa-solid fa-check-circle mr-2"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-900/30 border border-red-700 text-red-400 p-4 rounded-lg mb-6 text-center">
                    <i class="fa-solid fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="avatarForm">
                <input type="hidden" name="avatar" id="selectedAvatarInput">
                <div class="avatar-grid">
                    <?php foreach ($avatars as $avatar_path): ?>
                        <?php 
                            $avatar_file = basename($avatar_path); 
                            $isSelected = (isset($_SESSION['avatar_img']) && $_SESSION['avatar_img'] === $avatar_file);
                        ?>
                        <div class="relative group cursor-pointer transition-transform hover:scale-110" 
                             onclick="selectAvatar('<?= htmlspecialchars($avatar_file) ?>')">
                            <img src="<?= htmlspecialchars($avatar_path) ?>" 
                                 alt="Avatar" 
                                 class="w-full h-auto rounded-lg border-2 <?= $isSelected ? 'border-brand-orange ring-2 ring-brand-orange' : 'border-gray-700 hover:border-brand-orange/50' ?> transition-all">
                            <?php if ($isSelected): ?>
                                <div class="absolute top-0 right-0 bg-brand-orange text-white text-[10px] px-1 rounded-bl-lg font-bold">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </main>

    <script>
        function selectAvatar(avatarFile) {
            document.getElementById('selectedAvatarInput').value = avatarFile;
            document.getElementById('avatarForm').submit();
        }
    </script>

    <?php include_once 'footer.php'; ?>
</body>
</html>
