<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GCIOJ - Online Judge</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: '#1a1a1a',      // Main background
                            surface: '#282828', // Cards/Nav
                            hover: '#3e3e3e',   // Hover states
                            text: '#eff1f6',    // Primary text
                            muted: '#9ca3af',   // Secondary text
                        },
                        brand: {
                            orange: '#ffa116',  // LeetCode-ish accent
                            green: '#2cbb5d',   // Easy/Success
                            yellow: '#ffc01e',  // Medium
                            red: '#ef4743'      // Hard/Error
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['Menlo', 'Monaco', 'Courier New', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom Scrollbar for a cleaner look */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1a1a1a; }
        ::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        
        .hidden-page { display: none; }
    </style>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen flex flex-col antialiased">

    <nav class="bg-dark-surface border-b border-gray-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <div class="flex items-center gap-8">
                    <div class="flex-shrink-0 cursor-pointer" onclick="showPage('problems')">
                        <span class="text-brand-orange font-bold text-xl tracking-tight">&lt;/&gt; GCIOJ</span>
                    </div>
                    <div class="hidden md:block">
                        <div class="flex items-baseline space-x-4">
                            <button onclick="showPage('problems')" class="text-dark-text hover:bg-dark-hover px-3 py-2 rounded-md text-sm font-medium transition">Problems</button>
                            <button onclick="showPage('ranking')" class="text-dark-muted hover:text-dark-text hover:bg-dark-hover px-3 py-2 rounded-md text-sm font-medium transition">Contest & Ranking</button>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button onclick="showPage('stats')" class="hidden md:flex items-center gap-2 hover:bg-dark-hover px-3 py-2 rounded-md transition">
                        <div class="h-6 w-6 rounded-full bg-brand-orange flex items-center justify-center text-xs text-white font-bold">U</div>
                    </button>
                    <button onclick="showPage('login')" class="text-dark-muted hover:text-white text-sm">Sign In</button>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

        <section id="login" class="hidden-page max-w-md mx-auto mt-10">
            <div class="bg-dark-surface p-8 rounded-lg shadow-lg border border-gray-700">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-dark-text">Welcome Back</h2>
                    <p class="text-dark-muted text-sm mt-2">Sign in to continue your coding journey</p>
                </div>
                <form class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-dark-muted mb-2">Email or Username</label>
                        <input type="text" class="w-full bg-dark-bg border border-gray-600 rounded-md px-4 py-2 text-dark-text focus:outline-none focus:border-brand-orange focus:ring-1 focus:ring-brand-orange transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-muted mb-2">Password</label>
                        <input type="password" class="w-full bg-dark-bg border border-gray-600 rounded-md px-4 py-2 text-dark-text focus:outline-none focus:border-brand-orange focus:ring-1 focus:ring-brand-orange transition">
                    </div>
                    <button type="button" onclick="showPage('problems')" class="w-full bg-brand-orange hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                        Sign In
                    </button>
                    <div class="flex justify-between text-xs text-dark-muted mt-4">
                        <a href="#" class="hover:text-brand-orange">Forgot Password?</a>
                        <a href="#" class="hover:text-brand-orange">Sign Up</a>
                    </div>
                </form>
            </div>
        </section>

        <section id="problems" class="">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">All Problems</h2>
                <div class="flex gap-3">
                    <input type="text" placeholder="Search questions..." class="bg-dark-surface border border-gray-700 rounded-md px-4 py-2 text-sm text-dark-text focus:border-brand-orange outline-none">
                    <button class="bg-dark-surface hover:bg-dark-hover border border-gray-700 text-dark-text px-4 py-2 rounded-md text-sm transition">Pick One</button>
                </div>
            </div>

            <div class="bg-dark-surface rounded-lg border border-gray-700 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-800 text-dark-muted text-xs uppercase border-b border-gray-700">
                            <th class="px-6 py-4 font-medium w-16">Status</th>
                            <th class="px-6 py-4 font-medium">Title</th>
                            <th class="px-6 py-4 font-medium w-32">Difficulty</th>
                            <th class="px-6 py-4 font-medium w-32">Acceptance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700 text-sm">
                        <tr class="hover:bg-dark-hover transition group cursor-pointer">
                            <td class="px-6 py-4 text-brand-green">✔</td>
                            <td class="px-6 py-4 font-medium text-dark-text group-hover:text-brand-orange transition">1. Two Sum</td>
                            <td class="px-6 py-4 text-brand-green">Easy</td>
                            <td class="px-6 py-4 text-dark-muted">48.2%</td>
                        </tr>
                        <tr class="hover:bg-dark-hover transition group cursor-pointer">
                            <td class="px-6 py-4 text-dark-muted">-</td>
                            <td class="px-6 py-4 font-medium text-dark-text group-hover:text-brand-orange transition">2. Add Two Numbers</td>
                            <td class="px-6 py-4 text-brand-yellow">Medium</td>
                            <td class="px-6 py-4 text-dark-muted">39.1%</td>
                        </tr>
                        <tr class="hover:bg-dark-hover transition group cursor-pointer">
                            <td class="px-6 py-4 text-brand-green">✔</td>
                            <td class="px-6 py-4 font-medium text-dark-text group-hover:text-brand-orange transition">3. Longest Substring Without Repeating Characters</td>
                            <td class="px-6 py-4 text-brand-yellow">Medium</td>
                            <td class="px-6 py-4 text-dark-muted">32.8%</td>
                        </tr>
                        <tr class="hover:bg-dark-hover transition group cursor-pointer">
                            <td class="px-6 py-4 text-dark-muted">-</td>
                            <td class="px-6 py-4 font-medium text-dark-text group-hover:text-brand-orange transition">4. Median of Two Sorted Arrays</td>
                            <td class="px-6 py-4 text-brand-red">Hard</td>
                            <td class="px-6 py-4 text-dark-muted">28.5%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-center mt-6 gap-2">
                <button class="px-3 py-1 bg-dark-surface border border-gray-700 rounded hover:bg-dark-hover">&lt;</button>
                <button class="px-3 py-1 bg-brand-orange text-white border border-brand-orange rounded">1</button>
                <button class="px-3 py-1 bg-dark-surface border border-gray-700 rounded hover:bg-dark-hover">2</button>
                <button class="px-3 py-1 bg-dark-surface border border-gray-700 rounded hover:bg-dark-hover">3</button>
                <button class="px-3 py-1 bg-dark-surface border border-gray-700 rounded hover:bg-dark-hover">&gt;</button>
            </div>
        </section>

        <section id="ranking" class="hidden-page">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-brand-orange">Global Ranking</h2>
                <p class="text-dark-muted mt-2">See where you stand against the world's best developers.</p>
            </div>

            <div class="bg-dark-surface rounded-lg border border-gray-700 overflow-hidden max-w-4xl mx-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-800 text-xs uppercase text-dark-muted border-b border-gray-700">
                        <tr>
                            <th class="px-6 py-4 w-24">Rank</th>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4 text-right">Solved</th>
                            <th class="px-6 py-4 text-right">Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr class="hover:bg-dark-hover transition">
                            <td class="px-6 py-4 font-mono text-brand-yellow">#1</td>
                            <td class="px-6 py-4 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-600"></div>
                                <span class="font-medium text-white">tourist</span>
                            </td>
                            <td class="px-6 py-4 text-right text-dark-muted">3,402</td>
                            <td class="px-6 py-4 text-right font-bold text-brand-orange">3290</td>
                        </tr>
                        <tr class="hover:bg-dark-hover transition">
                            <td class="px-6 py-4 font-mono text-gray-400">#2</td>
                            <td class="px-6 py-4 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-600"></div>
                                <span class="font-medium text-white">Benq</span>
                            </td>
                            <td class="px-6 py-4 text-right text-dark-muted">2,900</td>
                            <td class="px-6 py-4 text-right font-bold text-white">3150</td>
                        </tr>
                         <tr class="hover:bg-dark-hover transition">
                            <td class="px-6 py-4 font-mono text-brand-orange">#3</td>
                            <td class="px-6 py-4 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-600"></div>
                                <span class="font-medium text-white">Neal_Wu</span>
                            </td>
                            <td class="px-6 py-4 text-right text-dark-muted">2,120</td>
                            <td class="px-6 py-4 text-right font-bold text-white">3105</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="stats" class="hidden-page">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="bg-dark-surface p-6 rounded-lg border border-gray-700 h-fit">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-20 h-20 rounded-lg bg-gray-600 flex items-center justify-center text-3xl font-bold text-gray-400">U</div>
                        <div>
                            <h2 class="text-xl font-bold">User_123</h2>
                            <p class="text-dark-muted text-sm">Rank 140,230</p>
                        </div>
                    </div>
                    <button class="w-full py-2 mb-4 bg-green-600/20 text-green-500 border border-green-600/50 rounded hover:bg-green-600/30 transition text-sm">Edit Profile</button>
                    
                    <div class="border-t border-gray-700 pt-4 space-y-3 text-sm text-dark-muted">
                        <div class="flex items-center gap-2">
                            <span>📍</span> Taiwan
                        </div>
                        <div class="flex items-center gap-2">
                            <span>🎓</span> University of Technology
                        </div>
                        <div class="flex items-center gap-2">
                            <span>🔗</span> github.com/user123
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 space-y-6">
                    
                    <div class="bg-dark-surface p-6 rounded-lg border border-gray-700">
                        <h3 class="font-bold text-dark-muted uppercase text-xs mb-4">Solved Problems</h3>
                        <div class="flex flex-col sm:flex-row items-center gap-10">
                            <div class="relative w-32 h-32 rounded-full border-8 border-gray-700 flex items-center justify-center">
                                <div class="text-center">
                                    <span class="text-2xl font-bold text-white">450</span>
                                    <div class="text-xs text-dark-muted">Solved</div>
                                </div>
                                <div class="absolute inset-0 border-8 border-brand-orange rounded-full" style="clip-path: polygon(0 0, 100% 0, 100% 50%, 0 50%); transform: rotate(45deg);"></div>
                            </div>

                            <div class="flex-1 w-full space-y-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-brand-green w-16">Easy</span>
                                    <div class="flex-1 mx-3 h-2 bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-brand-green w-[70%]"></div>
                                    </div>
                                    <span class="text-dark-muted">120/400</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-brand-yellow w-16">Medium</span>
                                    <div class="flex-1 mx-3 h-2 bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-brand-yellow w-[40%]"></div>
                                    </div>
                                    <span class="text-dark-muted">280/800</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-brand-red w-16">Hard</span>
                                    <div class="flex-1 mx-3 h-2 bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-brand-red w-[10%]"></div>
                                    </div>
                                    <span class="text-dark-muted">50/200</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-dark-surface p-6 rounded-lg border border-gray-700">
                        <h3 class="font-bold text-dark-muted uppercase text-xs mb-4">Recent Submissions</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-sm border-b border-gray-700 pb-2">
                                <span class="font-medium">Two Sum</span>
                                <span class="text-dark-muted">1 hour ago</span>
                                <span class="text-brand-green font-bold">Accepted</span>
                            </div>
                            <div class="flex justify-between items-center text-sm border-b border-gray-700 pb-2">
                                <span class="font-medium">Median of Two Arrays</span>
                                <span class="text-dark-muted">5 hours ago</span>
                                <span class="text-brand-red font-bold">Wrong Answer</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="font-medium">Add Two Numbers</span>
                                <span class="text-dark-muted">1 day ago</span>
                                <span class="text-brand-green font-bold">Accepted</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>

    <footer class="bg-dark-surface border-t border-gray-700 py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-dark-muted text-sm">
            <p>&copy; 2024 GCIOJ. All rights reserved.</p>
            <div class="mt-2 space-x-4">
                <a href="#" class="hover:text-brand-orange">Privacy Policy</a>
                <a href="#" class="hover:text-brand-orange">Terms of Service</a>
            </div>
        </div>
    </footer>

    <script>
        function showPage(pageId) {
            // Hide all sections
            document.querySelectorAll('main > section').forEach(el => {
                el.classList.add('hidden-page');
            });
            // Show target section
            document.getElementById(pageId).classList.remove('hidden-page');
            
            // Optional: Scroll to top
            window.scrollTo(0, 0);
        }

        // Initialize: Show Problems page by default
        showPage('problems');
    </script>
</body>
</html>
