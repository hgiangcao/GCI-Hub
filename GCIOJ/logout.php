<?php
// logout.php
session_start();

// Unset all session values
$_SESSION = array();

// Destroy the session cookie (important for security)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session storage
session_destroy();

// Redirect to Login
header("Location: login.php");
exit;
?>