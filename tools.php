<?php include 'layout/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Breadcrumb & Title -->
    <div class="mb-10">
        <nav class="flex text-slate-400 text-sm mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="index.php" class="hover:text-primary">首頁</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        <span class="ml-1 md:ml-2">所有工具</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold text-slate-900">探索所有 AI 工具</h1>
    </div>

    <div class="flex flex-col lg:flex-row gap-12">
        <!-- Sidebar Filters -->
        <aside class="w-full lg:w-72 flex-shrink-0">
            <div class="sticky top-24 space-y-8">
                <!-- Search within results -->
                <div>
                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i data-lucide="search" class="w-5 h-5 text-primary"></i> 關鍵字搜尋
                    </h3>
                    <input type="text" placeholder="例如：寫作助理..." class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-primary transition-all">
                </div>

                <!-- Category Filter -->
                <div>
                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i data-lucide="grid" class="w-5 h-5 text-primary"></i> 工具分類
                    </h3>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary">
                            <span class="text-slate-600 group-hover:text-primary transition-colors">文本創作</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary">
                            <span class="text-slate-600 group-hover:text-primary transition-colors">圖像生成</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary">
                            <span class="text-slate-600 group-hover:text-primary transition-colors">影片剪輯</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary">
                            <span class="text-slate-600 group-hover:text-primary transition-colors">生產力 / 筆記</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary">
                            <span class="text-slate-600 group-hover:text-primary transition-colors">程式開發助理</span>
                        </label>
                    </div>
                </div>

                <!-- Price Type Filter -->
                <div>
                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i data-lucide="dollar-sign" class="w-5 h-5 text-primary"></i> 價格方案
                    </h3>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="price" class="w-5 h-5 border-slate-300 text-primary focus:ring-primary">
                            <span class="text-slate-600 group-hover:text-primary transition-colors">完全免費</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="price" class="w-5 h-5 border-slate-300 text-primary focus:ring-primary">
                            <span class="text-slate-600 group-hover:text-primary transition-colors">免費試用 (Freemium)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="price" class="w-5 h-5 border-slate-300 text-primary focus:ring-primary">
                            <span class="text-slate-600 group-hover:text-primary transition-colors">付費訂閱</span>
                        </label>
                    </div>
                </div>

                <!-- Minimum Rating Filter -->
                <div>
                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i data-lucide="layers" class="w-5 h-5 text-primary"></i> 最低評分
                    </h3>
                    <select class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-primary transition-all bg-white">
                        <option>所有評分</option>
                        <option>4.5 以上</option>
                        <option>4.0 以上</option>
                        <option>3.5 以上</option>
                    </select>
                </div>

                <button class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-all">
                    套用篩選
                </button>
            </div>
        </aside>

        <!-- Tool Grid & Sorting -->
        <div class="flex-1">
            <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
                <span class="text-slate-500 font-medium">找到 128 個符合条件的工具</span>
                <div class="flex items-center gap-3">
                    <span class="text-slate-500 text-sm whitespace-nowrap">排序方式：</span>
                    <select class="px-4 py-2 rounded-lg border border-slate-200 outline-none focus:border-primary bg-white font-medium">
                        <option>熱門程度</option>
                        <option>最新發佈</option>
                        <option>評分最高</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Tool Card Loop -->
                <?php for ($i = 1; $i <= 9; $i++): ?>
                    <div class="glass-card group p-5 flex flex-col h-full">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden">
                                <img src="https://api.dicebear.com/7.x/identicon/svg?seed=Tool<?php echo $i; ?>" alt="Tool Logo" class="w-10 h-10">
                            </div>
                            <div class="flex gap-1 text-orange-400 items-center bg-orange-50 px-2 py-1 rounded-lg text-sm font-bold">
                                <i data-lucide="star" class="w-4 h-4 fill-current"></i> 4.<?php echo rand(5, 9); ?>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 group-hover:text-primary transition-colors mb-2">AI Tool Name <?php echo $i; ?></h3>
                        <p class="text-slate-500 text-sm mb-6 flex-grow">
                            這是一個示範工具描述，說明該 AI 如何幫助使用者自動化工作流程並提升創意產出。
                        </p>
                        <div class="flex flex-wrap gap-2 mb-6 text-xs">
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md font-semibold">分類項目</span>
                            <span class="px-3 py-1 bg-green-50 text-green-600 rounded-md font-semibold">免費版</span>
                        </div>
                        <div class="flex gap-2">
                            <a href="tool-detail.php?id=<?php echo $i; ?>" class="flex-1 py-3 text-center bg-slate-100 hover:bg-primary hover:text-white text-slate-700 font-bold rounded-xl transition-all">
                                查看詳情
                            </a>
                            <button class="w-12 h-12 flex items-center justify-center bg-slate-100 hover:bg-red-50 hover:text-red-500 text-slate-500 rounded-xl transition-all">
                                <i data-lucide="heart" class="w-6 h-6"></i>
                            </button>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Pagination -->
            <div class="mt-16 flex justify-center gap-2">
                <button class="w-12 h-12 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:border-primary hover:text-primary transition-all">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
                <button class="w-12 h-12 rounded-xl bg-primary text-white flex items-center justify-center font-bold">1</button>
                <button class="w-12 h-12 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:border-primary hover:text-primary transition-all">2</button>
                <button class="w-12 h-12 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:border-primary hover:text-primary transition-all">3</button>
                <button class="w-12 h-12 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:border-primary hover:text-primary transition-all">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>