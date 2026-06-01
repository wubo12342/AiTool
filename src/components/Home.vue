<script setup>
import { RouterLink } from 'vue-router'
import {
  Search,
  Zap,
  ArrowLeftRight,
  Bookmark,
  ChevronRight,
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
} from 'lucide-vue-next'
import { onMounted, ref } from 'vue'
import axios from 'axios'
import ToolCard from './ToolCard.vue'
import { useFavorites } from '../composables/useFavorites.js'

const { toggleFavorite, isFavorited } = useFavorites()

const popularTools = ref([])
const isLoading = ref(true)
const apiError = ref('')
const searchQuery = ref('')
const currentCid = ref('')
const currentSort = ref('rating') // 預設依評分高低
const sectionTitle = ref('本週熱門工具')

const fetchTools = async (keyword = '', cid = '', sort = 'rating') => {
  try {
    isLoading.value = true
    const url = `/api/get_tools.php?keyword=${encodeURIComponent(keyword)}&cid=${cid}&sort=${sort}`;
    const response = await axios.get(url);
    
    const toolList = response.data.tools || response.data;
    
    if (Array.isArray(toolList)) {
      popularTools.value = toolList
      apiError.value = ''
      
      if (keyword) {
        sectionTitle.value = `搜尋結果："${keyword}"`
      } else if (cid) {
        const categories = { '1': '文字生成', '2': '圖像生成', '3': '影片製作', '4': '程式開發', '5': '語音生成' }
        sectionTitle.value = `${categories[cid] || '分類'}工具`
      } else {
        sectionTitle.value = '本週熱門工具'
      }
    } else {
      throw new Error(response.data.error || '資料格式錯誤')
    }
  } catch (error) {
    console.error('API 請求失敗:', error)
    apiError.value = `API 請求失敗: ${error.message}`
    popularTools.value = []
  } finally {
    isLoading.value = false
  }
}

const handleSearch = () => {
  currentCid.value = ''
  fetchTools(searchQuery.value, '', currentSort.value)
}

const filterByCategory = (cid) => {
  searchQuery.value = ''
  currentCid.value = cid
  fetchTools('', cid, currentSort.value)
  document.getElementById('tools-section').scrollIntoView({ behavior: 'smooth' })
}

const handleSortChange = () => {
  fetchTools(searchQuery.value, currentCid.value, currentSort.value)
}

onMounted(() => {
  fetchTools()
})
</script>

<template>
  <div class="relative overflow-hidden animate-in fade-in duration-500 text-slate-900 dark:text-slate-100">
    <div class="relative z-10">
      <!-- Hero Section -->
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

            <input
              v-model="searchQuery"
              type="text"
              placeholder="輸入關鍵字立即搜尋工具..."
              class="w-full pl-14 pr-32 py-5 bg-white text-slate-900 rounded-2xl shadow-2xl outline-none focus:ring-4 focus:ring-primary/20 transition-all text-lg border-none"
              @input="handleSearch"
              @keyup.enter="handleSearch"
            >

            <button 
              @click="handleSearch"
              class="absolute inset-y-2 right-2 px-8 bg-primary hover:bg-primary/90 text-white rounded-xl font-bold transition-all flex items-center justify-center whitespace-nowrap min-w-[100px] border-none cursor-pointer"
            >
              搜尋
            </button>
          </div>

          <!-- Trending Tags -->
          <div class="mt-6 flex flex-wrap justify-center gap-3 text-sm text-slate-400">
            <span>熱門關鍵字：</span>
            <RouterLink to="/search?q=ChatGPT" class="text-slate-400 hover:text-white transition-colors no-underline">#ChatGPT</RouterLink>
            <RouterLink to="/search?q=Midjourney" class="text-slate-400 hover:text-white transition-colors no-underline">#Midjourney</RouterLink>
            <RouterLink to="/search?q=效率提升" class="text-slate-400 hover:text-white transition-colors no-underline">#效率提升</RouterLink>
            <RouterLink to="/search?q=影片自動生成" class="text-slate-400 hover:text-white transition-colors no-underline">#影片自動生成</RouterLink>
          </div>
        </div>
      </section>

      <!-- Stats / Features Quick Look -->
      <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur p-6 rounded-2xl shadow-xl flex items-center gap-4 hover:-translate-y-1 transition-transform border border-slate-100 dark:border-slate-700">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-primary flex-shrink-0">
              <Zap class="w-6 h-6" />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-slate-100 text-lg">快速發現</h3>
              <p class="text-slate-500 dark:text-slate-400 text-sm">每日更新最前衛的 AI 應用。</p>
            </div>
          </div>

          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur p-6 rounded-2xl shadow-xl flex items-center gap-4 hover:-translate-y-1 transition-transform border border-slate-100 dark:border-slate-700">
            <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center text-cta flex-shrink-0">
              <ArrowLeftRight class="w-6 h-6" />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-slate-100 text-lg">深度比較</h3>
              <p class="text-slate-500 dark:text-slate-400 text-sm">直觀的功能與價格對比。</p>
            </div>
          </div>

          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur p-6 rounded-2xl shadow-xl flex items-center gap-4 hover:-translate-y-1 transition-transform border border-slate-100 dark:border-slate-700">
            <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center text-orange-500 flex-shrink-0">
              <Bookmark class="w-6 h-6" />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-slate-100 text-lg">個人收藏</h3>
              <p class="text-slate-500 dark:text-slate-400 text-sm">打造您的專屬 AI 工具箱。</p>
            </div>
          </div>
        </div>
      </section>


      <!-- Category Entry Section -->
      <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 border-t border-slate-200/50 dark:border-slate-700/50">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-100">按分類探索</h2>
          <p class="text-slate-500 dark:text-slate-400 mt-4">從文本生成到影片剪輯，我們已為您將 AI 精確分類。</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
          <button @click="filterByCategory('1')" class="glass-card dark:bg-slate-800/50 dark:border-slate-700 p-8 rounded-2xl hover:shadow-xl hover:bg-white dark:hover:bg-slate-700 hover:text-primary transition-all text-center group no-underline border-none cursor-pointer w-full">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-primary mx-auto mb-4 group-hover:scale-110 transition-transform">
              <Type class="w-8 h-8" />
            </div>
            <span class="font-bold text-slate-900 dark:text-slate-200 group-hover:text-primary">文字生成</span>
          </button>

          <button @click="filterByCategory('2')" class="glass-card dark:bg-slate-800/50 dark:border-slate-700 p-8 rounded-2xl hover:shadow-xl hover:bg-white dark:hover:bg-slate-700 hover:text-primary transition-all text-center group no-underline border-none cursor-pointer w-full">
            <div class="w-16 h-16 rounded-2xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
              <Palette class="w-8 h-8" />
            </div>
            <span class="font-bold text-slate-900 dark:text-slate-200 group-hover:text-primary">圖像生成</span>
          </button>

          <button @click="filterByCategory('3')" class="glass-card dark:bg-slate-800/50 dark:border-slate-700 p-8 rounded-2xl hover:shadow-xl hover:bg-white dark:hover:bg-slate-700 hover:text-primary transition-all text-center group no-underline border-none cursor-pointer w-full">
            <div class="w-16 h-16 rounded-2xl bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
              <Video class="w-8 h-8" />
            </div>
            <span class="font-bold text-slate-900 dark:text-slate-200 group-hover:text-primary">影片製作</span>
          </button>

          <button @click="filterByCategory('4')" class="glass-card dark:bg-slate-800/50 dark:border-slate-700 p-8 rounded-2xl hover:shadow-xl hover:bg-white dark:hover:bg-slate-700 hover:text-primary transition-all text-center group no-underline border-none cursor-pointer w-full">
            <div class="w-16 h-16 rounded-2xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center text-green-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
              <Code class="w-8 h-8" />
            </div>
            <span class="font-bold text-slate-900 dark:text-slate-200 group-hover:text-primary">程式開發</span>
          </button>

          <button @click="filterByCategory('5')" class="glass-card dark:bg-slate-800/50 dark:border-slate-700 p-8 rounded-2xl hover:shadow-xl hover:bg-white dark:hover:bg-slate-700 hover:text-primary transition-all text-center group no-underline border-none cursor-pointer w-full">
            <div class="w-16 h-16 rounded-2xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center text-red-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
              <Music class="w-8 h-8" />
            </div>
            <span class="font-bold text-slate-900 dark:text-slate-200 group-hover:text-primary">語音生成</span>
          </button>

          <RouterLink to="/tools" class="glass-card dark:bg-slate-800/50 dark:border-slate-700 p-8 rounded-2xl hover:shadow-xl hover:bg-white dark:hover:bg-slate-700 hover:text-primary transition-all text-center group no-underline">
            <div class="w-16 h-16 rounded-2xl bg-yellow-50 dark:bg-yellow-900/30 flex items-center justify-center text-yellow-600 mx-auto mb-4 group-hover:scale-110 transition-transform">
              <Layout class="w-8 h-8" />
            </div>
            <span class="font-bold text-slate-900 dark:text-slate-200 group-hover:text-primary">更多工具</span>
          </RouterLink>
        </div>
      </section>

    </div>
  </div>
</template>