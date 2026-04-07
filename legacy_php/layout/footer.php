    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 pt-16 pb-8 mt-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <!-- Branding -->
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        <i data-lucide="bot" class="w-8 h-8 text-primary"></i>
                        <span class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">AI Hub</span>
                    </div>
                    <p class="text-slate-500 leading-relaxed mb-6">
                        為學生、上班族與創作者打造的 AI 工具推薦平台。幫助您發現、比較、收藏最適合您的 AI 工具。
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-primary hover:text-white transition-all">
                            <i data-lucide="facebook" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-primary hover:text-white transition-all">
                            <i data-lucide="instagram" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-primary hover:text-white transition-all">
                            <i data-lucide="twitter" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-primary hover:text-white transition-all">
                            <i data-lucide="github" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>

                <!-- Links 1 -->
                <div>
                    <h3 class="text-slate-900 font-bold mb-6">快速連結</h3>
                    <ul class="space-y-4">
                        <li><a href="tools.php" class="text-slate-600 hover:text-primary transition-colors">所有工具</a></li>
                        <li><a href="compare.php" class="text-slate-600 hover:text-primary transition-colors">功能比較</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-primary transition-colors">熱門排行榜</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-primary transition-colors">最新推介</a></li>
                    </ul>
                </div>

                <!-- Links 2 -->
                <div>
                    <h3 class="text-slate-900 font-bold mb-6">分類入口</h3>
                    <ul class="space-y-4">
                        <li><a href="tools.php?category=text" class="text-slate-600 hover:text-primary transition-colors">文本創作</a></li>
                        <li><a href="tools.php?category=image" class="text-slate-600 hover:text-primary transition-colors">圖像生成</a></li>
                        <li><a href="tools.php?category=video" class="text-slate-600 hover:text-primary transition-colors">影片剪輯</a></li>
                        <li><a href="tools.php?category=productivity" class="text-slate-600 hover:text-primary transition-colors">生產力工具</a></li>
                    </ul>
                </div>

                <!-- Subscription -->
                <div>
                    <h3 class="text-slate-900 font-bold mb-6">訂閱電子報</h3>
                    <p class="text-slate-500 mb-4">獲得每週最新 AI 工具推報與實戰技巧。</p>
                    <div class="flex gap-2">
                        <input type="email" placeholder="您的電子信箱" class="flex-1 px-4 py-2 rounded-lg border border-slate-200 outline-none focus:border-primary transition-colors">
                        <button class="bg-primary text-white p-2 px-4 rounded-lg hover:bg-primary/90 transition-all">訂閱</button>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-8 flex flex-col md:flex-row justify-between items-center text-slate-400 text-sm">
                <p>© 2026 AI Hub. 保留所有權利。</p>
                <div class="flex gap-6 mt-4 md:mt-0">
                    <a href="#" class="hover:text-primary transition-colors">服務條款</a>
                    <a href="#" class="hover:text-primary transition-colors">隱私政策</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
    </body>

    </html>