<?php
require_once 'db.php';

class Problem {
    // Create
    public static function create($code, $lc_id, $lc_link, $title, $desc, $level, $time, $memory, $tag) {
        $sql = "INSERT INTO problem (code, Leetcode_ID, Leetcode_link, title, description, level, time_limit_ms, memory_limit_mb, tag)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = DB::connect()->prepare($sql);
        return $stmt->execute([$code, $lc_id, $lc_link, $title, $desc, $level, $time, $memory, $tag]);
    }

    // Read (All)
    public static function getAll() {
        return DB::connect()->query("SELECT * FROM problem")->fetchAll();
    }

    // Read (One)
    public static function getById($id) {
        $stmt = DB::connect()->prepare("SELECT * FROM problem WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }    // Read (One)
    public static function getByCode($code) {
        $stmt = DB::connect()->prepare("SELECT * FROM problem WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    // Update
    public static function update($id, $code, $lc_id, $lc_link, $title, $desc, $level, $time, $memory, $tag) {
        $sql = "UPDATE Problem 
                SET code=?, Leetcode_ID=?, Leetcode_link=?, title=?, description=?, level=?, time_limit_ms=?, memory_limit_mb=?, tag=? 
                WHERE id=?";
        $stmt = DB::connect()->prepare($sql);
        return $stmt->execute([$code, $lc_id, $lc_link, $title, $desc, $level, $time, $memory, $tag, $id]);
    }

    // Delete
    public static function delete($id) {
        $stmt = DB::connect()->prepare("DELETE FROM problem WHERE id = ?");
        return $stmt->execute([$id]);
    }

     public static function getPage($limit, $offset) {
        $stmt = DB::connect()->prepare("SELECT * FROM problem WHERE code LIKE 'G%' ORDER BY id ASC LIMIT ? OFFSET ?");
        // PDO needs integers for LIMIT/OFFSET
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Count total problems (for calculating total pages)
    public static function count() {
        return DB::connect()->query("SELECT COUNT(*) FROM problem  WHERE code LIKE 'G%'")->fetchColumn();
    }

    public static function getCountsByLevel() {
        $db = DB::connect();
        $sql = "SELECT level, COUNT(*) as cnt FROM problem GROUP BY level";
        // Returns array like: ['Easy' => 10, 'Medium' => 5]
        return $db->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
    }

}

   
?>