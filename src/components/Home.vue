<script setup>
import { 
  Search, 
  Zap, 
  ArrowLeftRight, 
  Bookmark, 
  ChevronRight, 
  Star, 
  Type, 
  Palette, 
  Video, 
  Code, 
  Music, 
  Layout, 
  GraduationCap, 
  Briefcase, 
  PenTool, 
  Check 
} from 'lucide-vue-next';
import ToolCard from './ToolCard.vue';

const popularTools = [
  {
    id: 1,
    name: 'ChatGPT',
    description: '地表最強 AI 寫作助理，支援程式碼編寫與各類創意構思。',
    logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=ChatGPT',
    rating: 4.9,
    tags: ['文本創作', '免費版']
  },
  {
    id: 2,
    name: 'Midjourney',
    description: '藝術級圖像生成工具，將您的想像轉化為超寫實畫作。',
    logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=Midjourney',
    rating: 4.8,
    tags: ['圖像生成', '付費']
  },
  {
    id: 3,
    name: 'Jasper AI',
    description: '專業級行銷文案與部落格寫作工具，內建百種模板。',
    logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=Jasper',
    rating: 4.7,
    tags: ['行銷文案', '訂閱制']
  },
  {
    id: 4,
    name: 'Notion AI',
    description: '融合筆記、表格與 AI 助手，自動整理筆記與摘要內容。',
    logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=Notion',
    rating: 4.9,
    tags: ['生產力', 'Freemium']
  }
];
</script>

<template>
  <div class="relative z-10 animate-in fade-in duration-500 text-slate-900">
    <!-- Hero Section (Legacy Style) -->
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
            <Search class="w-6 h-6" />
          </div>
          <input type="text" placeholder="尋找：寫作助理、圖像生成、影片剪輯..."
            class="w-full pl-14 pr-32 py-5 bg-white text-slate-900 rounded-2xl shadow-2xl outline-none focus:ring-4 focus:ring-primary/20 transition-all text-lg border-none">
          <button class="absolute inset-y-2 right-2 px-6 bg-primary hover:bg-primary/90 text-white rounded-xl font-bold transition-all flex items-center gap-2 border-none cursor-pointer">
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
        <div class="bg-white p-6 rounded-2xl shadow-xl flex items-center gap-4 hover:-translate-y-1 transition-transform border border-slate-100">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-primary flex-shrink-0">
            <Zap class="w-6 h-6" />
          </div>
          <div>
            <h3 class="font-bold text-slate-900 text-lg">快速發現</h3>
            <p class="text-slate-500 text-sm">每日更新最前衛的 AI 應用。</p>
          </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl flex items-center gap-4 hover:-translate-y-1 transition-transform border border-slate-100">
          <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-cta flex-shrink-0">
            <ArrowLeftRight class="w-6 h-6" />
          </div>
          <div>
            <h3 class="font-bold text-slate-900 text-lg">深度比較</h3>
            <p class="text-slate-500 text-sm">直觀的功能與價格對比。</p>
          </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl flex items-center gap-4 hover:-translate-y-1 transition-transform border border-slate-100">
          <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500 flex-shrink-0">
            <Bookmark class="w-6 h-6" />
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
        <a href="/tools" class="text-primary font-semibold flex items-center gap-1 hover:underline cursor-pointer">
          查看更多 <ChevronRight class="w-4 h-4" />
        </a>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <ToolCard 
          v-for="tool in popularTools" 
          :key="tool.id" 
          v-bind="tool"
          @click="console.log('Click tool:', tool.name)"
        />
      </div>
    </section>

    <!-- Category Entry Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 border-t border-slate-200/50">
      <div class="text-center mb-16">
        <h2 class="text-3xl font-bold text-slate-900">按分類探索</h2>
        <p class="text-slate-500 mt-4">從文本生成到影片剪輯，我們已為您將 AI 精確分類。</p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
        <a href="#" class="glass-card bg-white/40 p-8 rounded-2xl hover:shadow-xl hover:bg-white hover:text-primary transition-all text-center group">
          <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-primary mx-auto mb-4 group-hover:scale-110 transition-transform">
            <Type class="w-8 h-8" />
          </div>
          <span class="font-bold">文本創作</span>
        </a>
        <a href="#" class="glass-card bg-white/40 p-8 rounded-2xl hover:shadow-xl hover:bg-white hover:text-primary transition-all text-center group">
          <div class="w-16 h-16 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
            <Palette class="w-8 h-8" />
          </div>
          <span class="font-bold">圖像生成</span>
        </a>
        <a href="#" class="glass-card bg-white/40 p-8 rounded-2xl hover:shadow-xl hover:bg-white hover:text-primary transition-all text-center group">
          <div class="w-16 h-16 rounded-2xl bg-orange-50 flex items-center justify-center text-orange-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
            <Video class="w-8 h-8" />
          </div>
          <span class="font-bold">影片剪輯</span>
        </a>
        <a href="#" class="glass-card bg-white/40 p-8 rounded-2xl hover:shadow-xl hover:bg-white hover:text-primary transition-all text-center group">
          <div class="w-16 h-16 rounded-2xl bg-green-50 flex items-center justify-center text-green-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
            <Code class="w-8 h-8" />
          </div>
          <span class="font-bold">程式開發</span>
        </a>
        <a href="#" class="glass-card bg-white/40 p-8 rounded-2xl hover:shadow-xl hover:bg-white hover:text-primary transition-all text-center group">
          <div class="w-16 h-16 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
            <Music class="w-8 h-8" />
          </div>
          <span class="font-bold">音訊處理</span>
        </a>
        <a href="#" class="glass-card bg-white/40 p-8 rounded-2xl hover:shadow-xl hover:bg-white hover:text-primary transition-all text-center group">
          <div class="w-16 h-16 rounded-2xl bg-yellow-50 flex items-center justify-center text-yellow-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
            <Layout class="w-8 h-8" />
          </div>
          <span class="font-bold">更多工具</span>
        </a>
      </div>
    </section>

    <!-- Persona Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
        <div>
          <span class="text-primary font-bold tracking-widest uppercase text-sm">專屬推薦</span>
          <h2 class="text-4xl font-bold text-slate-900 mt-4 mb-6 leading-tight">為不同身分的您，量身打造工具清單。</h2>
          <div class="space-y-6">
            <div class="flex gap-4 p-4 rounded-xl border border-transparent hover:border-slate-200 hover:bg-white/50 hover:shadow-lg transition-all cursor-default">
              <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-primary flex-shrink-0">
                <GraduationCap class="w-6 h-6" />
              </div>
              <div>
                <h4 class="font-bold text-slate-900">學生族群</h4>
                <p class="text-slate-500">論文摘要、語言學習、數學解題助手。</p>
              </div>
            </div>
            <div class="flex gap-4 p-4 rounded-xl border border-transparent hover:border-slate-200 hover:bg-white/50 hover:shadow-lg transition-all cursor-default">
              <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 flex-shrink-0">
                <Briefcase class="w-6 h-6" />
              </div>
              <div>
                <h4 class="font-bold text-slate-900">上班族</h4>
                <p class="text-slate-500">會議紀錄、PPT 自動生成、Excel 專家。</p>
              </div>
            </div>
            <div class="flex gap-4 p-4 rounded-xl border border-transparent hover:border-slate-200 hover:bg-white/50 hover:shadow-lg transition-all cursor-default">
              <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 flex-shrink-0">
                <PenTool class="w-6 h-6" />
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
          <div class="absolute -bottom-8 -left-8 bg-white/95 p-6 rounded-2xl shadow-xl flex items-center gap-4 max-w-xs border border-slate-100">
            <div class="w-10 h-10 rounded-full bg-cta flex items-center justify-center text-white">
              <Check class="w-6 h-6" />
            </div>
            <span class="font-bold text-slate-800">已幫助 10,000+ 使用者提升工作效率</span>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
      <div class="bg-gradient-to-br from-primary to-primary/80 rounded-[2.5rem] p-12 md:p-20 text-center relative overflow-hidden shadow-2xl shadow-primary/20">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>

        <h2 class="text-3xl md:text-5xl font-bold text-white mb-8 relative z-10 leading-tight">
          如果您有更好的 AI 工具，<br>歡迎推薦給我們！
        </h2>
        <div class="flex flex-col sm:flex-row justify-center gap-4 relative z-10">
          <button class="bg-cta hover:bg-cta/90 text-white px-10 py-5 rounded-2xl font-bold text-lg shadow-xl hover:shadow-cta/20 transition-all cursor-pointer border border-white/10">
            立即投稿工具
          </button>
          <button class="bg-white/20 hover:bg-white/30 text-white border border-white/20 px-10 py-5 rounded-2xl font-bold text-lg transition-all cursor-pointer">
            加入社群討論
          </button>
        </div>
      </div>
    </section>

    <footer class="py-12 text-center text-slate-400/80 mt-12 bg-transparent">
      <p>&copy; 2026 AI Hub. 專業 AI 工具導航門戶</p>
    </footer>
  </div>
</template>
