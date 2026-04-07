<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Tool Discovery Hub</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1E40AF',
                        secondary: '#3B82F6',
                        cta: '#22C55E',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-2">
                    <i data-lucide="bot" class="w-8 h-8 text-primary"></i>
                    <span class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">AI Hub</span>
                </div>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="index.php" class="text-slate-600 hover:text-primary font-medium transition-colors">首頁</a>
                    <a href="tools.php" class="text-slate-600 hover:text-primary font-medium transition-colors">所有工具</a>
                    <a href="compare.php" class="text-slate-600 hover:text-primary font-medium transition-colors">功能比較</a>
                    <a href="collections.php" class="text-slate-600 hover:text-primary font-medium transition-colors">我的收藏</a>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-4">
                    <button class="hidden sm:flex items-center gap-2 text-slate-600 hover:text-primary font-medium">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        登入
                    </button>
                    <button class="bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-lg font-semibold transition-all">
                        開始發現
                    </button>
                    <!-- Mobile Menu Icon -->
                    <button class="md:hidden p-2 text-slate-600">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    <main>