<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import axios from 'axios'
import { 
  ChevronRight, 
  ArrowLeft, 
  LayoutGrid, 
  Search,
  Filter,
  Loader2
} from 'lucide-vue-next'
import ToolCard from './ToolCard.vue'
import { useFavorites } from '../composables/useFavorites.js'

const route = useRoute()
const { toggleFavorite, isFavorited } = useFavorites()

const loading = ref(true)
const error = ref('')
const category = ref(null)
const tools = ref([])
const searchQuery = ref('')

const filteredTools = computed(() => {
  if (!searchQuery.value) return tools.value
  const q = searchQuery.value.toLowerCase()
  return tools.value.filter(tool => 
    tool.name.toLowerCase().includes(q) || 
    tool.description.toLowerCase().includes(q)
  )
})

const fetchCategoryData = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await axios.get(`/api/get_category_tools.php?cid=${route.params.id}`)
    category.value = response.data.category
    tools.value = response.data.tools
  } catch (err) {
    console.error('獲取分類資料失敗:', err)
    error.value = '無法載入分類資料，請稍後再試。'
  } finally {
    loading.value = false
  }
}

onMounted(fetchCategoryData)

// 當路由參數改變時（例如從一個分類跳到另一個分類），重新抓取資料
watch(() => route.params.id, fetchCategoryData)
</script>

<template>
  <div class="min-h-screen pb-20 animate-in fade-in duration-500 relative overflow-hidden dark:bg-slate-900">
    <div class="relative z-8">
      <!-- Header Section -->
      <header class="pt-8 pb-8 relative overflow-hidden">
      
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <RouterLink 
          to="/" 
          class="inline-flex items-center gap-2 text-slate-500 dark:text-slate-400 hover:text-primary transition-colors mb-8 no-underline text-sm font-medium"
        >
          <ArrowLeft class="w-4 h-4" />
          返回首頁
        </RouterLink>

        <div v-if="category" class="flex flex-col md:flex-row md:items-end justify-between gap-6">
          <div>
            <div class="flex items-center gap-3 text-primary mb-4">
              <LayoutGrid class="w-6 h-6" />
              <span class="font-bold tracking-widest uppercase text-sm">分類探索</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-slate-900 dark:text-slate-100">{{ category.name }}</h1>
            <p class="text-slate-500 dark:text-slate-400 max-w-2xl text-lg">
              {{ category.description || `探索最優質的 ${category.name} AI 工具，提升您的工作與創作效率。` }}
            </p>
          </div>

          <div class="text-slate-500 dark:text-slate-400 text-sm font-medium">
            共找到 <span class="text-primary text-lg font-bold">{{ tools.length }}</span> 個工具
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20">
      <!-- Toolbar -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-2 mb-12 border border-slate-100 dark:border-slate-700 flex items-center gap-2">
        <div class="relative flex-1">
          <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="在分類中搜尋工具..."
            class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border-none rounded-xl focus:ring-2 focus:ring-primary/20 outline-none transition-all"
          >
        </div>
        <button class="px-15 py-4 bg-primary text-white rounded-xl font-bold hover:shadow-lg transition-all border-none cursor-pointer whitespace-nowrap">
          搜尋
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="py-32 text-center">
        <Loader2 class="w-12 h-12 text-primary animate-spin mx-auto mb-4" />
        <p class="text-slate-500 font-medium">正在為您整理工具...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="py-32 text-center bg-white dark:bg-slate-800 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700">
        <p class="text-red-500 font-bold mb-4">{{ error }}</p>
        <button @click="fetchCategoryData" class="px-8 py-3 bg-primary text-white rounded-xl font-bold border-none cursor-pointer">
          重新載入
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="tools.length === 0" class="py-32 text-center bg-white dark:bg-slate-800 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700">
        <div class="w-20 h-20 bg-slate-50 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-6">
          <LayoutGrid class="w-10 h-10 text-slate-300 dark:text-slate-500" />
        </div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">此分類暫無工具</h3>
        <p class="text-slate-500 dark:text-slate-400">我們正在持續收錄更多優秀的 AI 工具，敬請期待。</p>
      </div>

      <!-- Tool Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <ToolCard
          v-for="tool in filteredTools"
          :key="tool.id"
          v-bind="tool"
          showFavoriteButton
          :isFavorited="isFavorited(tool.id)"
          @toggleFavorite="toggleFavorite(tool)"
        />
      </div>
    </main>
  </div>
</div>
</template>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(10px);
}
</style>
