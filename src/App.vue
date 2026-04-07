<script setup>
import { ref } from 'vue';
import Navbar from './components/Navbar.vue';
import AuthModal from './components/AuthModal.vue';
import { Search, Zap, ArrowLeftRight, Bookmark, ChevronRight, Star, Type, Palette, Video, Code, Music, Layout, GraduationCap, Briefcase, PenTool, Check } from 'lucide-vue-next';

const isAuthModalOpen = ref(false);
const authMode = ref('login');

const openAuth = (mode) => {
  authMode.value = mode;
  isAuthModalOpen.value = true;
};
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <Navbar @openAuth="openAuth" />

    <!-- Hero Section -->
    <section class="relative py-24 overflow-hidden bg-slate-900 text-white">
      <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&q=80&w=2000')] bg-cover bg-center opacity-20"></div>
      <div class="absolute inset-0 bg-gradient-to-b from-slate-900/60 via-slate-900 to-slate-900"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6 tracking-tight">
          發現最適合您的 <span class="text-secondary underline decoration-primary/30">AI 工具</span>
        </h1>
        <p class="text-lg md:text-xl text-slate-300 mb-10 max-w-2xl mx-auto leading-relaxed">
          協助學生、上班族與創作者在數千款 AI 應用中，找到提升效率的終極武器。
        </p>

        <!-- Search Bar -->
        <div class="max-w-2xl mx-auto relative group">
          <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
            <Search class="w-6 h-6" />
          </div>
          <input type="text" placeholder="尋找：寫作助理、圖像生成、影片剪輯..."
            class="w-full pl-14 pr-32 py-5 bg-white text-slate-900 rounded-2xl shadow-2xl outline-none focus:ring-4 focus:ring-primary/20 transition-all text-lg">
          <button class="absolute inset-y-2 right-2 px-6 bg-primary hover:bg-primary/90 text-white rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-primary/20">
            搜尋
          </button>
        </div>

        <!-- Trending Tags -->
        <div class="mt-8 flex flex-wrap justify-center gap-4 text-sm text-slate-400">
          <span class="font-bold text-slate-500">熱門關鍵字：</span>
          <a href="#" class="px-3 py-1 bg-white/5 hover:bg-white/10 rounded-full hover:text-white transition-all cursor-pointer border border-white/10">#ChatGPT</a>
          <a href="#" class="px-3 py-1 bg-white/5 hover:bg-white/10 rounded-full hover:text-white transition-all cursor-pointer border border-white/10">#Midjourney</a>
          <a href="#" class="px-3 py-1 bg-white/5 hover:bg-white/10 rounded-full hover:text-white transition-all cursor-pointer border border-white/10">#效率提升</a>
        </div>
      </div>
    </section>

    <!-- Stats / Features Quick Look -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 relative z-10">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card p-6 flex items-center gap-4 hover:translate-y-[-4px] transition-all">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-primary">
            <Zap class="w-6 h-6" />
          </div>
          <div>
            <h3 class="font-bold text-slate-900 text-lg">快速發現</h3>
            <p class="text-slate-500 text-sm">每日更新最前衛的 AI 應用。</p>
          </div>
        </div>
        <div class="glass-card p-6 flex items-center gap-4 hover:translate-y-[-4px] transition-all">
          <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-cta">
            <ArrowLeftRight class="w-6 h-6" />
          </div>
          <div>
            <h3 class="font-bold text-slate-900 text-lg">深度比較</h3>
            <p class="text-slate-500 text-sm">直觀的功能與價格對比。</p>
          </div>
        </div>
        <div class="glass-card p-6 flex items-center gap-4 hover:translate-y-[-4px] transition-all">
          <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500">
            <Bookmark class="w-6 h-6" />
          </div>
          <div>
            <h3 class="font-bold text-slate-900 text-lg">個人收藏</h3>
            <p class="text-slate-500 text-sm">打造您的專屬 AI 工具箱。</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
      <div class="bg-primary rounded-[2.5rem] p-12 md:p-20 text-center relative overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>

        <h2 class="text-3xl md:text-5xl font-bold text-white mb-8 relative z-10 leading-tight">
          如果您有更好的 AI 工具，<br>歡迎與我們分享！
        </h2>
        <div class="flex flex-col sm:flex-row justify-center gap-6 relative z-10">
          <button @click="openAuth('register')" class="bg-cta hover:bg-cta/90 text-white px-10 py-5 rounded-2xl font-bold text-lg shadow-xl shadow-cta/20 transition-all cursor-pointer">
            立即加入社群
          </button>
          <button class="bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-sm px-10 py-5 rounded-2xl font-bold text-lg transition-all cursor-pointer">
            探索功能
          </button>
        </div>
      </div>
    </section>

    <AuthModal :isOpen="isAuthModalOpen" :initialMode="authMode" @close="isAuthModalOpen = false" />
    
    <footer class="py-12 text-center text-slate-400 border-t border-slate-200 bg-white">
      <p>&copy; 2026 AI Hub. 專業 AI 工具導航門戶</p>
    </footer>
  </div>
</template>
