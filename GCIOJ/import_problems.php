<?php
// 1. Configuration
require_once 'db.php'; 

$csvUrl = 'LeetCode Problems - Sheet5.csv';

$defaultTimeMs = 1000;
$defaultMemoryMb = 256;
$defaultDesc = "Imported from LeetCode";

// 2. Connect to Database
try {
    $pdo = DB::connect();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 3. Get the starting ID (Next ID from the last ID in the table)
$startId = (int)$pdo->query("SELECT MAX(id) FROM problem")->fetchColumn() + 1;

// 4. Process CSV
if (($handle = fopen($csvUrl, "r")) !== FALSE) {
    
    fgetcsv($handle); // Skip Header Row

    echo "<h3>Importing Problems...</h3><ul>";

    // SQL now includes 'id' and matches the 4 required fields from CSV
    $sql = "INSERT INTO problem (
                id, code, Leetcode_ID, title, tag, Leetcode_link, 
                description, time_limit_ms, memory_limit_mb
            ) VALUES (
                :id, :code, :lc_id, :title, :tag, :lc_link, 
                :desc, :time, :memory
            )
            ON DUPLICATE KEY UPDATE 
                code = VALUES(code),
                Leetcode_ID = VALUES(Leetcode_ID),
                title = VALUES(title),
                tag = VALUES(tag),
                Leetcode_link = VALUES(Leetcode_link)";

    $stmt = $pdo->prepare($sql);
    $currentId = $startId;

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Mapping based on CSV structure:
        // [1]Name, [2]Tag, [4]LeetcodeID, [5]Leetcode Link
        $title = trim($data[1]);
        $tag   = trim($data[2]);
        $lcId  = trim($data[4]);
        $link  = trim($data[5]);
        
        // id and code are the same
        $codeValue = (string)$currentId;
        $idValue   = $currentId;

        try {
            $stmt->execute([
                ':id'      => $idValue,
                ':code'    => $codeValue,
                ':lc_id'   => $lcId,
                ':title'   => $title,
                ':tag'     => $tag,
                ':lc_link' => $link,
                ':desc'    => $defaultDesc,
                ':time'    => $defaultTimeMs,
                ':memory'  => $defaultMemoryMb
            ]);
            echo "<li>Imported: <b>$codeValue</b> - $title</li>";
            $currentId++; // Increment for the next record
        } catch (Exception $e) {
            echo "<li style='color:red;'>Error importing $title: " . $e->getMessage() . "</li>";
        }
    }

    fclose($handle);
    echo "</ul><h3>Import Complete.</h3>";
} else {
    echo "Error: Could not open CSV.";
}
?>