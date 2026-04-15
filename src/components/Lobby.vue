<script setup>
const emit = defineEmits(['openAuth'])
import { Search, ArrowLeftRight, Zap } from 'lucide-vue-next'
</script>

<template>
  <div class="relative z-10 animate-in fade-in duration-500">
    <!-- Hero Section -->
    <section class="px-6 pt-20 pb-16 md:pt-28 md:pb-24">
      <div class="max-w-5xl mx-auto text-center">
        <div class="inline-flex items-center rounded-full border border-slate-200 bg-white/95 px-4 py-2 text-sm text-slate-600 shadow-sm mb-8">
          智慧整合你的 AI 工作流程
        </div>

        <h1 class="text-5xl md:text-6xl font-extrabold text-slate-900 leading-tight tracking-tight mb-8">
          一站式 AI 工具平台
        </h1>

        <p class="text-lg md:text-xl text-slate-600 max-w-2xl mx-auto leading-relaxed mb-10">
          探索、比較並使用最適合你的 AI 工具，讓你的工作效率、創造力與決策速度全面提升。
        </p>

        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <button
            @click="emit('openAuth', 'register')"
            class="bg-primary hover:bg-primary/90 text-white px-10 py-5 rounded-2xl font-bold shadow-xl shadow-primary/20 transition-all cursor-pointer border-none text-lg"
          >
            免費開始使用
          </button>

          <button
            @click="emit('openAuth', 'login')"
            class="bg-white border border-slate-200 hover:border-primary text-slate-700 px-10 py-5 rounded-2xl font-bold shadow-sm transition-all cursor-pointer text-lg"
          >
            已有帳號？登入
          </button>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="max-w-7xl mx-auto px-6 pb-32">
      <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-white/80 p-10 rounded-[2.5rem] text-center hover:-translate-y-2 transition-all shadow-xl border border-white">
          <div class="w-16 h-16 bg-blue-50 text-primary rounded-2xl flex items-center justify-center mx-auto mb-6">
            <Search class="w-8 h-8" />
          </div>
          <h3 class="font-bold text-xl text-slate-900 mb-4">AI 工具搜尋</h3>
          <p class="text-slate-500 leading-relaxed">
            透過強大的分類與搜尋功能，快速找到符合您需求的優質 AI 應用。
          </p>
        </div>

        <div class="bg-white/80 p-10 rounded-[2.5rem] text-center hover:-translate-y-2 transition-all shadow-xl border border-white">
          <div class="w-16 h-16 bg-green-50 text-cta rounded-2xl flex items-center justify-center mx-auto mb-6">
            <ArrowLeftRight class="w-8 h-8" />
          </div>
          <h3 class="font-bold text-xl text-slate-900 mb-4">功能比較</h3>
          <p class="text-slate-500 leading-relaxed">
            直觀比較不同工具的優缺點與價格，幫助您做出最明智的選擇。
          </p>
        </div>

        <div class="bg-white/80 p-10 rounded-[2.5rem] text-center hover:-translate-y-2 transition-all shadow-xl border border-white">
          <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <Zap class="w-8 h-8" />
          </div>
          <h3 class="font-bold text-xl text-slate-900 mb-4">效率提升</h3>
          <p class="text-slate-500 leading-relaxed">
            自動化繁瑣任務，讓您將寶貴時間投入在更具創造力的核心事務。
          </p>
        </div>
      </div>
    </section>
  </div>
</template>
