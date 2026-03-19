<nav class="bg-dark-surface border-b border-gray-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-8">
                    <a href="index.php" class="flex-shrink-0 cursor-pointer">
                        <span class="text-brand-orange font-bold text-2xl tracking-tight">&lt;/&gt; GCIOJ</span>
                    </a>
                    <div class="hidden md:block">
                        <div class="flex items-baseline space-x-4"> 
                            <a href="index.php" class="bg-dark-hover text-white px-3 py-2 rounded-md text-sm font-medium transition">Contest</a>
                            <!-- <a href="ranking.php" class="text-dark-text hover:bg-dark-hover px-3 py-2 rounded-md text-sm font-medium transition">Ranking</a>-->
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <?php if(isset($_SESSION['student_id'])): ?>
                        <p class="hidden md:flex items-center gap-2 hover:bg-dark-hover px-3 py-2 rounded-md transition">
                        <a href="profile.php?id=<?= htmlspecialchars($_SESSION['student_id']) ?>" class="h-6 w-6 rounded-full bg-brand-orange flex items-center justify-center text-gray font-bold font-xl"><?= htmlspecialchars(mb_substr($_SESSION['name'], 0, 1, "UTF-8")) ?></a>
                        </p>
                        <span class="text-sm text-dark-muted">Hi, <?= htmlspecialchars($_SESSION['student_id']) ?></span>

                        <a href="logout.php" class="text-brand-orange hover:text-white text-sm">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="text-dark-muted hover:text-white text-sm">Sign In</a>
                        <a href="setup_password.php" class=" rounded bg-yellow-300 fw-bold text-red-500 hover:text-white text-md">Setup Password</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>