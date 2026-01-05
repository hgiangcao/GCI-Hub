<?php
require_once 'db.php';

class student {
    // Create
    public static function create($student_id, $name, $english_name, $class, $password) {
        $sql = "INSERT INTO student (student_id, name, english_name, class, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = DB::connect()->prepare($sql);
        return $stmt->execute([$student_id, $name, $english_name, $class, $password]);
    }

    // Read (All)
    public static function getAll() {
        return DB::connect()->query("SELECT * FROM student")->fetchAll();
    }

    // Read (One by ID)
    public static function getById($id) {
        $stmt = DB::connect()->prepare("SELECT * FROM student WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Read (By student ID/student_id)
    public static function getByStudentId($student_id) {
        $stmt = DB::connect()->prepare("SELECT * FROM student WHERE student_id = ?");
        $stmt->execute([$student_id]);
        return $stmt->fetch();
    }

    // Update
    public static function update($id, $name, $english_name, $class, $password) {
        $sql = "UPDATE student SET name=?, english_name=?, class=?, password=? WHERE id=?";
        $stmt = DB::connect()->prepare($sql);
        return $stmt->execute([$name, $english_name, $class, $password, $id]);
    }

    // Delete
    public static function delete($id) {
        $stmt = DB::connect()->prepare("DELETE FROM student WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Get global ranking based on unique problems solved
    public static function getRanking() {
        $db = DB::connect();
        // We join student with Submission to count 'PASS' results
        // COUNT(DISTINCT problem_id) ensures if they solved the same problem twice, it only counts once.
        $sql = "SELECT 
                    st.id, 
                    st.student_id, 
                    st.english_name, 
                    COUNT(DISTINCT sb.problem_id) as solved_count 
                FROM student st
                LEFT JOIN submission sb ON st.id = sb.student_id AND sb.status = 'Accepted'
                GROUP BY st.id
                ORDER BY solved_count DESC, st.id ASC"; // Tie-breaker: Lower ID (joined earlier) wins
        return $db->query($sql)->fetchAll();
    }
}
?>