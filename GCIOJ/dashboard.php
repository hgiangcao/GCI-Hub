<?php
// 1. Include auth.php FIRST.
// This automatically handles session_start() and redirects if not logged in.
require_once 'auth.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - GCIOJ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #1a1a1a; color: #fff; }
        .navbar { background-color: #282828; border-bottom: 1px solid #3e3e3e; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark px-4">
        <a class="navbar-brand fw-bold" href="#">GCIOJ</a>
        <div class="ms-auto d-flex align-items-center">
            <span class="me-3 text-secondary">
                Hello, <?= htmlspecialchars($_SESSION['name']) ?> 
                (<?= htmlspecialchars($_SESSION['student_id']) ?>)
            </span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Welcome to the Online Judge</h1>
        <p class="lead text-secondary">Select a problem to start coding.</p>
        
        <div class="card bg-secondary text-white mt-4">
            <div class="card-body">
                <h5 class="card-title">Quick Stats</h5>
                <p>Class: <?= htmlspecialchars($_SESSION['class'] ?? 'N/A') ?></p>
            </div>
        </div>
    </div>

</body>
</html>