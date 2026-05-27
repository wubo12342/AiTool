<script setup>
import { Star, Heart, ExternalLink } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

const props = defineProps({
  id: [Number, String],
  name: String,
  description: String,
  logoUrl: String,
  rating: [Number, String],
  tags: { type: Array, default: () => [] },
  isFavorited: { type: Boolean, default: false },
  showFavoriteButton: { type: Boolean, default: false },
  disableFlip: { type: Boolean, default: false }
})

const emit = defineEmits(['click', 'toggleFavorite'])
const router = useRouter()

const goDetail = () => router.push(`/tool/${props.id}`)

const TAG_COLORS = {
  '文字生成': 'bg-blue-100 text-blue-700',
  '圖像生成': 'bg-purple-100 text-purple-700',
  '影片製作': 'bg-orange-100 text-orange-700',
  '程式開發': 'bg-green-100 text-green-700',
  '語音生成': 'bg-red-100 text-red-700',
  '簡報設計': 'bg-yellow-100 text-yellow-700',
  '資料整理': 'bg-indigo-100 text-indigo-700',
  '翻譯語言': 'bg-teal-100 text-teal-700',
  'Free': 'bg-emerald-100 text-emerald-700',
  'Freemium': 'bg-cyan-100 text-cyan-700',
  'Paid': 'bg-rose-100 text-rose-700',
}

const getTagColor = (tag) => TAG_COLORS[tag] || 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'
</script>

<template>
  <!-- 翻轉卡片模式 -->
  <div v-if="!disableFlip" class="flip-card group" @click="emit('click')">
    <div class="flip-card-inner">

      <!-- ===== 正面 ===== -->
      <div class="flip-card-face flip-card-front">
        <div class="flex flex-col h-full">
          <!-- Logo row -->
          <div class="flex justify-between items-start mb-4">
            <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-700 shadow-md ring-1 ring-slate-100 dark:ring-slate-600 flex items-center justify-center overflow-hidden">
              <img :src="logoUrl" :alt="name" class="w-10 h-10 object-contain">
            </div>

            <div class="flex flex-col items-end gap-2">
              <div class="flex items-center gap-1 bg-amber-50 dark:bg-amber-900/20 text-amber-500 px-2.5 py-1 rounded-full text-sm font-bold">
                <Star class="w-3.5 h-3.5 fill-current" />
                {{ rating }}
              </div>
              <button
                v-if="showFavoriteButton"
                @click.stop="emit('toggleFavorite')"
                class="p-1.5 rounded-full transition-all"
                :class="isFavorited
                  ? 'text-rose-500 bg-rose-50 dark:bg-rose-900/30'
                  : 'text-slate-300 dark:text-slate-600 hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20'"
                type="button"
              >
                <Heart class="w-4 h-4" :class="{ 'fill-current': isFavorited }" />
              </button>
            </div>
          </div>

          <!-- Name -->
          <h3 class="text-xl font-bold text-slate-900 dark:text-white leading-snug mb-1 line-clamp-1">
            {{ name }}
          </h3>

          <!-- Category subtitle -->
          <p class="text-xs font-medium text-slate-400 dark:text-slate-500 mb-auto">
            {{ tags[0] || '&nbsp;' }}
          </p>

          <!-- Tags: price + feature (skip category) -->
          <div class="flex flex-wrap gap-1.5 mt-4">
            <span
              v-for="(tag, i) in tags.slice(1, 4)"
              :key="i"
              class="px-2.5 py-0.5 rounded-lg text-xs font-semibold"
              :class="getTagColor(tag)"
            >
              {{ tag }}
            </span>
            <span
              v-if="tags.length > 4"
              class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-400"
            >
              +{{ tags.length - 4 }}
            </span>
          </div>

          <!-- Flip hint -->
          <p class="text-[10px] tracking-widest text-slate-300 dark:text-slate-700 text-center uppercase mt-3 font-medium">
            hover to explore
          </p>
        </div>
      </div>

      <!-- ===== 背面 ===== -->
      <div class="flip-card-face flip-card-back">
        <div class="flex flex-col h-full">
          <!-- Header -->
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden flex-shrink-0">
                <img :src="logoUrl" :alt="name" class="w-7 h-7 object-contain">
              </div>
              <div class="min-w-0">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white leading-none truncate">{{ name }}</h3>
                <div class="flex items-center gap-1 text-amber-500 mt-1">
                  <Star class="w-3 h-3 fill-current flex-shrink-0" />
                  <span class="text-xs font-bold">{{ rating }}</span>
                </div>
              </div>
            </div>

            <button
              v-if="showFavoriteButton"
              @click.stop="emit('toggleFavorite')"
              class="p-1.5 rounded-full transition-all flex-shrink-0 ml-2"
              :class="isFavorited
                ? 'text-rose-500 bg-rose-50 dark:bg-rose-900/30'
                : 'text-slate-300 dark:text-slate-600 hover:text-rose-400'"
              type="button"
            >
              <Heart class="w-4 h-4" :class="{ 'fill-current': isFavorited }" />
            </button>
          </div>

          <!-- Description -->
          <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400 flex-grow line-clamp-5">
            {{ description }}
          </p>

          <!-- Tags -->
          <div class="flex flex-wrap gap-1.5 my-3">
            <span
              v-for="(tag, i) in tags.slice(0, 3)"
              :key="i"
              class="px-2 py-0.5 rounded-md text-xs font-semibold"
              :class="getTagColor(tag)"
            >
              {{ tag }}
            </span>
            <span
              v-if="tags.length > 3"
              class="px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-400"
            >
              +{{ tags.length - 3 }}
            </span>
          </div>

          <!-- CTA -->
          <button
            @click.stop="goDetail"
            class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-bold rounded-xl transition-all flex items-center justify-center gap-2 border-none cursor-pointer shadow-md shadow-blue-500/20"
            type="button"
          >
            查看詳情
            <ExternalLink class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- 靜態卡片模式 (無翻轉) -->
  <div v-else class="static-card group cursor-pointer" @click="emit('click')">
    <div class="relative flex flex-col h-full p-6 bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-[2rem] border border-white/40 dark:border-slate-700/50 shadow-xl hover:shadow-2xl hover:border-blue-200 dark:hover:border-blue-700/50 transition-all duration-300 min-h-[320px] overflow-hidden">
      <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden group-hover:scale-105 transition-transform shadow-sm">
            <img :src="logoUrl" :alt="name" class="w-8 h-8 object-contain">
          </div>
          <div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 group-hover:text-primary transition-colors">
              {{ name }}
            </h3>
            <div class="flex items-center gap-1 text-amber-500 mt-0.5">
              <Star class="w-3.5 h-3.5 fill-current" />
              <span class="text-sm font-bold">{{ rating }}</span>
            </div>
          </div>
        </div>

        <button
          v-if="showFavoriteButton"
          @click.stop="emit('toggleFavorite')"
          class="p-2 rounded-full transition-colors"
          :class="isFavorited ? 'text-rose-500 bg-rose-50 dark:bg-rose-900/30' : 'text-slate-300 hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20'"
          type="button"
        >
          <Heart class="w-5 h-5" :class="{ 'fill-current': isFavorited }" />
        </button>
      </div>

      <p class="text-sm leading-7 text-slate-600 dark:text-slate-300 flex-grow mb-4 line-clamp-4">
        {{ description }}
      </p>

      <div class="flex flex-wrap gap-2 mb-4">
        <span
          v-for="(tag, i) in tags.slice(0, 4)"
          :key="i"
          class="px-2.5 py-0.5 rounded-lg text-xs font-semibold"
          :class="getTagColor(tag)"
        >
          {{ tag }}
        </span>
        <span v-if="tags.length > 4" class="px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-400">
          +{{ tags.length - 4 }}
        </span>
      </div>

      <button
        @click.stop="goDetail"
        class="w-full py-3 bg-slate-100 dark:bg-slate-700 hover:bg-gradient-to-r hover:from-blue-600 hover:to-indigo-600 hover:text-white text-slate-700 dark:text-slate-200 font-bold rounded-xl transition-all border-none cursor-pointer flex items-center justify-center gap-2"
      >
        查看詳情
        <ExternalLink class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>

<style scoped>
.flip-card {
  height: 320px;
  perspective: 1200px;
}

.flip-card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  transition: transform 0.65s cubic-bezier(0.4, 0, 0.2, 1);
  transform-style: preserve-3d;
}

.flip-card:hover .flip-card-inner {
  transform: rotateY(180deg);
}

.flip-card-face {
  position: absolute;
  inset: 0;
  border-radius: 2rem;
  padding: 1.5rem;
  backface-visibility: hidden;
  overflow: hidden;
  backdrop-filter: blur(16px);
}

.flip-card-front {
  background: rgba(255, 255, 255, 0.92);
  box-shadow:
    0 8px 32px rgba(15, 23, 42, 0.07),
    0 0 0 1px rgba(226, 232, 240, 0.9);
}

.flip-card-back {
  background: rgba(255, 255, 255, 0.97);
  transform: rotateY(180deg);
  box-shadow:
    0 8px 32px rgba(15, 23, 42, 0.1),
    0 0 0 1px rgba(59, 130, 246, 0.12);
}

:global(.dark) .flip-card-front {
  background: rgba(30, 41, 59, 0.92);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(71, 85, 105, 0.5);
}

:global(.dark) .flip-card-back {
  background: rgba(15, 23, 42, 0.97);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(59, 130, 246, 0.15);
}

.flip-card:hover .flip-card-front {
  box-shadow:
    0 20px 48px rgba(15, 23, 42, 0.12),
    0 0 0 1px rgba(59, 130, 246, 0.15);
}

.static-card {
  height: 100%;
}
</style>
