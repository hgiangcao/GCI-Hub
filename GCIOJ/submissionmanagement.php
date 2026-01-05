<?php
// raw_submissions.php
session_start();
require_once 'auth.php'; // Optional: Remove if you want it public
require_once 'db.php';

// Fetch RAW data directly using PDO (bypassing Model logic to see everything)
$sql = "SELECT * FROM submission ORDER BY id DESC";
$data = DB::connect()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Raw Submission Data</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f0f0f0; }
        h1 { margin-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 12px; vertical-align: top; }
        th { background: #333; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        pre { margin: 0; white-space: pre-wrap; word-wrap: break-word; max-height: 100px; overflow-y: auto; }
    </style>
</head>
<body>

    <h1>Raw Submission Data (Debug View)</h1>
    <p>Total Records: <strong><?= count($data) ?></strong></p>

    <table>
        <thead>
            <tr>
                <?php if (count($data) > 0): ?>
                    <?php foreach (array_keys($data[0]) as $key): ?>
                        <th><?= htmlspecialchars($key) ?></th>
                    <?php endforeach; ?>
                <?php else: ?>
                    <th>No Data</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (count($data) > 0): ?>
                <?php foreach ($data as $row): ?>
                <tr>
                    <?php foreach ($row as $key => $value): ?>
                        <td>
                            <?php
                            // If the data is code or long text, wrap it in <pre>
                            if ($key === 'source_code' || $key === 'output_log') {
                                echo "<pre>" . htmlspecialchars(substr($value, 0, 200)) . (strlen($value) > 200 ? "..." : "") . "</pre>";
                            } else {
                                echo htmlspecialchars($value);
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="100%">Table is empty.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>