<?php include 'layout/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
    <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6">比較 AI 工具</h1>
    <p class="text-slate-500 max-w-2xl mx-auto mb-16">
        猶豫不決嗎？選擇兩個工具進行並排比較，找出最符合您工作流程且 CP 值最高的選擇。
    </p>

    <!-- Selection Area -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 items-center gap-8 mb-20">
        <!-- Tool 1 Select -->
        <div class="lg:col-span-2 space-y-4">
            <label class="block text-left text-slate-700 font-bold mb-2">選擇第一個工具</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </div>
                <input type="text" value="ChatGPT" class="w-full pl-12 pr-4 py-4 rounded-2xl border-2 border-primary outline-none focus:ring-4 focus:ring-primary/10 transition-all font-bold text-slate-900">
            </div>
        </div>

        <!-- VS -->
        <div class="hidden lg:flex flex-col items-center justify-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-black text-2xl border-4 border-white shadow-lg">VS</div>
        </div>

        <!-- Tool 2 Select -->
        <div class="lg:col-span-2 space-y-4">
            <label class="block text-left text-slate-700 font-bold mb-2">選擇第二個工具</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </div>
                <input type="text" placeholder="尋找工具..." class="w-full pl-12 pr-4 py-4 rounded-2xl border-2 border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 transition-all font-bold text-slate-900">
            </div>
        </div>
    </div>

    <!-- Comparison Table -->
    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100 mb-20">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="p-8 w-1/3 text-slate-500 font-bold uppercase tracking-wider text-sm">功能指標</th>
                        <th class="p-8 w-1/3 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 flex items-center justify-center">
                                    <img src="https://api.dicebear.com/7.x/identicon/svg?seed=ChatGPT" class="w-10 h-10">
                                </div>
                                <span class="font-bold text-slate-900 text-lg">ChatGPT</span>
                            </div>
                        </th>
                        <th class="p-8 w-1/3 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 flex items-center justify-center">
                                    <img src="https://api.dicebear.com/7.x/identicon/svg?seed=Claude" class="w-10 h-10">
                                </div>
                                <span class="font-bold text-slate-900 text-lg">Claude 3</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-700">
                    <tr>
                        <td class="p-8 font-bold bg-slate-50/50">核心定位</td>
                        <td class="p-8 text-center">全能通用助手</td>
                        <td class="p-8 text-center text-primary font-medium">長文本、高品質輸出</td>
                    </tr>
                    <tr>
                        <td class="p-8 font-bold bg-slate-50/50">免費方案</td>
                        <td class="p-8 text-center">
                            <i data-lucide="check-circle-2" class="w-6 h-6 text-cta mx-auto"></i>
                        </td>
                        <td class="p-8 text-center">
                            <i data-lucide="check-circle-2" class="w-6 h-6 text-cta mx-auto"></i>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-8 font-bold bg-slate-50/50">付費價格 (美金/月)</td>
                        <td class="p-8 text-center">$20 / 月</td>
                        <td class="p-8 text-center">$20 / 月</td>
                    </tr>
                    <tr>
                        <td class="p-8 font-bold bg-slate-50/50">語言模型架構</td>
                        <td class="p-8 text-center">GPT-4 / GPT-3.5</td>
                        <td class="p-8 text-center">Claude 3 Opus/Sonnet</td>
                    </tr>
                    <tr>
                        <td class="p-8 font-bold bg-slate-50/50">視覺輸入支援</td>
                        <td class="p-8 text-center">
                            <i data-lucide="check-circle-2" class="w-6 h-6 text-cta mx-auto"></i>
                        </td>
                        <td class="p-8 text-center">
                            <i data-lucide="check-circle-2" class="w-6 h-6 text-cta mx-auto"></i>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-8 font-bold bg-slate-50/50">上下文視窗頻寬</td>
                        <td class="p-8 text-center">約 128k tokens</td>
                        <td class="p-8 text-center text-primary font-bold">高達 200k tokens</td>
                    </tr>
                    <tr>
                        <td class="p-8 font-bold bg-slate-50/50">總體評分</td>
                        <td class="p-8 text-center">
                            <div class="flex items-center justify-center gap-1 text-orange-400">
                                <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                <span class="font-bold">4.9</span>
                            </div>
                        </td>
                        <td class="p-8 text-center">
                            <div class="flex items-center justify-center gap-1 text-orange-400">
                                <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                <span class="font-bold">4.8</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>