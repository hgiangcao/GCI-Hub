<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user_id is missing from session
if (!isset($_SESSION['student_id'])) {
    // Redirect to login page
    header("Location: login.php");
    exit;
}


// 2. Check Admin Permission
// (Redirects normal students back to their dashboard if they try to access this)
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}


?>