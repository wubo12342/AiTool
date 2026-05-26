<script setup>
import { Star, Heart, ExternalLink } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

const props = defineProps({
  id: [Number, String],
  name: String,
  description: String,
  logoUrl: String,
  rating: [Number, String],
  tags: {
    type: Array,
    default: () => []
  },
  isFavorited: {
    type: Boolean,
    default: false
  },
  showFavoriteButton: {
    type: Boolean,
    default: false
  },
  disableFlip: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click', 'toggleFavorite'])

const router = useRouter()

const goDetail = () => {
  router.push(`/tool/${props.id}`)
}

const getTagColor = (tagName) => {
  const colors = {
    '文字生成': 'bg-blue-100 text-blue-700',
    '圖像生成': 'bg-purple-100 text-purple-700',
    '影片製作': 'bg-orange-100 text-orange-700',
    '程式開發': 'bg-green-100 text-green-700',
    '語音生成': 'bg-red-100 text-red-700',
    '簡報設計': 'bg-yellow-100 text-yellow-700',
    '資料整理': 'bg-indigo-100 text-indigo-700',
    '翻譯語言': 'bg-teal-100 text-teal-700',
    '免費版': 'bg-emerald-100 text-emerald-700',
    '付費': 'bg-slate-200 text-slate-800',
    '訂閱制': 'bg-amber-100 text-amber-700',
    'Freemium': 'bg-cyan-100 text-cyan-700'
  }
  return colors[tagName] || 'bg-slate-100 text-slate-600'
}
</script>

<template>
  <!-- 翻轉卡片模式 -->
  <div v-if="!disableFlip" class="flip-card group cursor-pointer" @click="emit('click')">
    <div class="flip-card-inner">
      <!-- 正面 -->
      <div class="flip-card-face flip-card-front">
        <div class="flex flex-col h-full">
          <div class="flex justify-between items-start mb-5">
            <div class="w-16 h-16 rounded-2xl bg-white dark:bg-slate-700 shadow-md flex items-center justify-center overflow-hidden">
              <img :src="logoUrl" :alt="name" class="w-11 h-11 object-contain">
            </div>

            <div class="flex flex-col items-end gap-2">
              <div class="flex gap-1 text-orange-400 items-center bg-orange-50 dark:bg-orange-900/30 px-2 py-1 rounded-lg text-sm font-bold">
                <Star class="w-4 h-4 fill-current" />
                {{ rating }}
              </div>

              <button
                v-if="showFavoriteButton"
                @click.stop="emit('toggleFavorite')"
                class="p-2 rounded-full transition-colors"
                :class="isFavorited ? 'text-red-500 bg-red-50 dark:bg-red-900/30' : 'text-slate-400 bg-slate-100 dark:bg-slate-700 hover:text-red-500'"
                type="button"
              >
                <Heart class="w-5 h-5" :class="{ 'fill-current': isFavorited }" />
              </button>
            </div>
          </div>

          <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-4">
            {{ name }}
          </h3>

          <div class="flex flex-wrap gap-2 mt-auto">
            <span
              v-for="(tag, index) in tags"
              :key="index"
              class="px-3 py-1 rounded-md text-base font-semibold"
              :class="getTagColor(tag)"
            >
              {{ tag }}
            </span>
          </div>
        </div>
      </div>

      <!-- 背面 -->
      <div class="flip-card-face flip-card-back">
        <div class="flex flex-col h-full">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden">
                <img :src="logoUrl" :alt="name" class="w-8 h-8 object-contain">
              </div>

              <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                  {{ name }}
                </h3>
                <div class="flex gap-1 text-orange-400 items-center text-sm font-bold mt-1">
                  <Star class="w-4 h-4 fill-current" />
                  {{ rating }}
                </div>
              </div>
            </div>

            <button
              v-if="showFavoriteButton"
              @click.stop="emit('toggleFavorite')"
              class="p-2 rounded-full transition-colors"
              :class="isFavorited ? 'text-red-500 bg-red-50 dark:bg-red-900/30' : 'text-slate-400 bg-slate-100 dark:bg-slate-700 hover:text-red-500'"
              type="button"
            >
              <Heart class="w-5 h-5" :class="{ 'fill-current': isFavorited }" />
            </button>
          </div>

          <p class="text-sm leading-7 text-slate-600 dark:text-slate-300 flex-grow">
            {{ description }}
          </p>

          <div class="flex flex-wrap gap-2 mt-5">
            <span
              v-for="(tag, index) in tags"
              :key="index"
              class="px-3 py-1 rounded-md text-sm font-semibold"
              :class="getTagColor(tag)"
            >
              {{ tag }}
            </span>
          </div>

          <button
            @click.stop="goDetail"
            class="w-full mt-6 py-3 text-center bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white text-slate-700 dark:text-slate-200 font-bold rounded-xl transition-all"
            type="button"
          >
            查看詳情
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- 靜態卡片模式 (無翻轉) -->
  <div v-else class="static-card group" @click="emit('click')">
    <div class="flex flex-col h-full p-6 bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-[2rem] border border-white/20 dark:border-slate-700/50 shadow-xl hover:shadow-2xl hover:border-primary/20 transition-all duration-300 min-h-[320px]">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden group-hover:scale-105 transition-transform">
            <img :src="logoUrl" :alt="name" class="w-8 h-8 object-contain">
          </div>

          <div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 group-hover:text-primary transition-colors">
              {{ name }}
            </h3>
            <div class="flex gap-1 text-orange-400 items-center text-sm font-bold mt-1">
              <Star class="w-4 h-4 fill-current" />
              {{ rating }}
            </div>
          </div>
        </div>

        <button
          v-if="showFavoriteButton"
          @click.stop="emit('toggleFavorite')"
          class="p-2 rounded-full transition-colors"
          :class="isFavorited ? 'text-red-500 bg-red-50 dark:bg-red-900/30' : 'text-slate-400 bg-slate-100 dark:bg-slate-700 hover:text-red-500'"
          type="button"
        >
          <Heart class="w-5 h-5" :class="{ 'fill-current': isFavorited }" />
        </button>
      </div>

      <p class="text-sm leading-7 text-slate-600 dark:text-slate-300 flex-grow mb-6">
        {{ description }}
      </p>

      <div class="flex flex-wrap gap-2 mb-6">
        <span
          v-for="(tag, index) in tags"
          :key="index"
          class="px-3 py-1 rounded-md text-base font-semibold"
          :class="getTagColor(tag)"
        >
          {{ tag }}
        </span>
      </div>

      <button
        @click.stop="goDetail"
        class="w-full py-3 bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white text-slate-700 dark:text-slate-200 font-bold rounded-xl transition-all border-none cursor-pointer flex items-center justify-center gap-2 group/btn"
      >
        查看詳情
        <ExternalLink class="w-4 h-4 group-hover/btn:translate-x-0.5 transition-transform" />
      </button>
    </div>
  </div>
</template>

<style scoped>
/* 翻轉卡片樣式 */
.flip-card {
  height: 320px;
  perspective: 1200px;
}

.flip-card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
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
  box-shadow:
    0 10px 30px rgba(15, 23, 42, 0.08),
    0 0 0 1px rgba(226, 232, 240, 0.8);
  backdrop-filter: blur(16px);
}

.flip-card-front {
  background: rgba(255, 255, 255, 0.85);
}

.flip-card-back {
  background: rgba(255, 255, 255, 0.95);
  transform: rotateY(180deg);
}

:global(.dark) .flip-card-front {
  background: rgba(30, 41, 59, 0.9);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(71, 85, 105, 0.5);
}

:global(.dark) .flip-card-back {
  background: rgba(30, 41, 59, 0.98);
}

.flip-card:hover .flip-card-face {
  box-shadow:
    0 20px 50px rgba(15, 23, 42, 0.15),
    0 0 0 1px rgba(59, 130, 246, 0.1);
}

/* 靜態卡片樣式 */
.static-card {
  height: 100%;
  cursor: pointer;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>