<?php
// submission_management.php (Simple Debug View with Striped Rows)
session_start();
require_once 'auth.php'; 
require_once 'check_admin.php';
require_once 'db.php';

// Fetch detailed submission data with joins (Rule: Step 246 & 269)
$sql = "SELECT 
            s.id,
            stu.student_id as student_identifier,
            stu.name as student_name,
            c.name as contest_name,
            c.course as course_code,
            p.title as problem_name,
            s.status,
            s.count_cheat,
            s.created_at
        FROM submission s
        LEFT JOIN student stu ON s.student_id = stu.id
        LEFT JOIN contest c ON s.contest_id = c.id
        LEFT JOIN problem p ON s.problem_id = p.id
        ORDER BY s.id DESC 
        LIMIT 1000";

$data = DB::connect()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Debug | Submissions Logs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 p-6 font-mono text-md">

    <div class=" max-w-7xl  mx-auto bg-white border border-gray-300 shadow-sm">
        
        <div class="p-4 border-b border-gray-300 flex justify-between items-center bg-gray-50">
            <div>
                <h1 class="text-base font-bold text-gray-800 uppercase tracking-tighter">[GCIOJ] SUBMISSION_LOGS_DEBUG_VIEW</h1>
                <p class="text-[10px] text-gray-500 mt-1">Status: Monitoring | Limit: 1000 Records | Time: <?= date("Y-m-d H:i:s") ?></p>
            </div>
            <div class="space-x-4">
                <a href="dashboard.php" class="underline text-blue-600 hover:text-blue-800">Dashboard</a>
                <a href="logout.php" class="underline text-red-600 hover:text-red-800">Logout</a>
            </div>
        </div>

        <table class="w-full text-left border-collapse table-auto">
            <thead>
                <tr class="bg-gray-200/80 text-gray-600 font-bold border-b border-gray-300">
                    <th class="px-4 py-2 border-r border-gray-300">ID</th>
                    <th class="px-4 py-2 border-r border-gray-300">STUDENT_ID</th>
                    <th class="px-4 py-2 border-r border-gray-300">CONTEST</th>
                    <th class="px-4 py-2 border-r border-gray-300">PROBLEM</th>
                    <th class="px-4 py-2 border-r border-gray-300">STATUS</th>
                    <th class="px-4 py-2 border-r border-gray-300">VIOLATE</th>
                    <th class="px-4 py-2">TIMESTAMP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($data)): ?>
                    <tr><td colspan="8" class="p-10 text-center text-gray-400">--- NORECORDS_FOUND ---</td></tr>
                <?php else: ?>
                    <?php foreach ($data as $row): ?>
                        <tr class="even:bg-gray-50 hover:bg-yellow-50 transition-colors">
                            <td class="px-4 py-1.5 border-r border-gray-200 text-gray-400"><?= $row['id'] ?></td>
                            <td class="px-4 py-1.5 border-r border-gray-200 font-bold text-gray-800"><?= htmlspecialchars($row['student_identifier']) ?></td>
                            <td class="px-4 py-1.5 border-r border-gray-200">
                                <span class="font-bold text-gray-700"><?= htmlspecialchars($row['contest_name']) ?></span>
                                <span class="text-gray-400  ml-1">[<?= htmlspecialchars($row['course_code']) ?>]</span>
                            </td>
                            <td class="px-4 py-1.5 border-r border-gray-200 text-gray-600 truncate max-w-[150px]"><?= htmlspecialchars($row['problem_name']) ?></td>
                            <td class="px-4 py-1.5 border-r border-gray-200">
                                <?php 
                                $statusClass = 'text-black-500';
                                if ($row['status'] === 'Accepted') $statusClass = 'text-green-600 font-bold';
                                else if (strpos($row['status'], 'Wrong') !== false) $statusClass = 'text-red-500 font-bold';
                                ?>
                                <span class="<?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></span>
                            </td>
                            <td class="px-4 py-1.5 border-r border-gray-200 text-center">
                                <?php if ($row['count_cheat'] > 0): ?>
                                    <span class="text-center text-orange-600 font-black  items-center gap-1 text-center">
                                        <i class="fas fa-exclamation-triangle"></i> <?= (int)$row['count_cheat'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-300">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-1.5 text-gray-500"><?= $row['created_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="p-4 bg-gray-50 border-t border-gray-300 text-[10px] text-gray-400 text-center">
            END OF TRANSMISSION // [GCIOJ_LOG_SERVICE]
        </div>

    </div>

</body>
</html>