<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'check_admin.php';
require_once 'php_config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_database'])) {
    ob_start();
    try {
        $host = DB_HOST;
        $user = DB_USER;
        $pass = DB_PASS;
        $name = DB_NAME;
        
        $mysqli = new mysqli($host, $user, $pass, $name);
        if ($mysqli->connect_error) {
            throw new Exception("Connection failed: " . $mysqli->connect_error);
        }
        
        $mysqli->set_charset("utf8mb4");

        $filename = $name . "_" . date("Y-m-d_H-i-s") . ".sql";
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $filename . "\"");
        
        // Add safety wrappers for the import process
        echo "-- Database: `$name`\n";
        echo "-- Generated: " . date("Y-m-d H:i:s") . "\n\n";
        echo "SET FOREIGN_KEY_CHECKS=0;\n";
        echo "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n";
        echo "SET time_zone = \"+00:00\";\n\n";

        $tables = [];
        $result = $mysqli->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            echo "--\n-- Table structure for table `$table`\n--\n";
            echo "DROP TABLE IF EXISTS `$table`;\n";
            $row2 = $mysqli->query("SHOW CREATE TABLE `$table`")->fetch_row();
            echo $row2[1] . ";\n\n";

            echo "--\n-- Dumping data for table `$table`\n--\n";
            $result = $mysqli->query("SELECT * FROM `$table`");
            $num_fields = $result->field_count;

            while ($row = $result->fetch_row()) {
                echo "INSERT INTO `$table` VALUES(";
                for ($j = 0; $j < $num_fields; $j++) {
                    if (isset($row[$j])) {
                        // real_escape_string safely handles quotes, \r, \n, and \x00 automatically
                        $escaped_data = $mysqli->real_escape_string($row[$j]);
                        echo "'" . $escaped_data . "'";
                    } else {
                        echo "NULL";
                    }

                    if ($j < ($num_fields - 1)) {
                        echo ",";
                    }
                }
                echo ");\n";
            }
            echo "\n\n";
        }

        // Re-enable foreign key checks at the end of the file
        echo "SET FOREIGN_KEY_CHECKS=1;\n";

        $mysqli->close();
        ob_end_flush();
        exit;

    } catch (Exception $e) {
        ob_end_clean(); 
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Database Export - GCIOJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { dark: { bg: '#1a1a1a', surface: '#282828' }, brand: { orange: '#ffa116' } } } }
        }
    </script>
</head>
<body class="bg-dark-bg text-gray-200 min-h-screen p-6 flex items-center justify-center">

    <div class="max-w-md w-full bg-dark-surface p-8 rounded-lg shadow-lg border border-gray-700 text-center">
        <h2 class="text-2xl font-bold text-brand-orange mb-2">Database Backup</h2>
        <p class="text-gray-400 text-sm mb-6">Download a complete .sql dump of your current database structure and data.</p>
        
        <?php if ($error): ?> 
            <div class="bg-red-900/50 text-red-300 p-3 rounded mb-6 border border-red-700 text-sm text-left">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div> 
        <?php endif; ?>

        <form method="POST">
            <button type="submit" name="export_database" class="w-full bg-brand-orange hover:bg-orange-600 text-white font-bold py-3 px-4 rounded transition flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                Download .SQL Backup
            </button>
        </form>
        
        <div class="mt-6">
            <a href="index.php" class="text-sm text-gray-500 hover:text-white transition">&larr; Back to Dashboard</a>
        </div>
    </div>

</body>
</html>