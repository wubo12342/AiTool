<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import axios from 'axios'
import {
  Search, X, Star,
  ChevronRight, ChevronLeft, LayoutGrid,
  DollarSign, Tag, Sparkles
} from 'lucide-vue-next'
import ToolCard from './ToolCard.vue'
import { useFavorites } from '../composables/useFavorites.js'

const route = useRoute()
const { toggleFavorite, isFavorited } = useFavorites()

const tools      = ref([])
const categories = ref([])
const allTags    = ref([])
const loading    = ref(true)
const searchQuery   = ref('')
const currentSort   = ref('rating')

const currentPage = ref(1)
const pagination  = ref({ total_items: 0, total_pages: 1, current_page: 1 })

const selectedCategories = ref([])
const selectedPrices     = ref([])
const selectedTags       = ref([])
const minRating          = ref(0)

const priceOptions = [
  { label: '完全免費', value: 'Free' },
  { label: '部分免費 (Freemium)', value: 'Freemium' },
  { label: '付費', value: 'Paid' }
]

const ratingOptions = [
  { label: '不限', value: 0 },
  { label: '≥ 3.0', value: 3 },
  { label: '≥ 4.0', value: 4 },
  { label: '5.0', value: 5 }
]

// ── Active filter chips ─────────────────────────────────────
const activeFilterChips = computed(() => {
  const chips = []
  selectedCategories.value.forEach(id => {
    const cat = categories.value.find(c => c.id == id)
    if (cat) chips.push({ type: 'category', value: id, label: cat.name })
  })
  selectedPrices.value.forEach(p => {
    const opt = priceOptions.find(o => o.value === p)
    if (opt) chips.push({ type: 'price', value: p, label: opt.label })
  })
  selectedTags.value.forEach(id => {
    const tag = allTags.value.find(t => t.id == id)
    if (tag) chips.push({ type: 'tag', value: id, label: tag.name })
  })
  if (minRating.value > 0) {
    chips.push({ type: 'rating', value: minRating.value, label: `★ ${minRating.value}+ 以上` })
  }
  return chips
})

const hasActiveFilters = computed(() =>
  activeFilterChips.value.length > 0 || searchQuery.value !== ''
)

const removeChip = (chip) => {
  if (chip.type === 'category') {
    const i = selectedCategories.value.indexOf(chip.value)
    if (i >= 0) selectedCategories.value.splice(i, 1)
  } else if (chip.type === 'price') {
    const i = selectedPrices.value.indexOf(chip.value)
    if (i >= 0) selectedPrices.value.splice(i, 1)
  } else if (chip.type === 'tag') {
    const i = selectedTags.value.indexOf(chip.value)
    if (i >= 0) selectedTags.value.splice(i, 1)
  } else if (chip.type === 'rating') {
    minRating.value = 0
  }
}

// ── Smart pagination ────────────────────────────────────────
const visiblePages = computed(() => {
  const total   = pagination.value.total_pages
  const current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = [1]
  if (current > 3) pages.push('...')
  for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i)
  if (current < total - 2) pages.push('...')
  pages.push(total)
  return pages
})

// ── API calls ───────────────────────────────────────────────
const fetchCategories = async () => {
  try {
    const { data } = await axios.get('/api/get_categories.php')
    categories.value = data
  } catch {}
}

const fetchTags = async () => {
  try {
    const { data } = await axios.get('/api/get_tags.php')
    allTags.value = data
  } catch {}
}

const fetchTools = async (page = 1) => {
  loading.value   = true
  currentPage.value = page
  try {
    const params = new URLSearchParams()
    if (searchQuery.value)            params.append('q',          searchQuery.value)
    if (selectedCategories.value.length) params.append('categories', selectedCategories.value.join(','))
    if (selectedPrices.value.length)  params.append('prices',     selectedPrices.value.join(','))
    if (selectedTags.value.length)    params.append('tags',       selectedTags.value.join(','))
    if (minRating.value > 0)          params.append('rating',     minRating.value)
    params.append('sort',     currentSort.value)
    params.append('page',     page)
    params.append('per_page', 12)

    const { data } = await axios.get(`/api/search_tools.php?${params}`)
    if (data.tools) {
      tools.value      = data.tools
      pagination.value = data.pagination
    } else {
      tools.value      = data
      pagination.value = { total_items: data.length, total_pages: 1, current_page: 1 }
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const clearFilters = () => {
  selectedCategories.value = []
  selectedPrices.value     = []
  selectedTags.value       = []
  minRating.value          = 0
  searchQuery.value        = ''
  currentSort.value        = 'rating'
  fetchTools(1)
}

watch([selectedCategories, selectedPrices, selectedTags, minRating, currentSort], () => {
  fetchTools(1)
}, { deep: true })

let searchTimer = null
const handleSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => fetchTools(1), 300)
}

onMounted(async () => {
  const q = route.query.q
  if (typeof q === 'string' && q.trim()) searchQuery.value = q.trim()
  await Promise.all([fetchCategories(), fetchTags()])
  fetchTools(1)
})

watch(() => route.query.q, (q) => {
  if (typeof q === 'string') { searchQuery.value = q.trim(); fetchTools(1) }
})
</script>

<template>
  <div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- ══════════════ PAGE HEADER ══════════════ -->
      <div class="py-8 border-b border-slate-100 dark:border-slate-800">
        <nav class="flex items-center gap-1.5 text-sm text-slate-400 mb-5">
          <RouterLink to="/" class="hover:text-primary transition-colors no-underline">首頁</RouterLink>
          <ChevronRight class="w-3.5 h-3.5" />
          <span class="text-slate-600 dark:text-slate-300 font-medium">所有工具</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-5">
          <div>
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">AI 工具庫</h1>
            <p class="text-base text-slate-500 dark:text-slate-400 mt-2 font-medium">
              共收錄
              <span class="font-bold text-slate-800 dark:text-slate-200">{{ pagination.total_items }}</span>
              款精選工具
            </p>
          </div>

          <!-- Search + Sort -->
          <div class="flex items-center gap-3 flex-1 sm:max-w-lg">
            <div class="relative flex-1">
              <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
              <input
                v-model="searchQuery"
                @input="handleSearch"
                type="text"
                placeholder="搜尋工具名稱或功能..."
                class="w-full pl-10 pr-4 py-3 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
              >
            </div>
            <select
              v-model="currentSort"
              class="px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold outline-none focus:border-primary transition-all cursor-pointer whitespace-nowrap"
            >
              <option value="rating">評分最高</option>
              <option value="reviews">留言最多</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ══════════════ CONTENT ══════════════ -->
      <div class="py-8 flex flex-col lg:flex-row gap-8 items-start">

        <!-- ══ SIDEBAR（恆顯示）══ -->
        <aside class="w-full lg:w-64 xl:w-72 flex-shrink-0 lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto space-y-3 pb-2">

          <!-- Active filter chips -->
          <transition name="chips">
            <div v-if="hasActiveFilters" class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-800">
              <div class="flex items-center justify-between mb-2.5">
                <span class="text-sm font-bold text-blue-700 dark:text-blue-300">已套用篩選</span>
                <button @click="clearFilters" class="text-xs text-blue-500 hover:text-rose-500 font-semibold transition-colors">
                  全部清除
                </button>
              </div>
              <div class="flex flex-wrap gap-1.5">
                <button
                  v-if="searchQuery"
                  @click="searchQuery = ''; fetchTools(1)"
                  class="chip"
                >
                  <Search class="w-3 h-3" />
                  {{ searchQuery }}
                  <X class="w-3 h-3" />
                </button>
                <button
                  v-for="chip in activeFilterChips"
                  :key="`${chip.type}-${chip.value}`"
                  @click="removeChip(chip)"
                  class="chip"
                >
                  {{ chip.label }}
                  <X class="w-3 h-3" />
                </button>
              </div>
            </div>
          </transition>

          <!-- Category -->
          <div class="filter-card">
            <h3 class="filter-heading">
              <LayoutGrid class="w-4 h-4" /> 工具分類
            </h3>
            <div class="space-y-1">
              <label
                v-for="cat in categories"
                :key="cat.id"
                class="flex items-center gap-3 px-2 py-1.5 rounded-lg cursor-pointer group hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
              >
                <input type="checkbox" :value="cat.id" v-model="selectedCategories"
                  class="w-4 h-4 rounded accent-primary flex-shrink-0">
                <span class="text-base font-medium text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors">
                  {{ cat.name }}
                </span>
              </label>
            </div>
          </div>

          <!-- Price -->
          <div class="filter-card">
            <h3 class="filter-heading">
              <DollarSign class="w-4 h-4" /> 價格方案
            </h3>
            <div class="space-y-1">
              <label
                v-for="price in priceOptions"
                :key="price.value"
                class="flex items-center gap-3 px-2 py-1.5 rounded-lg cursor-pointer group hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
              >
                <input type="checkbox" :value="price.value" v-model="selectedPrices"
                  class="w-4 h-4 rounded accent-primary flex-shrink-0">
                <span class="text-base font-medium text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors">
                  {{ price.label }}
                </span>
              </label>
            </div>
          </div>

          <!-- Feature Tags -->
          <div v-if="allTags.length" class="filter-card">
            <h3 class="filter-heading">
              <Tag class="w-4 h-4" /> 功能特色
            </h3>
            <div class="space-y-1">
              <label
                v-for="tag in allTags"
                :key="tag.id"
                class="flex items-center gap-3 px-2 py-1.5 rounded-lg cursor-pointer group hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
              >
                <input type="checkbox" :value="tag.id" v-model="selectedTags"
                  class="w-4 h-4 rounded accent-primary flex-shrink-0">
                <span class="text-base font-medium text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors flex-1">
                  {{ tag.name }}
                </span>
                <span class="text-sm text-slate-400 dark:text-slate-600 font-medium">{{ tag.tool_count }}</span>
              </label>
            </div>
          </div>

          <!-- Rating -->
          <div class="filter-card">
            <h3 class="filter-heading">
              <Star class="w-4 h-4" /> 最低評分
            </h3>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="rate in ratingOptions"
                :key="rate.value"
                @click="minRating = rate.value"
                class="py-2.5 rounded-xl text-sm font-bold border transition-all"
                :class="minRating === rate.value
                  ? 'bg-primary text-white border-primary shadow-sm'
                  : 'bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-primary hover:text-primary'"
              >
                {{ rate.label }}
              </button>
            </div>
          </div>
        </aside>

        <!-- ══ MAIN GRID ══ -->
        <div class="flex-1 min-w-0">
          <!-- Loading skeleton -->
          <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div v-for="i in 9" :key="i" class="skeleton-card animate-pulse">
              <div class="h-full rounded-[2rem] bg-slate-200 dark:bg-slate-800 p-6 flex flex-col gap-4">
                <div class="flex justify-between">
                  <div class="w-14 h-14 rounded-2xl bg-slate-300 dark:bg-slate-700"></div>
                  <div class="w-16 h-7 rounded-full bg-slate-300 dark:bg-slate-700"></div>
                </div>
                <div class="h-6 rounded-lg bg-slate-300 dark:bg-slate-700 w-3/5"></div>
                <div class="h-4 rounded-lg bg-slate-300 dark:bg-slate-700 w-2/5"></div>
                <div class="flex gap-2 mt-auto">
                  <div class="h-6 w-16 rounded-lg bg-slate-300 dark:bg-slate-700"></div>
                  <div class="h-6 w-14 rounded-lg bg-slate-300 dark:bg-slate-700"></div>
                  <div class="h-6 w-10 rounded-lg bg-slate-300 dark:bg-slate-700"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty state -->
          <div v-else-if="tools.length === 0" class="py-24 flex flex-col items-center text-center">
            <div class="w-20 h-20 rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-6 shadow-inner">
              <Sparkles class="w-9 h-9 text-slate-300 dark:text-slate-600" />
            </div>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-200 mb-2">找不到相符的工具</h3>
            <p class="text-base text-slate-500 mb-8 max-w-xs">試試看更換關鍵字，或放寬篩選條件。</p>
            <button @click="clearFilters"
              class="px-8 py-3 bg-primary text-white rounded-xl font-bold hover:bg-blue-700 transition-all border-none cursor-pointer">
              重設所有條件
            </button>
          </div>

          <!-- Tool grid -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div
              v-for="(tool, index) in tools"
              :key="tool.id"
              class="card-enter"
              :style="`animation-delay: ${index * 45}ms`"
            >
              <ToolCard
                v-bind="tool"
                showFavoriteButton
                :isFavorited="isFavorited(tool.id)"
                @toggleFavorite="toggleFavorite(tool)"
              />
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="pagination.total_pages > 1" class="mt-12 flex justify-center items-center gap-1.5">
            <button @click="fetchTools(currentPage - 1)" :disabled="currentPage === 1" class="page-btn">
              <ChevronLeft class="w-4 h-4" />
            </button>
            <template v-for="p in visiblePages" :key="p">
              <span v-if="p === '...'" class="w-10 h-10 flex items-center justify-center text-slate-400 select-none">···</span>
              <button
                v-else
                @click="fetchTools(p)"
                class="w-10 h-10 rounded-xl text-sm font-bold transition-all border"
                :class="currentPage === p
                  ? 'bg-primary text-white border-primary shadow-md shadow-primary/25'
                  : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 hover:border-primary hover:text-primary'"
              >{{ p }}</button>
            </template>
            <button @click="fetchTools(currentPage + 1)" :disabled="currentPage === pagination.total_pages" class="page-btn">
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
@reference "../style.css";

.filter-card {
  @apply bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm;
}

.filter-heading {
  @apply flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-200 mb-3;
}

.chip {
  @apply inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-900/60 transition-all;
}

.chips-enter-active,
.chips-leave-active { transition: opacity 0.2s, transform 0.2s; }
.chips-enter-from,
.chips-leave-to { opacity: 0; transform: translateY(-4px); }

.card-enter {
  animation: card-in 0.4s ease-out both;
}

@keyframes card-in {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

.skeleton-card { height: 320px; }

.page-btn {
  @apply w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:border-primary hover:text-primary disabled:opacity-30 disabled:cursor-not-allowed transition-all;
}

/* ── Pagination button ── */
.page-btn {
  @apply w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:border-primary hover:text-primary disabled:opacity-30 disabled:cursor-not-allowed transition-all;
}

/* ── Mobile section title ── */
.mob-section-title {
  @apply text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3;
}

/* ── Mobile filter chips ── */
.mob-chip {
  @apply px-4 py-2 rounded-xl text-sm font-semibold border transition-all cursor-pointer;
}
.mob-chip-on  { @apply bg-primary text-white border-primary; }
.mob-chip-off { @apply bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700; }
</style>
