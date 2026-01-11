<?php
require_once 'db.php';

class Course {

    // --- 1. READ ALL ---
    public static function getAll() {
        $sql = "SELECT * FROM course ORDER BY year DESC, semester ASC, name ASC";
        $stmt = DB::connect()->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- 2. READ ONE ---
    public static function getById($id) {
        $sql = "SELECT * FROM course WHERE id = ?";
        $stmt = DB::connect()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- 2. READ ONE ---
    public static function getByCode($code) {
        $sql = "SELECT * FROM course WHERE code = ?";
        $stmt = DB::connect()->prepare($sql);
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // --- 3. CREATE ---
    public static function create($name,$code, $year, $semester, $department) {
        $sql = "INSERT INTO course (name,code, year, semester, department) VALUES (?,?, ?, ?, ?)";
        try {
            $stmt = DB::connect()->prepare($sql);
            return $stmt->execute([$name,$code, $year, $semester, $department]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // --- 4. UPDATE ---
    public static function update($id, $name,$code, $year, $semester, $department) {
        $sql = "UPDATE course SET name = ?,code = ?, year = ?, semester = ?, department = ? WHERE id = ?";
        try {
            $stmt = DB::connect()->prepare($sql);
            return $stmt->execute([$name,$code, $year, $semester, $department, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // --- 5. DELETE ---
    public static function delete($id) {
        $sql = "DELETE FROM course WHERE id = ?";
        try {
            $stmt = DB::connect()->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>