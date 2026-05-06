<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import { 
  Search, 
  Filter, 
  Star, 
  LayoutGrid, 
  Loader2,
  X,
  ChevronDown,
  Check
} from 'lucide-vue-next'
import ToolCard from './ToolCard.vue'
import NeuralTechBackground from './NeuralTechBackground.vue'
import { useFavorites } from '../composables/useFavorites.js'

const { toggleFavorite, isFavorited } = useFavorites()

// 狀態管理
const tools = ref([])
const categories = ref([])
const loading = ref(true)
const searchQuery = ref('')
const currentSort = ref('rating') // 預設依評分高低排序

// 分頁狀態
const currentPage = ref(1)
const pagination = ref({
  total_items: 0,
  total_pages: 1,
  current_page: 1
})

// 篩選條件
const selectedCategories = ref([])
const selectedPrices = ref([])
const minRating = ref(0)

// 靜態選項
const priceOptions = [
  { label: '完全免費', value: 'Free' },
  { label: '部分免費 (Freemium)', value: 'Freemium' },
  { label: '付費軟體', value: 'Paid' }
]

const ratingOptions = [
  { label: '不限評分', value: 0 },
  { label: '5.0 (滿分)', value: 5 },
  { label: '高於 4.0 顆星', value: 4 },
  { label: '高於 3.0 顆星', value: 3 }
]

// 獲取分類清單
const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/get_categories.php')
    categories.value = response.data
  } catch (err) {
    console.error('獲取分類失敗:', err)
  }
}

// 獲取工具清單 (含搜尋、篩選、排序與分頁)
const fetchTools = async (page = 1) => {
  loading.value = true
  currentPage.value = page
  try {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('q', searchQuery.value)
    if (selectedCategories.value.length > 0) params.append('categories', selectedCategories.value.join(','))
    if (selectedPrices.value.length > 0) params.append('prices', selectedPrices.value.join(','))
    if (minRating.value > 0) params.append('rating', minRating.value)
    params.append('sort', currentSort.value)
    params.append('page', page)
    params.append('per_page', 12)

    const response = await axios.get(`/api/search_tools.php?${params.toString()}`)
    
    // 處理分頁回傳格式
    if (response.data.tools) {
      tools.value = response.data.tools
      pagination.value = response.data.pagination
    } else {
      tools.value = response.data
      pagination.value = { total_items: tools.value.length, total_pages: 1, current_page: 1 }
    }
  } catch (err) {
    console.error('獲取工具失敗:', err)
  } finally {
    loading.value = false
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

// 監聽所有篩選與排序條件 (條件改變時回到第一頁)
watch([selectedCategories, selectedPrices, minRating, currentSort], () => {
  fetchTools(1)
}, { deep: true })

// 處理搜尋 (打字即搜尋)
let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchTools(1)
  }, 300)
}

const clearFilters = () => {
  selectedCategories.value = []
  selectedPrices.value = []
  minRating.value = 0
  searchQuery.value = ''
  currentSort.value = 'rating'
  fetchTools(1)
}

onMounted(() => {
  fetchCategories()
  fetchTools(1)
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 pt-24 pb-20 relative overflow-hidden">
    <NeuralTechBackground />
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- 頂部搜尋欄 -->
      <div class="mb-12">
        <h1 class="text-4xl font-extrabold text-slate-900 mb-8 text-center">探索所有 AI 工具</h1>
        
        <div class="max-w-5xl mx-auto relative group">
          <div class="absolute inset-0 bg-primary/20 blur-2xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          <div class="relative flex items-center bg-white rounded-2xl shadow-xl p-2 border border-slate-100">
            <Search class="w-6 h-6 text-slate-400 ml-4" />
            <input 
              v-model="searchQuery"
              @input="handleSearch"
              type="text" 
              placeholder="輸入名稱、功能或關鍵字搜尋..." 
              class="w-full pl-4 pr-4 py-4 text-lg border-none focus:ring-0 outline-none"
            >
            <button 
              @click="fetchTools"
              class="px-16 py-4 bg-primary text-white rounded-xl font-bold hover:shadow-lg transition-all border-none cursor-pointer flex items-center justify-center whitespace-nowrap min-w-[140px]"
            >
              搜尋
            </button>
          </div>
        </div>
      </div>

      <div class="flex flex-col lg:flex-row gap-10">
        <!-- 左側篩選側邊欄 -->
        <aside class="lg:w-72 flex-shrink-0">
          <div class="sticky top-28 bg-white/80 backdrop-blur-xl rounded-3xl p-8 border border-white shadow-xl">
            <div class="flex items-center justify-between mb-8">
              <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <Filter class="w-5 h-5 text-primary" />
                篩選條件
              </h2>
              <button 
                @click="clearFilters"
                class="text-xs text-slate-500 hover:text-primary transition-colors bg-transparent border-none cursor-pointer"
              >
                重設
              </button>
            </div>

            <!-- 工具分類 -->
            <div class="mb-8">
              <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">工具分類</h3>
              <div class="flex flex-wrap gap-3">
                <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2 group cursor-pointer bg-slate-50 px-3 py-2 rounded-lg hover:bg-primary/5 transition-colors border border-transparent peer-checked:border-primary">
                  <div class="relative flex items-center">
                    <input 
                      type="checkbox" 
                      :value="cat.id" 
                      v-model="selectedCategories"
                      class="peer appearance-none w-4 h-4 border-2 border-slate-200 rounded-md checked:bg-primary checked:border-primary transition-all cursor-pointer"
                    >
                    <Check class="absolute w-3 h-3 text-white opacity-0 peer-checked:opacity-100 left-0.5 pointer-events-none transition-opacity" />
                  </div>
                  <span class="text-sm text-slate-600 group-hover:text-primary transition-colors">{{ cat.name }}</span>
                </label>
              </div>
            </div>

            <!-- 價格方案 -->
            <div class="mb-8">
              <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">價格方案</h3>
              <div class="flex flex-wrap gap-3">
                <label v-for="price in priceOptions" :key="price.value" class="flex items-center gap-2 group cursor-pointer bg-slate-50 px-3 py-2 rounded-lg hover:bg-primary/5 transition-colors">
                  <div class="relative flex items-center">
                    <input 
                      type="checkbox" 
                      :value="price.value" 
                      v-model="selectedPrices"
                      class="peer appearance-none w-4 h-4 border-2 border-slate-200 rounded-md checked:bg-primary checked:border-primary transition-all cursor-pointer"
                    >
                    <Check class="absolute w-3 h-3 text-white opacity-0 peer-checked:opacity-100 left-0.5 pointer-events-none transition-opacity" />
                  </div>
                  <span class="text-sm text-slate-600 group-hover:text-primary transition-colors">{{ price.label }}</span>
                </label>
              </div>
            </div>

            <!-- 使用者評分 -->
            <div>
              <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">最低評分</h3>
              <div class="relative">
                <select 
                  v-model="minRating"
                  class="w-full bg-slate-50 border-2 border-transparent focus:border-primary/20 rounded-xl px-4 py-3 text-slate-600 font-medium outline-none transition-all appearance-none cursor-pointer"
                >
                  <option v-for="rate in ratingOptions" :key="rate.value" :value="rate.value">
                    {{ rate.label }}
                  </option>
                </select>
                <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
              </div>
            </div>
          </div>
        </aside>

        <!-- 右側工具列表 -->
        <main class="flex-1">
          <div class="mb-6 flex items-center justify-between">
            <div class="text-slate-500 font-medium">
              共找到 <span class="text-slate-900 font-bold">{{ pagination.total_items }}</span> 個工具
            </div>
            
            <div class="flex items-center gap-2 text-sm text-slate-500">
              排序方式：
              <select v-model="currentSort" class="bg-transparent border-none font-bold text-slate-900 outline-none cursor-pointer">
                <option value="rating">評分高低</option>
                <option value="reviews">留言數量</option>
              </select>
            </div>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="py-32 text-center">
            <Loader2 class="w-12 h-12 text-primary animate-spin mx-auto mb-4" />
            <p class="text-slate-500 font-medium">正在為您篩選最佳工具...</p>
          </div>

          <!-- Empty -->
          <div v-else-if="tools.length === 0" class="py-32 text-center bg-white/50 backdrop-blur-sm rounded-[3rem] border-2 border-dashed border-slate-200">
            <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <Search class="w-10 h-10 text-slate-300" />
            </div>
            <h3 class="text-2xl font-bold text-slate-900 mb-2">找不到符合條件的工具</h3>
            <p class="text-slate-500 mb-8">試試看更換關鍵字或放寬篩選條件。</p>
            <button @click="clearFilters" class="px-8 py-3 bg-slate-900 text-white rounded-xl font-bold border-none cursor-pointer hover:bg-primary transition-all">
              重設所有條件
            </button>
          </div>

          <!-- Grid -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8">
            <ToolCard
              v-for="tool in tools"
              :key="tool.id"
              v-bind="tool"
              showFavoriteButton
              :isFavorited="isFavorited(tool.id)"
              @toggleFavorite="toggleFavorite(tool)"
            />
          </div>

          <!-- 分頁導覽 -->
          <div v-if="pagination.total_pages > 1" class="mt-16 flex justify-center items-center gap-2">
            <button 
              @click="fetchTools(currentPage - 1)"
              :disabled="currentPage === 1"
              class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400 hover:text-primary hover:border-primary disabled:opacity-30 disabled:cursor-not-allowed transition-all"
            >
              <ChevronDown class="w-5 h-5 rotate-90" />
            </button>

            <button 
              v-for="p in pagination.total_pages" 
              :key="p"
              @click="fetchTools(p)"
              class="w-10 h-10 flex items-center justify-center rounded-xl font-bold transition-all"
              :class="currentPage === p ? 'bg-primary text-white shadow-lg' : 'bg-white border border-slate-200 text-slate-600 hover:border-primary hover:text-primary'"
            >
              {{ p }}
            </button>

            <button 
              @click="fetchTools(currentPage + 1)"
              :disabled="currentPage === pagination.total_pages"
              class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400 hover:text-primary hover:border-primary disabled:opacity-30 disabled:cursor-not-allowed transition-all"
            >
              <ChevronDown class="w-5 h-5 -rotate-90" />
            </button>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 自定義 Checkbox 勾選圖示，因為前面沒 import Check，我們可以直接用 lucide 組件 */
</style>
