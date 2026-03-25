<?php include 'layout/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Breadcrumb -->
    <nav class="flex text-slate-400 text-sm mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="index.php" class="hover:text-primary transition-colors">首頁</a>
            </li>
            <li class="inline-flex items-center">
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <a href="tools.php" class="ml-1 md:ml-2 hover:text-primary transition-colors">所有工具</a>
            </li>
            <li>
                <div class="flex items-center text-slate-900">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    <span class="ml-1 md:ml-2 font-medium">ChatGPT</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Tool Header Section -->
    <div class="glass-card p-8 md:p-12 mb-12">
        <div class="flex flex-col md:flex-row gap-10 items-start">
            <div class="w-24 h-24 md:w-32 md:h-32 rounded-3xl bg-slate-100 flex-shrink-0 flex items-center justify-center overflow-hidden border border-slate-200">
                <img src="https://api.dicebear.com/7.x/identicon/svg?seed=ChatGPT" alt="ChatGPT Logo" class="w-16 h-16 md:w-20 md:h-20">
            </div>

            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-4 mb-4">
                    <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900">ChatGPT</h1>
                    <span class="px-4 py-1.5 bg-blue-100 text-blue-700 rounded-full text-sm font-bold">文本創作</span>
                    <span class="px-4 py-1.5 bg-green-100 text-green-700 rounded-full text-sm font-bold">Freemium</span>
                </div>
                <p class="text-xl text-slate-500 mb-8 leading-relaxed max-w-3xl">
                    OpenAI 開發的人工智慧聊天機器人，基於 GPT 架構，能夠理解自然語言並進行複雜的對話、寫作與開發輔助。
                </p>

                <div class="flex flex-wrap gap-6 items-center">
                    <div class="flex items-center gap-2">
                        <div class="flex text-orange-400">
                            <i data-lucide="star" class="w-6 h-6 fill-current"></i>
                            <i data-lucide="star" class="w-6 h-6 fill-current"></i>
                            <i data-lucide="star" class="w-6 h-6 fill-current"></i>
                            <i data-lucide="star" class="w-6 h-6 fill-current"></i>
                            <i data-lucide="star" class="w-6 h-6 fill-current text-slate-200"></i>
                        </div>
                        <span class="text-xl font-bold text-slate-900">4.9 / 5.0</span>
                        <span class="text-slate-400 font-medium">(1,240 則評論)</span>
                    </div>
                    <div class="flex gap-4">
                        <button class="bg-primary hover:bg-primary/90 text-white px-8 py-4 rounded-2xl font-bold text-lg flex items-center gap-2 shadow-lg hover:shadow-primary/20 transition-all">
                            前往官方網站 <i data-lucide="external-link" class="w-5 h-5"></i>
                        </button>
                        <button class="bg-white border-2 border-slate-200 hover:border-red-400 hover:text-red-500 text-slate-700 px-6 py-4 rounded-2xl font-bold flex items-center gap-2 transition-all group">
                            <i data-lucide="heart" class="w-6 h-6 group-hover:scale-110 transition-transform"></i> 收藏工具
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <div class="lg:col-span-2 space-y-12">
            <!-- Detailed Description -->
            <section>
                <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                    <i data-lucide="file-text" class="w-6 h-6 text-primary"></i> 工具詳情與介紹
                </h2>
                <div class="prose prose-slate max-w-none text-slate-600 leading-8">
                    <p class="text-lg">
                        ChatGPT 是目前全球最受歡迎的人工智慧對話系統。無論您是需要撰寫電子郵件、創作故事、編寫程式碼，還是進行學術研究摘要，ChatGPT 都能提供即時且精確的協助。
                    </p>
                    <h3 class="text-xl font-bold text-slate-900 mt-8 mb-4">核心功能</h3>
                    <ul class="list-disc pl-6 space-y-3">
                        <li><strong>自然語言處理：</strong> 能夠以極其自然的方式與人類進行對話。</li>
                        <li><strong>多語言支援：</strong> 支援流暢的中文、英文、日文等多國語言翻譯與寫作。</li>
                        <li><strong>程式碼助理：</strong> 輔助開發者偵錯、解釋程式碼邏輯與生成範例。</li>
                        <li><strong>創意寫作：</strong> 提供詩歌、腳本、行銷文案等各種類型的創作靈感。</li>
                    </ul>
                </div>
            </section>

            <!-- Media/Screenshots placeholder -->
            <section>
                <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                    <i data-lucide="image" class="w-6 h-6 text-primary"></i> 介面預覽
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="aspect-video bg-slate-200 rounded-2xl flex items-center justify-center text-slate-400 font-bold border border-slate-300">
                        介面截圖 1
                    </div>
                    <div class="aspect-video bg-slate-200 rounded-2xl flex items-center justify-center text-slate-400 font-bold border border-slate-300">
                        介面截圖 2
                    </div>
                </div>
            </section>

            <!-- Reviews Section -->
            <section id="reviews">
                <div class="flex justify-between items-center mb-8 border-b border-slate-200 pb-4">
                    <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
                        <i data-lucide="message-square" class="w-6 h-6 text-primary"></i> 使用者評論
                    </h2>
                    <button class="text-primary font-bold hover:underline">我也要評論</button>
                </div>

                <div class="space-y-8">
                    <!-- Comment 1 -->
                    <div class="flex gap-6 p-6 rounded-2xl border border-slate-100 bg-white shadow-sm">
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex-shrink-0 flex items-center justify-center text-primary font-bold text-lg">
                            W
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="font-bold text-slate-900">Wang Xiao Ming</span>
                                <div class="flex text-orange-400 scale-75">
                                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                </div>
                                <span class="text-slate-400 text-sm">2 天前</span>
                            </div>
                            <p class="text-slate-600 leading-relaxed">
                                真的太好用了！現在寫報告或是回信都會先請它幫我檢查語法跟潤飾。
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Sidebar Info -->
        <aside class="space-y-12">
            <!-- Quick Specs -->
            <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-900 mb-6 text-lg">工具資訊</h3>
                <dl class="space-y-6">
                    <div>
                        <dt class="text-slate-400 text-sm mb-1">開發商</dt>
                        <dd class="text-slate-900 font-bold">OpenAI</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 text-sm mb-1">支援平台</dt>
                        <dd class="text-slate-900 font-bold">Web, iOS, Android</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 text-sm mb-1">API 開放</dt>
                        <dd class="inline-flex items-center gap-2 bg-green-50 text-green-600 px-3 py-1 rounded-md text-xs font-bold">
                            <i data-lucide="check-circle" class="w-4 h-4"></i> 支援 API
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 text-sm mb-1">主要分類</dt>
                        <dd class="flex flex-wrap gap-2 pt-1">
                            <span class="bg-slate-100 px-3 py-1 rounded-md text-xs font-bold">LLM</span>
                            <span class="bg-slate-100 px-3 py-1 rounded-md text-xs font-bold">寫作工具</span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Related Tools -->
            <div>
                <h3 class="font-bold text-slate-900 mb-6 text-lg">您可能也會感興趣</h3>
                <div class="space-y-4">
                    <a href="#" class="flex items-center gap-4 group p-3 rounded-xl hover:bg-slate-100 transition-all border border-transparent hover:border-slate-200">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden">
                            <img src="https://api.dicebear.com/7.x/identicon/svg?seed=Claude" alt="Claude" class="w-8 h-8">
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 group-hover:text-primary transition-colors">Claude 3</h4>
                            <p class="text-slate-400 text-xs">長文本處理專家</p>
                        </div>
                    </a>
                    <a href="#" class="flex items-center gap-4 group p-3 rounded-xl hover:bg-slate-100 transition-all border border-transparent hover:border-slate-200">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden">
                            <img src="https://api.dicebear.com/7.x/identicon/svg?seed=Gemini" alt="Gemini" class="w-8 h-8">
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 group-hover:text-primary transition-colors">Google Gemini</h4>
                            <p class="text-slate-400 text-xs">多模態 AI 領導者</p>
                        </div>
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php include 'layout/footer.php'; ?>