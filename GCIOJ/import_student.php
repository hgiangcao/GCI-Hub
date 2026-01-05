<?php
// 1. Configuration
$host = '127.0.0.1';
$db   = 'db_gcioj';
$user = 'root';
$pass = ''; // No password
$charset = 'utf8mb4';

// REPLACE with your actual Google Sheet Published CSV Link
$csvUrl = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSygkiXnRFouwkddY9Lk2Rq0BPGwR8dsOYAZ8VKbhZRFgIjcNstYL7emnuZ6RB8fL2ajGZDjdgvbaO2/pub?gid=0&single=true&output=csv';
$defaultClass = 'MCUT_CLASS';

// 2. Connect
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// 3. Process CSV
if (($handle = fopen($csvUrl, "r")) !== FALSE) {
    
    fgetcsv($handle); // Skip Header

    echo "<h3>Importing Students...</h3><ul>";

    // Prepare Statement with English Name
    $stmt = $pdo->prepare("
        INSERT INTO student (student_id, name, english_name, class, password)
        VALUES (:student_id, :cname, :ename, :class, :password)
        ON DUPLICATE KEY UPDATE 
            name = VALUES(name), 
            english_name = VALUES(english_name),
            class = VALUES(class),
            password = VALUES(password)
    ");

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // CSV: [0]=>ID, [1]=>ChineseName, [2]=>EnglishName, [3]=>Pass
        $studentId = trim($data[0]); 
        $chineseName = trim($data[1]);
        $englishName = trim($data[2]);
        $password = trim($data[3]); 
        $class = trim($data[4]); 

        try {
            $stmt->execute([
                ':student_id' => $studentId,
                ':cname'    => $chineseName, // Maps to 'name' column
                ':ename'    => $englishName, // Maps to 'english_name' column
                ':class'    => $class,
                ':password' => $password
            ]);
            echo "<li>Saved: $studentId | $chineseName ($englishName)</li>";
        } catch (Exception $e) {
            echo "<li>Error $studentId: " . $e->getMessage() . "</li>";
        }
    }
    fclose($handle);
    echo "</ul>Done.";
} else {
    echo "Failed to open CSV.";
}
?>