<?php
require_once 'db.php';

class Submission {

    // --- NEW METHOD: Used by submit.php (Synchronous Grading) ---
    // Saves score immediately, does NOT save source_code
    public static function saveGraded($student_id, $contest_id, $problem_id, $lang, $status, $score) {
        $sql = "INSERT INTO submission (student_id, contest_id, problem_id, language, status, score, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = DB::connect()->prepare($sql);
        $stmt->execute([$student_id, $contest_id, $problem_id, $lang, $status, $score]);
        return DB::connect()->lastInsertId();
    }

    // --- OLD METHOD: For Async Judge (Optional) ---
    // Updated to remove source_code and default score to 0
    public static function create($student_id, $contest_id, $problem_id, $lang) {
        $sql = "INSERT INTO submission (student_id, contest_id, problem_id, language, status, score, created_at)
                VALUES (?, ?, ?, ?, 'Pending', 0, NOW())";
        $stmt = DB::connect()->prepare($sql);
        $stmt->execute([$student_id, $contest_id, $problem_id, $lang]);
        return DB::connect()->lastInsertId();
    }

    // Read (All)
    public static function getAll() {
        $sql = "SELECT s.id, stu.name as student_name, p.title as problem_title, s.status, s.score, s.created_at
                FROM submission s
                JOIN student stu ON s.student_id = stu.id
                JOIN problem p ON s.problem_id = p.id
                ORDER BY s.created_at DESC";
        return DB::connect()->query($sql)->fetchAll();
    }

    // Read (One)
    public static function getById($id) {
        $stmt = DB::connect()->prepare("SELECT * FROM submission WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Update Status & Score
    public static function updateStatus($id, $status, $score) {
        $sql = "UPDATE submission SET status=?, score=? WHERE id=?";
        $stmt = DB::connect()->prepare($sql);
        return $stmt->execute([$status, $score, $id]);
    }

    // Delete
    public static function delete($id) {
        $stmt = DB::connect()->prepare("DELETE FROM submission WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Check if a student has submitted a specific problem in a specific contest
    public static function hasSubmitted($studentId, $contestId, $problemId) {
        $db = DB::connect();
        $sql = "SELECT COUNT(*) FROM submission
                WHERE student_id = ? AND contest_id = ? AND problem_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$studentId, $contestId, $problemId]);
        return $stmt->fetchColumn() > 0;
    }

    // Get count of UNIQUE solved problems by level for a student
    public static function getSolvedStats($studentId) {
        $db = DB::connect();
        $sql = "SELECT p.level, COUNT(DISTINCT s.problem_id)
                FROM submission s
                JOIN problem p ON s.problem_id = p.id
                WHERE s.student_id = ? AND s.status = 'Accepted'
                GROUP BY p.level";
        $stmt = $db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    // Get recent submissions (Updated to include Score in select if needed)
    public static function getRecent($studentId, $limit = 10) {
        $db = DB::connect();
        // s.* includes 'score' if your DB table has it
        $sql = "SELECT s.*, p.title, p.code 
                FROM submission s
                JOIN problem p ON s.problem_id = p.id
                WHERE s.student_id = ?
                ORDER BY s.id DESC
                LIMIT $limit";
        $stmt = $db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
}
?>