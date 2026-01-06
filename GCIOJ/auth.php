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
?>