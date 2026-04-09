<script setup>
const emit = defineEmits(['openAuth'])
</script>

<template>
  <div class="relative z-10 animate-in fade-in duration-500">
    <!-- Hero -->
    <section class="px-6 pt-20 pb-16 md:pt-28 md:pb-24">
      <div class="max-w-5xl mx-auto text-center">
        <div class="inline-flex items-center rounded-full border border-slate-200 bg-white/70 backdrop-blur px-4 py-2 text-sm text-slate-600 shadow-sm">
          智慧整合你的 AI 工作流程
        </div>

        <h1 class="mt-8 text-5xl md:text-6xl font-bold text-slate-900 leading-tight">
          一站式 AI 工具平台
        </h1>

        <p class="mt-6 text-lg text-slate-600 max-w-2xl mx-auto leading-8">
          探索、比較並使用最適合你的 AI 工具，讓你的工作效率、創造力與決策速度全面提升。
        </p>

        <div class="mt-10 flex flex-col sm:flex-row justify-center gap-4">
          <button
            @click="emit('openAuth', 'register')"
            class="bg-primary hover:bg-primary/90 text-white px-8 py-4 rounded-xl font-semibold shadow-lg shadow-primary/20 transition-all cursor-pointer"
          >
            免費開始使用
          </button>

          <button
            @click="emit('openAuth', 'login')"
            class="bg-white/80 backdrop-blur border border-slate-200 hover:border-primary text-slate-700 px-8 py-4 rounded-xl font-semibold transition-all cursor-pointer"
          >
            已有帳號？登入
          </button>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="px-6 pb-24">
      <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-6">
        <div class="glass-card p-6 text-center hover:-translate-y-1 transition-transform">
          <h3 class="font-bold text-lg mb-2">AI 工具搜尋</h3>
          <p class="text-slate-500 text-sm">
            快速找到最適合你的 AI 工具
          </p>
        </div>

        <div class="glass-card p-6 text-center hover:-translate-y-1 transition-transform">
          <h3 class="font-bold text-lg mb-2">功能比較</h3>
          <p class="text-slate-500 text-sm">
            一鍵比較不同 AI 工具的優缺點
          </p>
        </div>

        <div class="glass-card p-6 text-center hover:-translate-y-1 transition-transform">
          <h3 class="font-bold text-lg mb-2">效率提升</h3>
          <p class="text-slate-500 text-sm">
            幫助你更快完成工作與創作
          </p>
        </div>
      </div>
    </section>
  </div>
</template>
