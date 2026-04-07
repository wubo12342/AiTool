<?php include 'layout/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Profile Header -->
    <div class="flex flex-col md:flex-row items-center gap-8 mb-16 pb-12 border-b border-slate-100">
        <div class="w-24 h-24 rounded-full bg-primary/10 flex items-center justify-center text-primary border-4 border-white shadow-xl">
            <i data-lucide="user" class="w-12 h-12"></i>
        </div>
        <div class="text-center md:text-left">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">我的收藏清單</h1>
            <p class="text-slate-500">
                Hi, 測試使用者！這裡存放了您感興趣並隨時準備使用的 AI 工具。
            </p>
        </div>
        <div class="md:ml-auto flex gap-4">
            <div class="bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-sm text-center">
                <span class="block text-2xl font-black text-primary">12</span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">已收藏工具</span>
            </div>
            <div class="bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-sm text-center">
                <span class="block text-2xl font-black text-cta">5</span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">常用分類</span>
            </div>
        </div>
    </div>

    <!-- Collections Grid -->
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold text-slate-900">收藏的工具 (12)</h2>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:border-primary hover:text-primary transition-all flex items-center gap-2 font-medium">
                <i data-lucide="share-2" class="w-4 h-4"></i> 分享清單
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Collection Tool 1 -->
        <div class="glass-card group p-5 flex flex-col h-full relative">
            <button class="absolute top-4 right-4 text-red-400 hover:text-red-600 transition-colors p-2 bg-white rounded-lg shadow-sm border border-slate-100" title="移除收藏">
                <i data-lucide="trash-2" class="w-5 h-5 font-bold"></i>
            </button>

            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden mb-4">
                <img src="https://api.dicebear.com/7.x/identicon/svg?seed=ChatGPT" class="w-10 h-10">
            </div>
            <h3 class="text-xl font-bold text-slate-900 group-hover:text-primary transition-colors mb-2">ChatGPT</h3>
            <p class="text-slate-500 text-sm mb-6 flex-grow">
                地表最強 AI 寫作助理，支援程式碼編寫與各類創意構思。
            </p>
            <div class="flex flex-wrap gap-2 mb-6">
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md text-xs font-semibold">文本創作</span>
            </div>
            <a href="tool-detail.php?id=1" class="w-full mt-auto py-3 text-center bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all">
                立即使用
            </a>
        </div>

        <!-- Collection Tool 2 -->
        <div class="glass-card group p-5 flex flex-col h-full relative">
            <button class="absolute top-4 right-4 text-red-400 hover:text-red-600 transition-colors p-2 bg-white rounded-lg shadow-sm border border-slate-100" title="移除收藏">
                <i data-lucide="trash-2" class="w-5 h-5 font-bold"></i>
            </button>

            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden mb-4">
                <img src="https://api.dicebear.com/7.x/identicon/svg?seed=Midjourney" class="w-10 h-10">
            </div>
            <h3 class="text-xl font-bold text-slate-900 group-hover:text-primary transition-colors mb-2">Midjourney</h3>
            <p class="text-slate-500 text-sm mb-6 flex-grow">
                藝術級圖像生成工具，將您的想像轉化為超寫實畫作。
            </p>
            <div class="flex flex-wrap gap-2 mb-6">
                <span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-md text-xs font-semibold">圖像生成</span>
            </div>
            <a href="tool-detail.php?id=2" class="w-full mt-auto py-3 text-center bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all">
                立即使用
            </a>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>