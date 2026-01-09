<?php
require_once 'db.php';

class Contest {
    // Create (Updated with flags)
    public static function create($name, $course, $start, $end, $isActive, $isPublic) {
        $sql = "INSERT INTO contest (name, course, start_time, end_time, is_active, is_public) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = DB::connect()->prepare($sql);
        return $stmt->execute([$name, $course, $start, $end, $isActive, $isPublic]);
    }

    // Update (Updated with flags)
    public static function update($id, $name, $course, $start, $end, $isActive, $isPublic) {
        $sql = "UPDATE contest SET name=?, course=?, start_time=?, end_time=?, is_active=?, is_public=? WHERE id=?";
        $stmt = DB::connect()->prepare($sql);
        return $stmt->execute([$name, $course, $start, $end, $isActive, $isPublic, $id]);
    }

    // ... (Keep getAll, getById, delete, addProblem as they were before) ...
public static function getAll() {
        // Sort by Active (1) first, then by ID (newest first)
        return DB::connect()->query("SELECT * FROM contest ORDER BY is_active DESC, id DESC")->fetchAll();
    }
    public static function getById($id) {
        $stmt = DB::connect()->prepare("SELECT * FROM contest WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public static function delete($id) {
        $stmt = DB::connect()->prepare("DELETE FROM contest WHERE id = ?");
        return $stmt->execute([$id]);
    }
    public static function addProblem($contestId, $problemId, $order = 0) {
        $sql = "INSERT IGNORE INTO contest_problem (contest_id, problem_id, problem_order) VALUES (?, ?, ?)";
        $stmt = DB::connect()->prepare($sql);
        return $stmt->execute([$contestId, $problemId, $order]);
    }

    // Find contest by Name
    public static function getByName($name) {
        $stmt = DB::connect()->prepare("SELECT * FROM contest WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }

    public static function getProblems($contestId) {
        $sql = "SELECT p.*, cp.problem_order 
                FROM problem p
                JOIN contest_problem cp ON p.id = cp.problem_id
                WHERE cp.contest_id = ? 
                ORDER BY cp.problem_order ASC";
        $stmt = DB::connect()->prepare($sql);
        $stmt->execute([$contestId]);
        return $stmt->fetchAll();
    }


    public static function getProblemsByName($contestName) {
        $sql = "SELECT p.*, cp.problem_order
                FROM problem p
                JOIN contest_problem cp ON p.id = cp.problem_id
                WHERE cp.contest_name = ?
                ORDER BY cp.problem_order ASC";
        $stmt = DB::connect()->prepare($sql);
        $stmt->execute([$contestId]);
        return $stmt->fetchAll();
    }
}
?>