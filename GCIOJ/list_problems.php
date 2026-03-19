<?php
session_start();
require_once 'auth.php';
require_once 'check_admin.php';
require_once 'problem.php';

// 1. Fetch All
$allProblems = Problem::getAll();

// 2. Group by 'tag' (Acting as Exam Name)
$groupedProblems = [];
foreach ($allProblems as $p) {
    $examName = !empty($p['tag']) ? $p['tag'] : 'Uncategorized';
    $groupedProblems[$examName][] = $p;
}
ksort($groupedProblems); // Sort exams alphabetically
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Problem List - GCIOJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { bg: '#121212', surface: '#1e1e1e', border: '#333333', text: '#e0e0e0', muted: '#888888' },
                        brand: { orange: '#ffa116', green: '#2cbb5d' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen p-6">

    <div class="max-w-[1400px] mx-auto">

        <div class="flex justify-between items-center mb-8 pb-4 border-b border-dark-border">
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <span class="text-brand-orange">Problem Sets</span>
                <span class="text-xs bg-dark-surface border border-dark-border text-dark-muted px-2 py-1 rounded">
                    <?= count($allProblems) ?> Problems in <?= count($groupedProblems) ?> Groups
                </span>
            </h1>
            <div class="flex gap-4">
                <a href="create_problem.php" class="bg-brand-orange hover:bg-orange-600 text-white px-4 py-2 rounded text-sm font-bold transition">+ Create New</a>
                <a href="dashboard.php" class="text-sm text-dark-muted hover:text-white transition self-center">Back to Dashboard</a>
            </div>
        </div>

        <?php if (empty($groupedProblems)): ?>
            <div class="p-8 text-center text-dark-muted bg-dark-surface rounded border border-dark-border">No problems found.</div>
        <?php endif; ?>

        <?php foreach ($groupedProblems as $examName => $problems): ?>

            <div class="mb-8">
                <h2 class="text-xl font-bold text-white mb-3 flex items-center gap-2">
                    <span class="text-brand-green/80">#</span>
                    <?= htmlspecialchars($examName) ?>
                    <span class="text-xs font-normal text-dark-muted ml-2">(<?= count($problems) ?>)</span>
                </h2>

                <div class="bg-dark-surface rounded border border-dark-border overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#141414] text-dark-muted text-xs uppercase tracking-wider border-b border-dark-border">
                                    <th class="p-4 w-24 font-medium">Code</th>
                                    <th class="p-4 font-medium">Title</th>
                                    <th class="p-4 w-24 font-medium">Level</th>
                                    <th class="p-4 w-32 font-medium">Limits</th>
                                    <th class="p-4 font-medium">Config</th>
                                    <th class="p-4 w-24 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-dark-border text-sm">
                                <?php foreach ($problems as $p): ?>
                                <tr class="hover:bg-dark-border/30 transition group">

                                    <td class="p-4 font-mono text-brand-orange font-bold">
                                        <?= $p['code'] ?>
                                    </td>

                                    <td class="p-4 text-white font-medium">
                                        <?= htmlspecialchars($p['title']) ?>
                                    </td>

                                    <td class="p-4">
                                        <?php
                                            $color = 'text-gray-400';
                                            if ($p['level'] === 'Easy') $color = 'text-green-400';
                                            if ($p['level'] === 'Medium') $color = 'text-yellow-400';
                                            if ($p['level'] === 'Hard') $color = 'text-red-400';
                                        ?>
                                        <span class="<?= $color ?>"><?= $p['level'] ?></span>
                                    </td>

                                    <td class="p-4 text-dark-muted font-mono text-xs">
                                        <?= $p['time_limit_ms'] ?>ms / <?= $p['memory_limit_mb'] ?>MB
                                    </td>

                                    <td class="p-4 text-xs text-dark-muted">
                                        <span class="bg-dark-bg border border-dark-border px-1 rounded"><?= $p['grading_type'] ?></span>
                                        <span class="bg-dark-bg border border-dark-border px-1 rounded ml-1"><?= $p['input_type'] ?></span>
                                    </td>

                                    <td class="p-4 text-right">
                                        <a href="create_problem.php?id=<?= $p['id'] ?>"
                                           class="text-brand-orange hover:text-white transition text-xs font-bold px-2 py-1 rounded bg-brand-orange/10 hover:bg-brand-orange/20">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>

    </div>
</body>
</html>