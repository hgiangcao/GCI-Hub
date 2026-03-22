<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">

<style>
@keyframes border-flash {
    0%, 100% { border-color: rgba(255, 255, 255, 0.1); box-shadow: 0 0 0px rgba(255, 161, 22, 0); }
    50% { border-color: #ffa116; box-shadow: 0 0 10px rgba(255, 161, 22, 0.5); }
}
.animate-border-flash {
    animation: border-flash 2s infinite ease-in-out;
}
</style>

<nav class="nav-font sticky top-0 z-[9999] w-full border-b border-white/10 bg-[#0f172a]/90 backdrop-blur-xl">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-14 items-center justify-between"> 
            
            <div class="flex items-center gap-6">
                <a href="index.php" class="group flex items-center transition-transform active:scale-95">
                    <span class="text-xl font-black tracking-tighter text-[#ffa116] group-hover:drop-shadow-[0_0_12px_rgba(255,161,22,0.6)]">
                        &lt;/&gt; GCIOJ
                    </span>
                </a>

                <div class="hidden items-center space-x-2 md:flex">
                    <a href="index.php" 
                       class="px-4 py-1.5 text-sm font-black transition-all rounded-lg border-2 <?= $currentPage == 'index.php' ? 'bg-[#ffa116] border-[#ffa116] text-slate-900 shadow-md' : 'border-transparent text-slate-400 hover:text-[#ffa116] hover:bg-[#ffa116]/10' ?>">
                        CONTEST
                    </a>
                    
                    <a href="practice.php" 
                       class="flex items-center gap-2 px-4 py-1.5 text-sm font-black transition-all rounded-lg border-2 <?= $currentPage == 'practice.php' ? 'bg-[#00d2ff] border-[#00d2ff] text-slate-900 shadow-md' : 'border-transparent text-slate-400 hover:text-[#00d2ff] hover:bg-[#00d2ff]/10' ?>">
                        <i class="fas fa-rocket text-xs"></i>
                        PRACTICE
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <?php if(isset($_SESSION['student_id'])): ?>
                    <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 py-1 pl-1 pr-3 animate-border-flash hover:scale-105 transition-transform">
                        <a href="profile.php" class="h-8 w-8 overflow-hidden rounded-lg border border-slate-700 transition-all hover:border-[#ffa116]">
                            <?php if(!empty($_SESSION['avatar_img']) && file_exists("avt_img/" . $_SESSION['avatar_img'])): ?>
                                <img src="avt_img/<?= htmlspecialchars($_SESSION['avatar_img']) ?>" class="h-full w-full object-cover">
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#ffa116] to-[#ea580c] text-[10px] font-black text-white">
                                    <?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </a>
                        <a href="profile.php"> 
                            <span class="text-xs font-black text-white"><?= htmlspecialchars($_SESSION['student_id']) ?></span>
                        </a>
                    </div>
                    
                    <a href="logout.php" class="text-[11px] font-black uppercase tracking-tighter text-slate-500 hover:text-red-500">
                        Logout
                    </a>

                <?php else: ?>
                    <a href="login.php" class="text-sm font-black text-slate-400 hover:text-white mr-2">Sign In</a>
                    
                    <a href="setup_password.php" class="group relative overflow-hidden rounded-lg bg-gradient-to-r from-[#facc15] to-[#f97316] px-5 py-1.5 text-sm font-black text-slate-900 shadow-md transition-all hover:scale-105 active:scale-95">
                        <span class="relative z-10 flex items-center gap-2">
                            <i class="fas fa-shield-halved text-xs"></i>
                            Setup Password
                        </span>
                        <div class="animate-shimmer absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/50 to-transparent"></div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>