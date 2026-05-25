<script setup>
import { ref, onMounted, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import axios from 'axios'
import {
  Search,
  Grid,
  DollarSign,
  Layers,
  Loader2,
  ChevronRight,
  ChevronLeft
} from 'lucide-vue-next'
import ToolCard from './ToolCard.vue'
import { useFavorites } from '../composables/useFavorites.js'

const route = useRoute()
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
  // H4 — 支援從 /search?q=xxx 或 /tools?q=xxx 帶搜尋詞進來
  const initialQuery = route.query.q
  if (typeof initialQuery === 'string' && initialQuery.trim() !== '') {
    searchQuery.value = initialQuery.trim()
  }
  fetchCategories()
  fetchTools(1)
})

// 路由 query 變動時（例如點不同熱門詞而沒離開頁面）也跟著更新
watch(() => route.query.q, (q) => {
  if (typeof q === 'string') {
    searchQuery.value = q.trim()
    fetchTools(1)
  }
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Breadcrumb & Title -->
    <div class="mb-10">
      <nav class="flex text-slate-400 text-sm mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
          <li class="inline-flex items-center">
            <RouterLink to="/" class="hover:text-primary no-underline">首頁</RouterLink>
          </li>
          <li>
            <div class="flex items-center">
              <ChevronRight class="w-4 h-4" />
              <span class="ml-1 md:ml-2">所有工具</span>
            </div>
          </li>
        </ol>
      </nav>
      <h1 class="text-3xl md:text-4xl font-bold text-slate-900">探索所有 AI 工具</h1>
    </div>

    <div class="flex flex-col lg:flex-row gap-12">
      <!-- Sidebar Filters -->
      <aside class="w-full lg:w-72 flex-shrink-0">
        <div class="sticky top-24 space-y-8">
          <!-- Search within results -->
          <div>
            <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
              <Search class="w-5 h-5 text-primary" /> 關鍵字搜尋
            </h3>
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              placeholder="例如：寫作助理..."
              class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-primary transition-all"
            >
          </div>

          <!-- Category Filter -->
          <div>
            <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
              <Grid class="w-5 h-5 text-primary" /> 工具分類
            </h3>
            <div class="space-y-3">
              <label
                v-for="cat in categories"
                :key="cat.id"
                class="flex items-center gap-3 cursor-pointer group"
              >
                <input
                  type="checkbox"
                  :value="cat.id"
                  v-model="selectedCategories"
                  class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary"
                >
                <span class="text-slate-600 group-hover:text-primary transition-colors">{{ cat.name }}</span>
              </label>
            </div>
          </div>

          <!-- Price Type Filter -->
          <div>
            <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
              <DollarSign class="w-5 h-5 text-primary" /> 價格方案
            </h3>
            <div class="space-y-3">
              <label
                v-for="price in priceOptions"
                :key="price.value"
                class="flex items-center gap-3 cursor-pointer group"
              >
                <input
                  type="checkbox"
                  :value="price.value"
                  v-model="selectedPrices"
                  class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary"
                >
                <span class="text-slate-600 group-hover:text-primary transition-colors">{{ price.label }}</span>
              </label>
            </div>
          </div>

          <!-- Minimum Rating Filter -->
          <div>
            <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
              <Layers class="w-5 h-5 text-primary" /> 最低評分
            </h3>
            <select
              v-model="minRating"
              class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-primary transition-all bg-white"
            >
              <option v-for="rate in ratingOptions" :key="rate.value" :value="rate.value">
                {{ rate.label }}
              </option>
            </select>
          </div>

          <button
            @click="fetchTools(1)"
            class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-all border-none cursor-pointer"
          >
            套用篩選
          </button>
        </div>
      </aside>

      <!-- Tool Grid & Sorting -->
      <div class="flex-1">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
          <span class="text-slate-500 font-medium">
            找到 <span class="text-slate-900 font-bold">{{ pagination.total_items }}</span> 個符合條件的工具
          </span>
          <div class="flex items-center gap-3">
            <span class="text-slate-500 text-sm whitespace-nowrap">排序方式：</span>
            <select
              v-model="currentSort"
              class="px-4 py-2 rounded-lg border border-slate-200 outline-none focus:border-primary bg-white font-medium"
            >
              <option value="rating">評分最高</option>
              <option value="reviews">留言最多</option>
            </select>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="py-32 text-center">
          <Loader2 class="w-12 h-12 text-primary animate-spin mx-auto mb-4" />
          <p class="text-slate-500 font-medium">正在為您篩選最佳工具...</p>
        </div>

        <!-- Empty -->
        <div v-else-if="tools.length === 0" class="py-32 text-center bg-white/50 rounded-[3rem] border-2 border-dashed border-slate-200">
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
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <ToolCard
            v-for="tool in tools"
            :key="tool.id"
            v-bind="tool"
            showFavoriteButton
            :isFavorited="isFavorited(tool.id)"
            @toggleFavorite="toggleFavorite(tool)"
          />
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total_pages > 1" class="mt-16 flex justify-center gap-2">
          <button
            @click="fetchTools(currentPage - 1)"
            :disabled="currentPage === 1"
            class="w-12 h-12 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:border-primary hover:text-primary disabled:opacity-30 disabled:cursor-not-allowed transition-all bg-white"
          >
            <ChevronLeft class="w-5 h-5" />
          </button>

          <button
            v-for="p in pagination.total_pages"
            :key="p"
            @click="fetchTools(p)"
            class="w-12 h-12 rounded-xl flex items-center justify-center font-bold transition-all"
            :class="currentPage === p ? 'bg-primary text-white' : 'border border-slate-200 bg-white text-slate-500 hover:border-primary hover:text-primary'"
          >
            {{ p }}
          </button>

          <button
            @click="fetchTools(currentPage + 1)"
            :disabled="currentPage === pagination.total_pages"
            class="w-12 h-12 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:border-primary hover:text-primary disabled:opacity-30 disabled:cursor-not-allowed transition-all bg-white"
          >
            <ChevronRight class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
