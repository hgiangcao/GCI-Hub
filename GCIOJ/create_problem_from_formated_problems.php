<?php
require_once 'db.php';

// Configuration
$csvFile = 'LeetCode Problems - Sheet5.csv';
$formattedProblemsFile = 'formatted_problems.txt';
$basePath = 'problem_set/';

/**
 * Searches CSV for the LeetcodeID at Index 4 using the Code (#) at Index 0
 */
function getLeetcodeIdFromCsv($codeToFind, $csvPath) {
    if (!file_exists($csvPath)) return null;
    $handle = fopen($csvPath, "r");
    fgetcsv($handle); // Skip header
    while (($data = fgetcsv($handle)) !== FALSE) {
        if (trim($data[0]) == trim($codeToFind)) {
            $val = trim($data[4]);
            fclose($handle);
            return $val;
        }
    }
    fclose($handle);
    return null;
}

try {
    $pdo = DB::connect();
    if (!file_exists($formattedProblemsFile)) die("Formatted file not found.");

    $sections = explode('*****', file_get_contents($formattedProblemsFile));
    echo "<h2>Processing Problems</h2><ul>";

    foreach ($sections as $section) {
        $section = trim($section);
        if (empty($section)) continue;

        $lines = explode("\n", $section);
        $headerLine = array_shift($lines);
        
        // Extract code and parameter: "1, solve(s)"
        if (!preg_match('/^(\d+),\s*solve\((.*?)\)/', $headerLine, $matches)) continue;

        $codeInFile = $matches[1];
        $parameter  = $matches[2];
        $htmlDesc   = implode("\n", $lines);

        // 1. Get LeetcodeID from CSV
        $leetcodeId = getLeetcodeIdFromCsv($codeInFile, $csvFile);
        if (!$leetcodeId) {
            echo "<li>Code $codeInFile: Not found in CSV.</li>";
            continue;
        }

        // 2. Find internal ID from DB using LeetcodeID
        $stmt = $pdo->prepare("SELECT id FROM problem WHERE Leetcode_ID = ? LIMIT 1");
        $stmt->execute([$leetcodeId]);
        $problem = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$problem) {
            echo "<li>LeetcodeID $leetcodeId: Not found in database.</li>";
            continue;
        }

        $id = $problem['id'];
        $folderPath = $basePath . $id;

        // 3. Create folder and files
        if (!is_dir($folderPath)) mkdir($folderPath, 0777, true);

        // Save HTML
        file_put_contents("$folderPath/$id.html", trim($htmlDesc));

        // Save Python Template
        $pyTemplate = "def solve($parameter):\n    \n\n    return \n";
        file_put_contents("$folderPath/$id.py", $pyTemplate);

        echo "<li><b>Success:</b> Created folder <b>$id</b> (LeetCode $leetcodeId)</li>";
    }
    echo "</ul><p>Processing complete.</p>";

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>