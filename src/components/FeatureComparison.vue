<script setup>
import { ref, reactive, watch, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Check, Search, ArrowLeftRight, Star, ExternalLink, Trash2, Heart, ChevronDown, ChevronUp, Copy, RotateCcw, Play, X, Trophy, Loader2, ArrowLeft, LayoutGrid } from 'lucide-vue-next'
import axios from 'axios'
import { useFavorites } from '../composables/useFavorites.js'

const route = useRoute()
const router = useRouter()
const { toggleFavorite, isFavorited } = useFavorites()

const searchResults = ref([])
const categories = ref([])
const categoryTools = ref([])
const isFetchingCategory = ref(false)
const activeSearchIndex = ref(null)
// slot 0 的兩步驟選擇：'category'（選類別）或 'tool'（選工具）
const searchStep = ref('category')
const pickedCategory = ref(null)   // slot 0 在第一步選好的類別
const selectedTools = ref([null, null])
const isSearching = ref(false)
const searchQuery = ref('')
const expandedPlans = reactive([false, false])
const showVideoModal = ref(false)
const videoUrl = ref('')
const copySuccess = ref(false)

let debounceTimer = null

// 只有搜尋 slot 1 時，才以 slot 0 作為分類鎖定依據
const lockedTool = computed(() => {
  if (activeSearchIndex.value !== 1) return null
  return selectedTools.value[0] ?? null
})

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/get_categories.php')
    categories.value = response.data || []
  } catch (error) {
    console.error('Fetch categories failed:', error)
  }
}

const searchTools = async (query) => {
  if (!query) {
    searchResults.value = []
    return
  }
  try {
    isSearching.value = true
    const cid = lockedTool.value?.cid ?? pickedCategory.value?.id
    const cidParam = cid ? `&cid=${cid}` : ''
    const response = await axios.get(`/api/get_tools.php?keyword=${encodeURIComponent(query)}${cidParam}`)
    searchResults.value = response.data.tools || []
  } catch (error) {
    console.error('Search failed:', error)
  } finally {
    isSearching.value = false
  }
}

// slot 0 第一步：選類別後，進入第二步顯示該類別工具
const pickCategory = async (cat) => {
  pickedCategory.value = cat
  searchStep.value = 'tool'
  isFetchingCategory.value = true
  categoryTools.value = []
  searchQuery.value = ''
  searchResults.value = []
  try {
    const response = await axios.get(`/api/get_tools.php?cid=${cat.id}&sort=rating`)
    categoryTools.value = response.data.tools || []
  } catch (e) {
    console.error('Fetch category tools failed:', e)
  } finally {
    isFetchingCategory.value = false
  }
}

const selectTool = async (tool, index) => {
  try {
    const response = await axios.get(`/api/tool.php?id=${tool.id}`)
    const newTool = response.data

    // 若替換 slot 0 且新工具與 slot 1 類別不同，清除 slot 1
    if (index === 0 && selectedTools.value[1]?.cid && newTool.cid !== selectedTools.value[1].cid) {
      selectedTools.value[1] = null
      expandedPlans[1] = false
    }

    selectedTools.value[index] = newTool
    activeSearchIndex.value = null
    searchQuery.value = ''
    searchResults.value = []
    pickedCategory.value = null
    expandedPlans[index] = false
    syncUrl()
  } catch (error) {
    console.error('Fetch tool detail failed:', error)
  }
}

const removeTool = (index) => {
  selectedTools.value[index] = null
  expandedPlans[index] = false
  syncUrl()
}

const openSearch = (index) => {
  searchQuery.value = ''
  searchResults.value = []
  categoryTools.value = []
  pickedCategory.value = null
  if (index === 0) {
    searchStep.value = 'category'   // slot 0 從選類別開始
  } else {
    searchStep.value = 'tool'       // slot 1 直接進工具列表
  }
  activeSearchIndex.value = index
}

// slot 1 開啟時，自動抓取 slot 0 的同類別工具
watch(activeSearchIndex, async (newIndex) => {
  if (newIndex !== 1) return
  const anchor = selectedTools.value[0]
  if (!anchor?.cid) return

  isFetchingCategory.value = true
  categoryTools.value = []
  try {
    const response = await axios.get(`/api/get_tools.php?cid=${anchor.cid}&sort=rating`)
    categoryTools.value = response.data.tools || []
  } catch (e) {
    console.error('Fetch category tools failed:', e)
  } finally {
    isFetchingCategory.value = false
  }
})

const resetAll = () => {
  selectedTools.value = [null, null]
  expandedPlans[0] = false
  expandedPlans[1] = false
  router.replace({ query: {} })
}

const syncUrl = () => {
  const q = {}
  if (selectedTools.value[0]) q.t1 = selectedTools.value[0].id
  if (selectedTools.value[1]) q.t2 = selectedTools.value[1].id
  router.replace({ query: q })
}

const copyLink = async () => {
  await navigator.clipboard.writeText(window.location.href)
  copySuccess.value = true
  setTimeout(() => { copySuccess.value = false }, 2000)
}

const openVideo = (url) => {
  const match = url.match(/(?:v=|youtu\.be\/)([^&?/]+)/)
  if (match) {
    videoUrl.value = `https://www.youtube.com/embed/${match[1]}?autoplay=1`
    showVideoModal.value = true
  } else {
    window.open(url, '_blank')
  }
}

// ---- Winner logic ----
const statusScore = { 'Free': 3, 'Freemium': 2, 'Subscription': 1, 'Paid': 0 }

const winner = computed(() => {
  const [a, b] = selectedTools.value
  if (!a || !b) return {}

  const result = {}

  // rating: higher is better
  if (a.rating !== b.rating) {
    result.rating = a.rating > b.rating ? 0 : 1
  }

  // has_free_plan
  if (a.has_free_plan !== b.has_free_plan) {
    result.free = a.has_free_plan ? 0 : 1
  }

  // lowest paid: lower is better (null = no paid plan, which is "better" if free exists)
  const ap = a.lowest_paid_price
  const bp = b.lowest_paid_price
  if (ap !== bp) {
    if (ap === null) result.lowestPaid = 0
    else if (bp === null) result.lowestPaid = 1
    else result.lowestPaid = ap < bp ? 0 : 1
  }

  // pricing status
  const as = statusScore[a.pricing_status] ?? -1
  const bs = statusScore[b.pricing_status] ?? -1
  if (as !== bs) result.pricingStatus = as > bs ? 0 : 1

  // last_check: newer is better
  if (a.pricing_last_check && b.pricing_last_check && a.pricing_last_check !== b.pricing_last_check) {
    result.lastCheck = a.pricing_last_check > b.pricing_last_check ? 0 : 1
  }

  return result
})

const isWinner = (field, index) => winner.value[field] === index

const bothSelected = computed(() => selectedTools.value[0] && selectedTools.value[1])
const eitherSelected = computed(() => selectedTools.value[0] || selectedTools.value[1])

watch(searchQuery, (val) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => searchTools(val), 300)
})

onMounted(async () => {
  await fetchCategories()
  const t1 = route.query.t1
  const t2 = route.query.t2
  if (t1) await selectTool({ id: t1 }, 0)
  if (t2) await selectTool({ id: t2 }, 1)
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 pb-20 pt-10">

    <!-- Video Modal -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showVideoModal" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4" @click.self="showVideoModal = false">
        <div class="relative w-full max-w-3xl aspect-video bg-black rounded-2xl overflow-hidden shadow-2xl">
          <button @click="showVideoModal = false" class="absolute top-3 right-3 z-10 w-9 h-9 bg-black/60 hover:bg-black text-white rounded-full flex items-center justify-center border-none cursor-pointer">
            <X class="w-5 h-5" />
          </button>
          <iframe :src="videoUrl" class="w-full h-full" allow="autoplay; fullscreen" allowfullscreen frameborder="0"></iframe>
        </div>
      </div>
    </Transition>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="bg-white dark:bg-slate-800 rounded-[3rem] shadow-2xl p-8 md:p-12 border border-slate-100 dark:border-slate-700">

        <!-- Header -->
        <div class="text-center mb-12">
          <span class="inline-block px-4 py-1 rounded-full bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 font-bold text-sm mb-4">動態工具對比</span>
          <h2 class="text-3xl font-bold">選取兩款 AI 工具進行比較</h2>
          <p class="text-slate-500 dark:text-slate-400 mt-2">從我們的工具庫中挑選您感興趣的 AI 進行深度對比</p>

          <!-- Actions -->
          <div v-if="eitherSelected" class="mt-6 flex items-center justify-center gap-3">
            <button @click="copyLink" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-sm rounded-xl border-none cursor-pointer transition-all">
              <Copy class="w-4 h-4" />
              {{ copySuccess ? '已複製！' : '複製比較連結' }}
            </button>
            <button @click="resetAll" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 text-slate-700 dark:text-slate-200 font-bold text-sm rounded-xl border-none cursor-pointer transition-all">
              <RotateCcw class="w-4 h-4" />
              清除全部
            </button>
          </div>
        </div>

        <!-- Tool Selectors -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 relative">
          <div class="hidden md:flex absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700 border-4 border-white dark:border-slate-800 items-center justify-center text-slate-400 z-10 shadow-sm">
            <ArrowLeftRight class="w-6 h-6" />
          </div>

          <!-- 每個 slot 的外層 wrapper 加 relative，讓 overlay 可以覆蓋在上面 -->
          <div v-for="(tool, index) in selectedTools" :key="index" class="relative flex flex-col">

            <!-- Empty Slot -->
            <div
              v-if="!tool"
              class="group min-h-[400px] rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-600 flex flex-col items-center justify-center gap-6 hover:border-teal-400 hover:bg-teal-50/30 dark:hover:bg-teal-900/10 transition-all cursor-pointer"
              @click="openSearch(index)"
            >
              <div class="w-20 h-20 rounded-2xl bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-300 dark:text-slate-500 group-hover:text-teal-400 group-hover:scale-110 transition-all">
                <Search class="w-10 h-10" />
              </div>
              <div class="text-center">
                <h4 class="font-bold text-slate-400 dark:text-slate-500 group-hover:text-teal-600">選取第 {{ index + 1 }} 款工具</h4>
                <p class="text-slate-300 dark:text-slate-600 text-sm mt-1">搜尋名稱或分類...</p>
              </div>
            </div>

            <!-- Selected Tool Card -->
            <div v-else class="min-h-[400px] rounded-3xl bg-slate-50 dark:bg-slate-700 p-8 flex flex-col animate-in zoom-in-95 duration-300 group">
              <div class="flex justify-end mb-2">
                <button
                  @click="removeTool(index)"
                  class="w-9 h-9 rounded-full bg-white dark:bg-slate-600 text-slate-300 dark:text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all shadow-sm flex items-center justify-center opacity-0 group-hover:opacity-100 border-none cursor-pointer"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>

              <div class="flex flex-col items-center text-center mb-6">
                <div class="w-24 h-24 rounded-3xl bg-white dark:bg-slate-600 p-4 shadow-xl mb-6 relative">
                  <img :src="tool.logoUrl" class="w-full h-full object-contain" alt="logo">
                  <div class="absolute -bottom-2 -right-2 bg-teal-500 text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg border-2 border-white">
                    <Check class="w-4 h-4" />
                  </div>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ tool.name }}</h3>
                <div class="inline-flex items-center gap-1 mt-2 px-3 py-1 bg-white dark:bg-slate-600 rounded-full text-slate-500 dark:text-slate-300 text-sm font-medium shadow-sm">
                  <Star class="w-4 h-4 text-amber-400 fill-amber-400" />
                  <span>{{ tool.rating }}</span>
                  <span class="mx-1 text-slate-200">|</span>
                  <span>{{ tool.category_name }}</span>
                </div>
              </div>

              <p class="text-slate-500 dark:text-slate-300 text-sm line-clamp-3 text-center px-2 mb-6">{{ tool.description }}</p>

              <div class="flex gap-2 flex-wrap justify-center mt-auto">
                <a :href="tool.officialUrl" target="_blank" class="flex items-center gap-1.5 px-4 py-2.5 bg-white dark:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold rounded-xl border border-slate-200 dark:border-slate-500 hover:bg-slate-50 dark:hover:bg-slate-500 transition-all no-underline text-sm">
                  <ExternalLink class="w-4 h-4" />官方網站
                </a>
                <button v-if="tool.video_url" @click="openVideo(tool.video_url)" class="flex items-center gap-1.5 px-4 py-2.5 bg-white dark:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold rounded-xl border border-slate-200 dark:border-slate-500 hover:bg-slate-50 dark:hover:bg-slate-500 transition-all border-none cursor-pointer text-sm">
                  <Play class="w-4 h-4 text-red-500" />介紹影片
                </button>
                <button @click="toggleFavorite(tool)" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white dark:bg-slate-600 border border-slate-200 dark:border-slate-500 hover:bg-pink-50 dark:hover:bg-pink-900/20 font-bold transition-all border-none cursor-pointer text-sm" :class="isFavorited(tool.id) ? 'text-pink-500' : 'text-slate-400 dark:text-slate-300'">
                  <Heart class="w-4 h-4" :class="isFavorited(tool.id) ? 'fill-pink-500' : ''" />{{ isFavorited(tool.id) ? '已收藏' : '收藏' }}
                </button>
                <button @click="openSearch(index)" class="px-4 py-2.5 bg-teal-600 text-white font-bold rounded-xl hover:bg-teal-700 transition-all border-none cursor-pointer text-sm">
                  更換
                </button>
              </div>
            </div>

            <!-- Search Overlay -->
            <div v-if="activeSearchIndex === index" class="absolute inset-0 bg-white dark:bg-slate-800 z-20 p-6 flex flex-col rounded-3xl shadow-xl" @click.stop>

              <!-- ── Step 1：Slot 0 選類別 ── -->
              <template v-if="index === 0 && searchStep === 'category'">
                <div class="flex items-center justify-between mb-5">
                  <h4 class="font-bold text-slate-800 dark:text-slate-100">選擇工具類別</h4>
                  <button @click="activeSearchIndex = null" class="text-slate-400 hover:text-slate-600 bg-transparent border-none cursor-pointer text-sm font-medium">取消</button>
                </div>
                <div class="flex-grow overflow-y-auto custom-scrollbar">
                  <div class="grid grid-cols-2 gap-2">
                    <button
                      v-for="cat in categories"
                      :key="cat.id"
                      @click="pickCategory(cat)"
                      class="flex items-center gap-3 p-3 rounded-2xl border border-slate-100 dark:border-slate-600 hover:border-teal-300 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition-all text-left cursor-pointer bg-slate-50 dark:bg-slate-700 group"
                    >
                      <div class="w-9 h-9 rounded-xl bg-white dark:bg-slate-600 flex items-center justify-center shrink-0 shadow-sm group-hover:shadow-md transition-all">
                        <LayoutGrid class="w-4 h-4 text-teal-500" />
                      </div>
                      <span class="text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:text-teal-700 leading-tight">{{ cat.name }}</span>
                    </button>
                  </div>
                </div>
              </template>

              <!-- ── Step 2：選工具（Slot 0 選完類別後 / Slot 1 直接進入） ── -->
              <template v-else>
                <div class="flex items-center gap-3 mb-4">
                  <!-- Slot 0 在第二步有返回鍵 -->
                  <button
                    v-if="index === 0"
                    @click="searchStep = 'category'; searchQuery = ''; searchResults = []"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center border-none cursor-pointer shrink-0 transition-colors"
                  >
                    <ArrowLeft class="w-4 h-4 text-slate-600" />
                  </button>
                  <div class="flex-grow relative">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input
                      v-model="searchQuery"
                      type="text"
                      placeholder="搜尋工具名稱..."
                      class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border-none rounded-xl outline-none focus:ring-2 focus:ring-teal-500/20 text-sm"
                      autofocus
                    >
                  </div>
                  <button @click="activeSearchIndex = null; searchQuery = ''" class="text-slate-400 hover:text-slate-600 bg-transparent border-none cursor-pointer font-medium text-sm shrink-0">取消</button>
                </div>

                <!-- 分類標籤 -->
                <div class="mb-3 flex items-center gap-2 px-3 py-1.5 bg-teal-50 border border-teal-100 rounded-xl text-xs text-teal-700 font-bold w-fit">
                  <Check class="w-3.5 h-3.5 shrink-0" />
                  {{ index === 0 ? pickedCategory?.name : lockedTool?.category_name }}
                </div>

                <div class="flex-grow overflow-y-auto space-y-1 custom-scrollbar">
                  <div v-if="isSearching || isFetchingCategory" class="text-center py-10">
                    <Loader2 class="w-8 h-8 text-teal-500 animate-spin mx-auto" />
                    <p class="text-xs text-slate-400 mt-2">載入工具中...</p>
                  </div>
                  <template v-else-if="!searchQuery">
                    <div v-if="categoryTools.length === 0" class="text-center py-6 text-slate-400 text-sm">此分類暫無工具</div>
                    <div
                      v-for="result in categoryTools"
                      :key="result.id"
                      class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 cursor-pointer transition-colors border border-transparent hover:border-slate-100"
                      @click="selectTool(result, index)"
                    >
                      <img :src="result.logoUrl" class="w-10 h-10 rounded-lg object-cover shrink-0" alt="logo">
                      <div class="text-left flex-grow min-w-0">
                        <div class="font-bold text-slate-900 truncate text-sm">{{ result.name }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ result.description }}</div>
                      </div>
                      <div class="flex items-center gap-1 text-amber-500 text-sm shrink-0">
                        <Star class="w-3 h-3 fill-current" />{{ result.rating }}
                      </div>
                    </div>
                  </template>
                  <div v-else-if="searchResults.length === 0" class="text-center py-10 text-slate-400 text-sm">找不到符合的工具</div>
                  <template v-else>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">搜尋結果</div>
                    <div
                      v-for="result in searchResults"
                      :key="result.id"
                      class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 cursor-pointer transition-colors border border-transparent hover:border-slate-100"
                      @click="selectTool(result, index)"
                    >
                      <img :src="result.logoUrl" class="w-10 h-10 rounded-lg object-cover shrink-0" alt="logo">
                      <div class="text-left flex-grow min-w-0">
                        <div class="font-bold text-slate-900 truncate text-sm">{{ result.name }}</div>
                        <div class="text-xs text-slate-500">{{ result.category_name }}</div>
                      </div>
                      <div class="flex items-center gap-1 text-amber-500 text-sm shrink-0">
                        <Star class="w-3 h-3 fill-current" />{{ result.rating }}
                      </div>
                    </div>
                  </template>
                </div>
              </template>

            </div>

          </div>
        </div>

        <!-- Comparison Table -->
        <div v-if="eitherSelected" class="mt-20 border-t border-slate-100 pt-16">
          <h3 class="text-xl font-bold text-slate-900 mb-8 flex items-center gap-2">
            <Trophy class="w-5 h-5 text-amber-500" />
            詳細比較
            <span v-if="!bothSelected" class="text-sm font-normal text-slate-400 ml-2">（選取第二款工具以啟用勝者比較）</span>
          </h3>

          <div class="overflow-hidden bg-slate-50/50 dark:bg-slate-900/50 rounded-3xl border border-slate-100 dark:border-slate-700">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                  <th class="p-6 text-sm font-bold text-slate-500 dark:text-slate-400 w-1/4">比較項目</th>
                  <th class="p-6 text-center text-sm font-bold" :class="selectedTools[0] ? 'text-teal-700' : 'text-slate-400'">
                    {{ selectedTools[0]?.name ?? '工具 1' }}
                  </th>
                  <th class="p-6 text-center text-sm font-bold" :class="selectedTools[1] ? 'text-purple-700' : 'text-slate-400'">
                    {{ selectedTools[1]?.name ?? '工具 2' }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-700">

                <!-- Rating -->
                <tr class="hover:bg-white dark:hover:bg-slate-700 transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600 dark:text-slate-300">社群評分</td>
                  <td class="p-6 text-center" :class="{ 'bg-green-50': isWinner('rating', 0) }">
                    <div v-if="selectedTools[0]" class="flex items-center justify-center gap-1.5">
                      <div class="flex items-center gap-1 text-amber-600 font-bold text-lg">
                        <Star class="w-4 h-4 fill-current" />
                        {{ selectedTools[0].rating }}
                      </div>
                      <span v-if="isWinner('rating', 0)" class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">勝出</span>
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6 text-center" :class="{ 'bg-green-50': isWinner('rating', 1) }">
                    <div v-if="selectedTools[1]" class="flex items-center justify-center gap-1.5">
                      <div class="flex items-center gap-1 text-amber-600 font-bold text-lg">
                        <Star class="w-4 h-4 fill-current" />
                        {{ selectedTools[1].rating }}
                      </div>
                      <span v-if="isWinner('rating', 1)" class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">勝出</span>
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>

                <!-- Category -->
                <tr class="hover:bg-white dark:hover:bg-slate-700 transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600 dark:text-slate-300">應用分類</td>
                  <td class="p-6 text-center">
                    <span v-if="selectedTools[0]" class="inline-block px-4 py-1.5 rounded-full bg-teal-50 text-teal-700 font-bold text-sm border border-teal-100">
                      {{ selectedTools[0].category_name }}
                    </span>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6 text-center">
                    <span v-if="selectedTools[1]" class="inline-block px-4 py-1.5 rounded-full bg-purple-50 text-purple-700 font-bold text-sm border border-purple-100">
                      {{ selectedTools[1].category_name }}
                    </span>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>

                <!-- Pricing Status -->
                <tr class="hover:bg-white dark:hover:bg-slate-700 transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600 dark:text-slate-300">計費模式</td>
                  <td class="p-6 text-center" :class="{ 'bg-green-50': isWinner('pricingStatus', 0) }">
                    <div v-if="selectedTools[0]" class="flex items-center justify-center gap-1.5">
                      <span class="inline-block px-3 py-1 rounded-full text-xs font-bold"
                        :class="{
                          'bg-green-100 text-green-700': selectedTools[0].pricing_status === 'Free',
                          'bg-blue-100 text-blue-700': selectedTools[0].pricing_status === 'Freemium',
                          'bg-orange-100 text-orange-700': selectedTools[0].pricing_status === 'Paid' || selectedTools[0].pricing_status === 'Subscription',
                        }">
                        {{ selectedTools[0].pricing_status }}
                      </span>
                      <span v-if="isWinner('pricingStatus', 0)" class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">勝出</span>
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6 text-center" :class="{ 'bg-green-50': isWinner('pricingStatus', 1) }">
                    <div v-if="selectedTools[1]" class="flex items-center justify-center gap-1.5">
                      <span class="inline-block px-3 py-1 rounded-full text-xs font-bold"
                        :class="{
                          'bg-green-100 text-green-700': selectedTools[1].pricing_status === 'Free',
                          'bg-blue-100 text-blue-700': selectedTools[1].pricing_status === 'Freemium',
                          'bg-orange-100 text-orange-700': selectedTools[1].pricing_status === 'Paid' || selectedTools[1].pricing_status === 'Subscription',
                        }">
                        {{ selectedTools[1].pricing_status }}
                      </span>
                      <span v-if="isWinner('pricingStatus', 1)" class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">勝出</span>
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>

                <!-- Free Plan -->
                <tr class="hover:bg-white dark:hover:bg-slate-700 transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600 dark:text-slate-300">免費方案</td>
                  <td class="p-6 text-center" :class="{ 'bg-green-50': isWinner('free', 0) }">
                    <div v-if="selectedTools[0]" class="flex items-center justify-center gap-1.5">
                      <span v-if="selectedTools[0].has_free_plan" class="inline-flex items-center gap-1 text-green-600 font-bold">
                        <Check class="w-4 h-4" /> 有免費方案
                      </span>
                      <span v-else class="text-slate-400">無</span>
                      <span v-if="isWinner('free', 0)" class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">勝出</span>
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6 text-center" :class="{ 'bg-green-50': isWinner('free', 1) }">
                    <div v-if="selectedTools[1]" class="flex items-center justify-center gap-1.5">
                      <span v-if="selectedTools[1].has_free_plan" class="inline-flex items-center gap-1 text-green-600 font-bold">
                        <Check class="w-4 h-4" /> 有免費方案
                      </span>
                      <span v-else class="text-slate-400">無</span>
                      <span v-if="isWinner('free', 1)" class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">勝出</span>
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>

                <!-- Lowest Paid Price -->
                <tr class="hover:bg-white dark:hover:bg-slate-700 transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600 dark:text-slate-300">最低月費</td>
                  <td class="p-6 text-center" :class="{ 'bg-green-50': isWinner('lowestPaid', 0) }">
                    <div v-if="selectedTools[0]" class="flex items-center justify-center gap-1.5">
                      <span class="font-bold text-slate-800 dark:text-slate-100">
                        {{ selectedTools[0].lowest_paid_price != null ? `$${selectedTools[0].lowest_paid_price} / 月` : '—' }}
                      </span>
                      <span v-if="isWinner('lowestPaid', 0)" class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">勝出</span>
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6 text-center" :class="{ 'bg-green-50': isWinner('lowestPaid', 1) }">
                    <div v-if="selectedTools[1]" class="flex items-center justify-center gap-1.5">
                      <span class="font-bold text-slate-800 dark:text-slate-100">
                        {{ selectedTools[1].lowest_paid_price != null ? `$${selectedTools[1].lowest_paid_price} / 月` : '—' }}
                      </span>
                      <span v-if="isWinner('lowestPaid', 1)" class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">勝出</span>
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>

                <!-- Pricing Plans Detail -->
                <tr class="hover:bg-white transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600 align-top pt-7">所有方案</td>
                  <td class="p-6">
                    <div v-if="selectedTools[0]?.plans?.length">
                      <div class="flex flex-wrap justify-center gap-2 mb-3">
                        <span v-for="plan in selectedTools[0].plans" :key="plan.name" class="px-2 py-1 bg-white text-xs font-bold text-slate-700 rounded-lg border border-slate-200">
                          {{ plan.name }}: {{ plan.price }}
                        </span>
                      </div>
                      <button
                        @click="expandedPlans[0] = !expandedPlans[0]"
                        class="flex items-center gap-1 mx-auto text-xs text-teal-600 font-bold bg-transparent border-none cursor-pointer hover:text-teal-800"
                      >
                        {{ expandedPlans[0] ? '收起' : '查看功能列表' }}
                        <ChevronDown v-if="!expandedPlans[0]" class="w-3 h-3" />
                        <ChevronUp v-else class="w-3 h-3" />
                      </button>
                      <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
                        <div v-if="expandedPlans[0]" class="mt-4 space-y-3">
                          <div v-for="plan in selectedTools[0].plans" :key="plan.name" class="bg-white dark:bg-slate-700 rounded-2xl p-4 border border-slate-100 dark:border-slate-600 text-left">
                            <div class="flex items-center justify-between mb-2">
                              <span class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ plan.name }}</span>
                              <span class="font-bold text-sm text-teal-600">{{ plan.price }}</span>
                            </div>
                            <p v-if="plan.description" class="text-xs text-slate-500 mb-2">{{ plan.description }}</p>
                            <ul v-if="plan.features?.length" class="space-y-1">
                              <li v-for="feat in plan.features" :key="feat" class="flex items-start gap-1.5 text-xs text-slate-600">
                                <Check class="w-3 h-3 text-teal-500 mt-0.5 shrink-0" />
                                {{ feat }}
                              </li>
                            </ul>
                          </div>
                        </div>
                      </Transition>
                    </div>
                    <span v-else-if="selectedTools[0]" class="text-slate-500 text-sm">未提供詳細資料</span>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6">
                    <div v-if="selectedTools[1]?.plans?.length">
                      <div class="flex flex-wrap justify-center gap-2 mb-3">
                        <span v-for="plan in selectedTools[1].plans" :key="plan.name" class="px-2 py-1 bg-white text-xs font-bold text-slate-700 rounded-lg border border-slate-200">
                          {{ plan.name }}: {{ plan.price }}
                        </span>
                      </div>
                      <button
                        @click="expandedPlans[1] = !expandedPlans[1]"
                        class="flex items-center gap-1 mx-auto text-xs text-purple-600 font-bold bg-transparent border-none cursor-pointer hover:text-purple-800"
                      >
                        {{ expandedPlans[1] ? '收起' : '查看功能列表' }}
                        <ChevronDown v-if="!expandedPlans[1]" class="w-3 h-3" />
                        <ChevronUp v-else class="w-3 h-3" />
                      </button>
                      <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
                        <div v-if="expandedPlans[1]" class="mt-4 space-y-3">
                          <div v-for="plan in selectedTools[1].plans" :key="plan.name" class="bg-white dark:bg-slate-700 rounded-2xl p-4 border border-slate-100 dark:border-slate-600 text-left">
                            <div class="flex items-center justify-between mb-2">
                              <span class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ plan.name }}</span>
                              <span class="font-bold text-sm text-purple-600">{{ plan.price }}</span>
                            </div>
                            <p v-if="plan.description" class="text-xs text-slate-500 mb-2">{{ plan.description }}</p>
                            <ul v-if="plan.features?.length" class="space-y-1">
                              <li v-for="feat in plan.features" :key="feat" class="flex items-start gap-1.5 text-xs text-slate-600">
                                <Check class="w-3 h-3 text-purple-500 mt-0.5 shrink-0" />
                                {{ feat }}
                              </li>
                            </ul>
                          </div>
                        </div>
                      </Transition>
                    </div>
                    <span v-else-if="selectedTools[1]" class="text-slate-500 text-sm">未提供詳細資料</span>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>

                <!-- Video -->
                <tr class="hover:bg-white dark:hover:bg-slate-700 transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600 dark:text-slate-300">介紹影片</td>
                  <td class="p-6 text-center">
                    <button v-if="selectedTools[0]?.video_url" @click="openVideo(selectedTools[0].video_url)" class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 font-bold text-sm rounded-xl border-none cursor-pointer hover:bg-red-100 transition-all">
                      <Play class="w-4 h-4" /> 觀看
                    </button>
                    <span v-else-if="selectedTools[0]" class="text-slate-400">未提供</span>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6 text-center">
                    <button v-if="selectedTools[1]?.video_url" @click="openVideo(selectedTools[1].video_url)" class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 font-bold text-sm rounded-xl border-none cursor-pointer hover:bg-red-100 transition-all">
                      <Play class="w-4 h-4" /> 觀看
                    </button>
                    <span v-else-if="selectedTools[1]" class="text-slate-400">未提供</span>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>

                <!-- Data Last Check -->
                <tr class="hover:bg-white dark:hover:bg-slate-700 transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600 dark:text-slate-300">資料更新時間</td>
                  <td class="p-6 text-center" :class="{ 'bg-green-50': isWinner('lastCheck', 0) }">
                    <div v-if="selectedTools[0]" class="flex items-center justify-center gap-1.5">
                      <span class="text-sm text-slate-600">{{ selectedTools[0].pricing_last_check ?? '—' }}</span>
                      <span v-if="isWinner('lastCheck', 0)" class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">最新</span>
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6 text-center" :class="{ 'bg-green-50': isWinner('lastCheck', 1) }">
                    <div v-if="selectedTools[1]" class="flex items-center justify-center gap-1.5">
                      <span class="text-sm text-slate-600">{{ selectedTools[1].pricing_last_check ?? '—' }}</span>
                      <span v-if="isWinner('lastCheck', 1)" class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">最新</span>
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>

                <!-- Official Website -->
                <tr class="hover:bg-white dark:hover:bg-slate-700 transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600 dark:text-slate-300">官方網站</td>
                  <td class="p-6 text-center">
                    <a v-if="selectedTools[0]" :href="selectedTools[0].officialUrl" target="_blank" class="text-teal-700 font-bold hover:underline">前往官方網站</a>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6 text-center">
                    <a v-if="selectedTools[1]" :href="selectedTools[1].officialUrl" target="_blank" class="text-teal-700 font-bold hover:underline">前往官方網站</a>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>

              </tbody>
            </table>
          </div>
        </div>

      </div>
    </section>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
