<?php
// submit.php
session_start();
require_once 'submission.php'; // Changed from db.php to use the Model
require_once 'contest.php';
require_once 'problem.php';

// 1. Check Authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

// 2. Get POST Data
$data = json_decode(file_get_contents('php://input'), true);

$studentId    = $_SESSION['student_id']; // String ID (e.g. "U123456")
$dbId    = $_SESSION['user_id']; // String ID (e.g. "U123456")
$problemCode  = $data['problem_code'];
$contestName    = $data['contest_name'];
$problemId  = $data['problem_id'];
$contestId    = $data['contest_id'];
$pythonCode   = $data['code'];
$outputLog    = $data['output'];

// 3. Determine Result & Score
$result = "Wrong Answer"; // Default
$score = 0;

// Logic: Check for Error first (Looking for common Skulpt error strings)
if (strpos($outputLog, 'Runtime Error') !== false || strpos($outputLog, 'Compile Error') !== false || strpos($outputLog, 'Error:') !== false) {
    $result = "Compile Error";
    $score = 0;
}
else if (strpos($outputLog, 'Accepted') !== false ) {
    $result = "Accepted";
    $score = 100;
}
// Logic: Check for "Final Score: X/Y" (Standard Skulpt Grader Output)
else if(preg_match('/Final Score:\s*(\d+)\s*\/\s*(\d+)/', $outputLog, $matches)) {
    $earned = intval($matches[1]); // 4
    $total  = intval($matches[2]); // 100

    $score = $earned ;
    $result = "Wrong Answer";

}

// 4. Save File to: contest_upload/[ContestName]/[student_id]/[problem_code].py

// Define Path
$uploadDir = "contest_upload/" . $contestName . "/" . $studentId;

// Create directory recursively if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Filename: FND_MAX.py
$filename = $studentId."_".$problemCode . ".py";
$filePath = $uploadDir . "/" . $filename;

file_put_contents($filePath,$pythonCode);

Submission::saveGraded($dbId , $contestId, $problemId, 'python', $result, $score);

// Return JSON response
echo json_encode(['status' => 'success', 'result' => $result, 'score' => $score]);
?>