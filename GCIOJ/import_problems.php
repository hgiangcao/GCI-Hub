<?php
// 1. Configuration
require_once 'db.php'; // Ensure db.php is in the same folder

// REPLACE THIS with your actual Google Sheet Published CSV Link
$csvUrl = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSygkiXnRFouwkddY9Lk2Rq0BPGwR8dsOYAZ8VKbhZRFgIjcNstYL7emnuZ6RB8fL2ajGZDjdgvbaO2/pub?gid=1754615259&single=true&output=csv';

// Default Settings
$defaultTimeMs = 1000; // 1s
$defaultMemoryMb = 256; // 256MB
$defaultDesc = "Imported from LeetCode"; // Placeholder for description

// 2. Connect to Database
try {
    $pdo = DB::connect();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 3. Process CSV
if (($handle = fopen($csvUrl, "r")) !== FALSE) {
    
    // Skip Header Row if your sheet has one. 
    // If your sheet starts directly with data, comment out this line:
    fgetcsv($handle); 

    echo "<h3>Importing Problems...</h3>";
    echo "<ul>";

    $sql = "INSERT INTO problem (
                code, Leetcode_ID, title, tag, level, Leetcode_link, 
                description, time_limit_ms, memory_limit_mb
            ) VALUES (
                :code, :lc_id, :title, :tag, :level, :lc_link, 
                :desc, :time, :memory
            )
            ON DUPLICATE KEY UPDATE 
                Leetcode_ID = VALUES(Leetcode_ID),
                title = VALUES(title),
                tag = VALUES(tag),
                level = VALUES(level),
                Leetcode_link = VALUES(Leetcode_link),
                time_limit_ms = VALUES(time_limit_ms),
                memory_limit_mb = VALUES(memory_limit_mb)";

    $stmt = $pdo->prepare($sql);

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Map CSV columns based on your provided order:
        // [0]=>code, [1]=>Leetcode_ID, [2]=>Name, [3]=>Tag, [4]=>Level, [5]=>Link
        
        $code = trim($data[0]);
        $lcId = trim($data[1]);
        $title = trim($data[2]);
        $tag = trim($data[3]); // Stores "['Array', '...']" as is
        $level = trim($data[4]);
        $link = trim($data[5]);

        try {
            $stmt->execute([
                ':code'    => $code,
                ':lc_id'   => $lcId,
                ':title'   => $title,
                ':tag'     => $tag,
                ':level'   => $level,
                ':lc_link' => $link,
                ':desc'    => $defaultDesc,
                ':time'    => $defaultTimeMs,
                ':memory'  => $defaultMemoryMb
            ]);
            echo "<li>Imported: <b>$code</b> - $title</li>";
        } catch (Exception $e) {
            echo "<li style='color:red;'>Error importing $code: " . $e->getMessage() . "</li>";
        }
    }

    fclose($handle);
    echo "</ul>";
    echo "<h3>Import Complete.</h3>";

} else {
    echo "Error: Could not open CSV URL. Make sure the sheet is published to the web.";
}
?>