<?php include 'layout/header.php'; ?>

<!-- Hero Section -->
<section class="relative py-24 overflow-hidden bg-slate-900 text-white">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&q=80&w=2000')] bg-cover bg-center opacity-20"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-slate-900/60 via-slate-900 to-slate-900"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6 tracking-tight">
            發現最適合您的 <span class="text-secondary">AI 工具</span>
        </h1>
        <p class="text-lg md:text-xl text-slate-300 mb-10 max-w-2xl mx-auto">
            幫助學生、上班族與創作者在數千款 AI 應用中，找到提升效率的終極武器。
        </p>

        <!-- Search Bar -->
        <div class="max-w-2xl mx-auto relative group">
            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                <i data-lucide="search" class="w-6 h-6"></i>
            </div>
            <input type="text" placeholder="尋找：寫作助理、圖像生成、影片剪輯..."
                class="w-full pl-14 pr-32 py-5 bg-white text-slate-900 rounded-2xl shadow-2xl outline-none focus:ring-4 focus:ring-primary/20 transition-all text-lg">
            <button class="absolute inset-y-2 right-2 px-6 bg-primary hover:bg-primary/90 text-white rounded-xl font-bold transition-all flex items-center gap-2">
                搜尋
            </button>
        </div>

        <!-- Trending Tags -->
        <div class="mt-6 flex flex-wrap justify-center gap-3 text-sm text-slate-400">
            <span>熱門關鍵字：</span>
            <a href="#" class="hover:text-white transition-colors">#ChatGPT</a>
            <a href="#" class="hover:text-white transition-colors">#Midjourney</a>
            <a href="#" class="hover:text-white transition-colors">#效率提升</a>
            <a href="#" class="hover:text-white transition-colors">#影片自動生成</a>
        </div>
    </div>
</section>

<!-- Stats / Features Quick Look -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 relative z-10">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-xl flex items-center gap-4 border border-slate-100">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-primary">
                <i data-lucide="zap" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-lg">快速發現</h3>
                <p class="text-slate-500 text-sm">每日更新最前衛的 AI 應用。</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl flex items-center gap-4 border border-slate-100">
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-cta">
                <i data-lucide="arrow-left-right" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-lg">深度比較</h3>
                <p class="text-slate-500 text-sm">直觀的功能與價格對比。</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl flex items-center gap-4 border border-slate-100">
            <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500">
                <i data-lucide="bookmark" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-lg">個人收藏</h3>
                <p class="text-slate-500 text-sm">打造您的專屬 AI 工具箱。</p>
            </div>
        </div>
    </div>
</section>

<!-- Popular AI Tools -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="flex justify-between items-end mb-12">
        <div>
            <h2 class="text-3xl font-bold text-slate-900 mb-2">本週熱門工具</h2>
            <p class="text-slate-500">當前社區評分最高與使用最廣泛的工具。</p>
        </div>
        <a href="tools.php" class="text-primary font-semibold flex items-center gap-1 hover:underline">
            查看更多 <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Tool Card 1 -->
        <div class="glass-card group p-5 flex flex-col h-full">
            <div class="flex justify-between items-start mb-4">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden">
                    <img src="https://api.dicebear.com/7.x/identicon/svg?seed=ChatGPT" alt="Tool Logo" class="w-10 h-10">
                </div>
                <div class="flex gap-1 text-orange-400 items-center bg-orange-50 px-2 py-1 rounded-lg text-sm font-bold">
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i> 4.9
                </div>
            </div>
            <h3 class="text-xl font-bold text-slate-900 group-hover:text-primary transition-colors mb-2">ChatGPT</h3>
            <p class="text-slate-500 text-sm mb-6 flex-grow">地表最強 AI 寫作助理，支援程式碼編寫與各類創意構思。</p>
            <div class="flex flex-wrap gap-2 mb-6">
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md text-xs font-semibold">文本創作</span>
                <span class="px-3 py-1 bg-green-50 text-green-600 rounded-md text-xs font-semibold">免費版</span>
            </div>
            <a href="tool-detail.php?id=1" class="w-full mt-auto py-3 text-center bg-slate-100 hover:bg-primary hover:text-white text-slate-700 font-bold rounded-xl transition-all">
                查看詳情
            </a>
        </div>

        <!-- Tool Card 2 -->
        <div class="glass-card group p-5 flex flex-col h-full">
            <div class="flex justify-between items-start mb-4">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden">
                    <img src="https://api.dicebear.com/7.x/identicon/svg?seed=Midjourney" alt="Tool Logo" class="w-10 h-10">
                </div>
                <div class="flex gap-1 text-orange-400 items-center bg-orange-50 px-2 py-1 rounded-lg text-sm font-bold">
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i> 4.8
                </div>
            </div>
            <h3 class="text-xl font-bold text-slate-900 group-hover:text-primary transition-colors mb-2">Midjourney</h3>
            <p class="text-slate-500 text-sm mb-6 flex-grow">藝術級圖像生成工具，將您的想像轉化為超寫實畫作。</p>
            <div class="flex flex-wrap gap-2 mb-6">
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md text-xs font-semibold">圖像生成</span>
                <span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-md text-xs font-semibold">付費</span>
            </div>
            <a href="tool-detail.php?id=2" class="w-full mt-auto py-3 text-center bg-slate-100 hover:bg-primary hover:text-white text-slate-700 font-bold rounded-xl transition-all">
                查看詳情
            </a>
        </div>

        <!-- Tool Card 3 -->
        <div class="glass-card group p-5 flex flex-col h-full">
            <div class="flex justify-between items-start mb-4">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden">
                    <img src="https://api.dicebear.com/7.x/identicon/svg?seed=Jasper" alt="Tool Logo" class="w-10 h-10">
                </div>
                <div class="flex gap-1 text-orange-400 items-center bg-orange-50 px-2 py-1 rounded-lg text-sm font-bold">
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i> 4.7
                </div>
            </div>
            <h3 class="text-xl font-bold text-slate-900 group-hover:text-primary transition-colors mb-2">Jasper AI</h3>
            <p class="text-slate-500 text-sm mb-6 flex-grow">專業級行銷文案與部落格寫作工具，內建百種模板。</p>
            <div class="flex flex-wrap gap-2 mb-6">
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md text-xs font-semibold">行銷文案</span>
                <span class="px-3 py-1 bg-red-50 text-red-600 rounded-md text-xs font-semibold">訂閱制</span>
            </div>
            <a href="tool-detail.php?id=3" class="w-full mt-auto py-3 text-center bg-slate-100 hover:bg-primary hover:text-white text-slate-700 font-bold rounded-xl transition-all">
                查看詳情
            </a>
        </div>

        <!-- Tool Card 4 -->
        <div class="glass-card group p-5 flex flex-col h-full">
            <div class="flex justify-between items-start mb-4">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden">
                    <img src="https://api.dicebear.com/7.x/identicon/svg?seed=Notion" alt="Tool Logo" class="w-10 h-10">
                </div>
                <div class="flex gap-1 text-orange-400 items-center bg-orange-50 px-2 py-1 rounded-lg text-sm font-bold">
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i> 4.9
                </div>
            </div>
            <h3 class="text-xl font-bold text-slate-900 group-hover:text-primary transition-colors mb-2">Notion AI</h3>
            <p class="text-slate-500 text-sm mb-6 flex-grow">融合筆記、表格與 AI 助手，自動整理筆記與摘要內容。</p>
            <div class="flex flex-wrap gap-2 mb-6">
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md text-xs font-semibold">生產力</span>
                <span class="px-3 py-1 bg-green-50 text-green-600 rounded-md text-xs font-semibold">Freemium</span>
            </div>
            <a href="tool-detail.php?id=4" class="w-full mt-auto py-3 text-center bg-slate-100 hover:bg-primary hover:text-white text-slate-700 font-bold rounded-xl transition-all">
                查看詳情
            </a>
        </div>
    </div>
</section>

<!-- Category Entry Section -->
<section class="bg-slate-100 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-slate-900">按分類探索</h2>
            <p class="text-slate-500 mt-4">從文本生成到影片剪輯，我們已為您將 AI 精確分類。</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
            <a href="tools.php?cat=1" class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl hover:text-primary transition-all text-center group border border-slate-200">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-primary mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="type" class="w-8 h-8"></i>
                </div>
                <span class="font-bold">文本創作</span>
            </a>
            <a href="tools.php?cat=2" class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl hover:text-primary transition-all text-center group border border-slate-200">
                <div class="w-16 h-16 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="palette" class="w-8 h-8"></i>
                </div>
                <span class="font-bold">圖像生成</span>
            </a>
            <a href="tools.php?cat=3" class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl hover:text-primary transition-all text-center group border border-slate-200">
                <div class="w-16 h-16 rounded-2xl bg-orange-50 flex items-center justify-center text-orange-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="video" class="w-8 h-8"></i>
                </div>
                <span class="font-bold">影片剪輯</span>
            </a>
            <a href="tools.php?cat=4" class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl hover:text-primary transition-all text-center group border border-slate-200">
                <div class="w-16 h-16 rounded-2xl bg-green-50 flex items-center justify-center text-green-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="code" class="w-8 h-8"></i>
                </div>
                <span class="font-bold">程式開發</span>
            </a>
            <a href="tools.php?cat=5" class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl hover:text-primary transition-all text-center group border border-slate-200">
                <div class="w-16 h-16 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="music" class="w-8 h-8"></i>
                </div>
                <span class="font-bold">音訊處理</span>
            </a>
            <a href="tools.php?cat=6" class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl hover:text-primary transition-all text-center group border border-slate-200">
                <div class="w-16 h-16 rounded-2xl bg-yellow-50 flex items-center justify-center text-yellow-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="layout" class="w-8 h-8"></i>
                </div>
                <span class="font-bold">更多工具</span>
            </a>
        </div>
    </div>
</section>

<!-- Persona Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
        <div>
            <span class="text-primary font-bold tracking-widest uppercase text-sm">專屬推薦</span>
            <h2 class="text-4xl font-bold text-slate-900 mt-4 mb-6 leading-tight">為不同身分的您，量身打造工具清單。</h2>
            <div class="space-y-6">
                <div class="flex gap-4 p-4 rounded-xl border border-transparent hover:border-slate-200 hover:bg-white hover:shadow-lg transition-all">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-primary flex-shrink-0">
                        <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900">學生族群</h4>
                        <p class="text-slate-500">論文摘要、語言學習、數學解題助手。</p>
                    </div>
                </div>
                <div class="flex gap-4 p-4 rounded-xl border border-transparent hover:border-slate-200 hover:bg-white hover:shadow-lg transition-all">
                    <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 flex-shrink-0">
                        <i data-lucide="briefcase" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900">上班族</h4>
                        <p class="text-slate-500">會議紀錄、PPT 自動生成、Excel 專家。</p>
                    </div>
                </div>
                <div class="flex gap-4 p-4 rounded-xl border border-transparent hover:border-slate-200 hover:bg-white hover:shadow-lg transition-all">
                    <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 flex-shrink-0">
                        <i data-lucide="pen-tool" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900">創作者</h4>
                        <p class="text-slate-500">社群貼文、SEO 優化、Podcast 全自動後製。</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="relative">
            <div class="aspect-square rounded-3xl overflow-hidden shadow-2xl">
                <img src="https://images.unsplash.com/photo-1571171637578-41bc2dd41cd2?auto=format&fit=crop&q=80&w=1000" class="w-full h-full object-cover">
            </div>
            <div class="absolute -bottom-8 -left-8 bg-white p-6 rounded-2xl shadow-xl flex items-center gap-4 max-w-xs border border-slate-100">
                <div class="w-10 h-10 rounded-full bg-cta flex items-center justify-center text-white">
                    <i data-lucide="check" class="w-6 h-6"></i>
                </div>
                <span class="font-bold text-slate-800">已幫助 10,000+ 使用者提升工作效率</span>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
    <div class="bg-primary rounded-[2.5rem] p-12 md:p-20 text-center relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>

        <h2 class="text-3xl md:text-5xl font-bold text-white mb-8 relative z-10">
            如果您有更好的 AI 工具，<br>歡迎推薦給我們！
        </h2>
        <div class="flex flex-col sm:flex-row justify-center gap-4 relative z-10">
            <button class="bg-cta hover:bg-cta/90 text-white px-10 py-5 rounded-2xl font-bold text-lg shadow-xl hover:shadow-cta/20 transition-all">
                立即投稿工具
            </button>
            <button class="bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-sm px-10 py-5 rounded-2xl font-bold text-lg transition-all">
                加入社群討論
            </button>
        </div>
    </div>
</section>

<?php include 'layout/footer.php'; ?>