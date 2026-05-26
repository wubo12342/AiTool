<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import {
  LayoutDashboard, Wrench, MessageSquare, Users,
  Plus, Pencil, Trash2, Search, X, Check, Loader2,
  Star, ChevronDown, AlertTriangle, Package, Eye,
  ArrowLeft, Save
} from 'lucide-vue-next'

const router = useRouter()
const activeTab = ref('tools')

// ── Stats ──────────────────────────────────────────────
const stats = reactive({ tools: 0, reviews: 0, users: 0, categories: 0 })

// ── Tools list ─────────────────────────────────────────
const tools = ref([])
const toolSearch = ref('')
const categories = ref([])

const filteredTools = computed(() => {
  const q = toolSearch.value.toLowerCase()
  if (!q) return tools.value
  return tools.value.filter(t =>
    t.name.toLowerCase().includes(q) ||
    (t.category_name || '').toLowerCase().includes(q)
  )
})

// ── Tool form ──────────────────────────────────────────
const showToolForm = ref(false)
const isEditMode = ref(false)
const savingTool = ref(false)
const toolFormError = ref('')

const blankForm = () => ({
  id: null,
  name: '',
  logo_url: '',
  website_url: '',
  video_url: '',
  cid: '',
  description: '',
  pricing: { status: 'Subscription', currency: '$', plans: [] }
})

const toolForm = reactive(blankForm())

const resetToolForm = () => {
  const blank = blankForm()
  Object.assign(toolForm, blank)
  toolFormError.value = ''
}

const openAddForm = () => {
  resetToolForm()
  toolForm.cid = categories.value[0]?.id || ''
  isEditMode.value = false
  showToolForm.value = true
}

const openEditForm = (tool) => {
  resetToolForm()
  let pricing = { status: 'Subscription', currency: '$', plans: [] }
  if (tool.pricing_plans) {
    try { pricing = JSON.parse(tool.pricing_plans) } catch (e) { /* keep default */ }
  }
  // Deep clone plans so edits don't affect the list
  pricing.plans = (pricing.plans || []).map(p => ({
    ...p,
    features: [...(p.features || [])]
  }))
  Object.assign(toolForm, {
    id: tool.id,
    name: tool.name,
    logo_url: tool.logo_url || '',
    website_url: tool.website_url || '',
    video_url: tool.video_url || '',
    cid: tool.cid || '',
    description: tool.description || '',
    pricing
  })
  isEditMode.value = true
  showToolForm.value = true
}

const addPlan = () => {
  toolForm.pricing.plans.push({ name: '', price: '0', features: [] })
}

const removePlan = (i) => {
  toolForm.pricing.plans.splice(i, 1)
}

const addFeature = (pi) => {
  toolForm.pricing.plans[pi].features.push('')
}

const removeFeature = (pi, fi) => {
  toolForm.pricing.plans[pi].features.splice(fi, 1)
}

const submitTool = async () => {
  toolFormError.value = ''
  if (!toolForm.name.trim()) { toolFormError.value = '請填寫工具名稱'; return }
  if (!toolForm.cid) { toolFormError.value = '請選擇分類'; return }

  savingTool.value = true
  const endpoint = isEditMode.value ? '/api/admin/update_tool.php' : '/api/admin/add_tool.php'
  try {
    await axios.post(endpoint, {
      token: getToken(),
      id: toolForm.id,
      name: toolForm.name.trim(),
      logo_url: toolForm.logo_url.trim(),
      website_url: toolForm.website_url.trim(),
      video_url: toolForm.video_url.trim(),
      cid: toolForm.cid,
      description: toolForm.description.trim(),
      pricing_plans: JSON.stringify(toolForm.pricing)
    })
    showToolForm.value = false
    await fetchTools()
    await fetchStats()
  } catch (e) {
    toolFormError.value = e.response?.data?.error || '操作失敗，請稍後再試'
  } finally {
    savingTool.value = false
  }
}

const deleteTool = async (tool) => {
  if (!confirm(`確定要刪除「${tool.name}」？相關評論與收藏記錄也會一併刪除，此操作無法復原。`)) return
  try {
    await axios.post('/api/admin/delete_tool.php', { token: getToken(), id: tool.id })
    await fetchTools()
    await fetchStats()
  } catch (e) {
    alert(e.response?.data?.error || '刪除失敗')
  }
}

// ── Reviews ────────────────────────────────────────────
const reviews = ref([])
const reviewSearch = ref('')
const reviewsLoaded = ref(false)

const filteredReviews = computed(() => {
  const q = reviewSearch.value.toLowerCase()
  if (!q) return reviews.value
  return reviews.value.filter(r =>
    (r.tool_name || '').toLowerCase().includes(q) ||
    (r.username || '').toLowerCase().includes(q) ||
    (r.comment || '').toLowerCase().includes(q)
  )
})

const deleteReview = async (review) => {
  if (!confirm('確定要刪除這則評論？')) return
  try {
    await axios.post('/api/admin/delete_review.php', { token: getToken(), id: review.review_id })
    reviews.value = reviews.value.filter(r => r.review_id !== review.review_id)
    stats.reviews = Math.max(0, stats.reviews - 1)
  } catch (e) {
    alert(e.response?.data?.error || '刪除失敗')
  }
}

// ── Users ──────────────────────────────────────────────
const users = ref([])
const usersLoaded = ref(false)

// ── Helpers ────────────────────────────────────────────
const getToken = () => localStorage.getItem('jwt_token') || ''

const fetchStats = async () => {
  const res = await axios.post('/api/admin/get_stats.php', { token: getToken() })
  Object.assign(stats, res.data)
}

const fetchTools = async () => {
  const res = await axios.post('/api/admin/get_all_tools.php', { token: getToken() })
  tools.value = res.data.tools || []
}

const fetchCategories = async () => {
  const res = await axios.get('/api/get_categories.php')
  categories.value = res.data || []
}

const fetchReviews = async () => {
  const res = await axios.post('/api/admin/get_all_reviews.php', { token: getToken() })
  reviews.value = res.data.reviews || []
  reviewsLoaded.value = true
}

const fetchUsers = async () => {
  const res = await axios.post('/api/admin/get_users.php', { token: getToken() })
  users.value = res.data.users || []
  usersLoaded.value = true
}

const switchTab = async (tab) => {
  activeTab.value = tab
  if (tab === 'reviews' && !reviewsLoaded.value) await fetchReviews()
  if (tab === 'users'   && !usersLoaded.value)   await fetchUsers()
}

onMounted(async () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  if (Number(user.role) !== 1) {
    router.push('/')
    return
  }
  await Promise.all([fetchStats(), fetchTools(), fetchCategories()])
})

const starsArray = (n) => Array.from({ length: 5 }, (_, i) => i < n)
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900 pb-20">

    <!-- Page Header -->
    <div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-16 z-30 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 py-4">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">
              <LayoutDashboard class="w-5 h-5 text-primary" />
            </div>
            <div>
              <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-tight">管理後台</h1>
              <p class="text-xs text-slate-500 dark:text-slate-400">AI Hub Admin Panel</p>
            </div>
          </div>
          <!-- Tabs -->
          <div class="flex gap-1 bg-slate-100 dark:bg-slate-700 rounded-xl p-1">
            <button
              v-for="tab in [
                { id: 'tools',   label: '工具管理', icon: Wrench },
                { id: 'reviews', label: '評論管理', icon: MessageSquare },
                { id: 'users',   label: '用戶列表', icon: Users }
              ]"
              :key="tab.id"
              @click="switchTab(tab.id)"
              :class="activeTab === tab.id
                ? 'bg-white dark:bg-slate-600 text-primary shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
              class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all"
            >
              <component :is="tab.icon" class="w-4 h-4" />
              <span class="hidden sm:inline">{{ tab.label }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div
          v-for="card in [
            { label: 'AI 工具', value: stats.tools, icon: Wrench, color: 'blue' },
            { label: '評論數', value: stats.reviews, icon: MessageSquare, color: 'violet' },
            { label: '用戶數', value: stats.users, icon: Users, color: 'emerald' },
            { label: '分類數', value: stats.categories, icon: Package, color: 'orange' }
          ]"
          :key="card.label"
          class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-5 shadow-sm"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ card.label }}</span>
            <div :class="{
              'bg-blue-50 dark:bg-blue-900/30 text-blue-500': card.color === 'blue',
              'bg-violet-50 dark:bg-violet-900/30 text-violet-500': card.color === 'violet',
              'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500': card.color === 'emerald',
              'bg-orange-50 dark:bg-orange-900/30 text-orange-500': card.color === 'orange',
            }" class="w-8 h-8 rounded-lg flex items-center justify-center">
              <component :is="card.icon" class="w-4 h-4" />
            </div>
          </div>
          <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ card.value }}</p>
        </div>
      </div>

      <!-- ═══════════════ TOOLS TAB ═══════════════ -->
      <div v-if="activeTab === 'tools'">
        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row gap-3 mb-6">
          <div class="relative flex-1">
            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
              v-model="toolSearch"
              type="text"
              placeholder="搜尋工具名稱或分類..."
              class="w-full pl-11 pr-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-sm transition-all"
            >
          </div>
          <button
            @click="openAddForm"
            class="flex items-center gap-2 px-5 py-3 bg-primary hover:bg-primary/90 text-white rounded-xl font-bold text-sm transition-all shadow-lg shadow-primary/20 border-none cursor-pointer whitespace-nowrap"
          >
            <Plus class="w-4 h-4" />
            新增工具
          </button>
        </div>

        <!-- Tools Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 overflow-hidden shadow-sm">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                  <th class="text-left px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">工具</th>
                  <th class="text-left px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">分類</th>
                  <th class="text-center px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">評分</th>
                  <th class="text-center px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">評論</th>
                  <th class="text-center px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">收藏</th>
                  <th class="text-right px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="tool in filteredTools"
                  :key="tool.id"
                  class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
                >
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <img
                        :src="tool.logo_url || `https://api.dicebear.com/7.x/identicon/svg?seed=${encodeURIComponent(tool.name)}`"
                        :alt="tool.name"
                        class="w-9 h-9 rounded-lg object-contain bg-slate-50 dark:bg-slate-700 border border-slate-100 dark:border-slate-600 p-0.5 shrink-0"
                        @error="e => e.target.src = `https://api.dicebear.com/7.x/identicon/svg?seed=${encodeURIComponent(tool.name)}`"
                      >
                      <div>
                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ tool.name }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 max-w-[200px] truncate">{{ tool.description }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-4 hidden md:table-cell">
                    <span class="px-2.5 py-1 bg-primary/10 text-primary rounded-full text-xs font-semibold">
                      {{ tool.category_name || '未分類' }}
                    </span>
                  </td>
                  <td class="px-4 py-4 text-center hidden lg:table-cell">
                    <span class="font-bold text-amber-500">{{ tool.rating.toFixed(1) }}</span>
                  </td>
                  <td class="px-4 py-4 text-center hidden lg:table-cell text-slate-600 dark:text-slate-300">
                    {{ tool.review_count }}
                  </td>
                  <td class="px-4 py-4 text-center hidden lg:table-cell text-slate-600 dark:text-slate-300">
                    {{ tool.favorite_count }}
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                      <button
                        @click="openEditForm(tool)"
                        class="p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors border-none bg-transparent cursor-pointer"
                        title="編輯"
                      >
                        <Pencil class="w-4 h-4" />
                      </button>
                      <button
                        @click="deleteTool(tool)"
                        class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors border-none bg-transparent cursor-pointer"
                        title="刪除"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredTools.length === 0">
                  <td colspan="6" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">
                    {{ toolSearch ? '找不到符合條件的工具' : '尚無工具資料' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════ REVIEWS TAB ═══════════════ -->
      <div v-else-if="activeTab === 'reviews'">
        <div class="flex gap-3 mb-6">
          <div class="relative flex-1">
            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
              v-model="reviewSearch"
              type="text"
              placeholder="搜尋工具名稱、用戶或評論內容..."
              class="w-full pl-11 pr-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-sm transition-all"
            >
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 overflow-hidden shadow-sm">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                  <th class="text-left px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">用戶 / 工具</th>
                  <th class="text-left px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">評論內容</th>
                  <th class="text-center px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">評分</th>
                  <th class="text-center px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">日期</th>
                  <th class="text-right px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="review in filteredReviews"
                  :key="review.review_id"
                  class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
                >
                  <td class="px-6 py-4">
                    <p class="font-semibold text-slate-800 dark:text-slate-200">{{ review.username }}</p>
                    <p class="text-xs text-primary mt-0.5">{{ review.tool_name }}</p>
                  </td>
                  <td class="px-4 py-4 max-w-[280px]">
                    <p class="text-slate-600 dark:text-slate-300 text-sm line-clamp-2">{{ review.comment || '（無文字評論）' }}</p>
                  </td>
                  <td class="px-4 py-4 text-center hidden sm:table-cell">
                    <div class="flex items-center justify-center gap-0.5">
                      <Star
                        v-for="(filled, i) in starsArray(review.stars)"
                        :key="i"
                        :class="filled ? 'text-amber-400 fill-amber-400' : 'text-slate-200 dark:text-slate-600'"
                        class="w-3.5 h-3.5"
                      />
                    </div>
                  </td>
                  <td class="px-4 py-4 text-center hidden md:table-cell text-xs text-slate-400 dark:text-slate-500">
                    {{ (review.created_at || '').slice(0, 10) }}
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex justify-end">
                      <button
                        @click="deleteReview(review)"
                        class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors border-none bg-transparent cursor-pointer"
                        title="刪除評論"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredReviews.length === 0">
                  <td colspan="5" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">
                    {{ reviewSearch ? '找不到符合條件的評論' : '尚無評論資料' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════ USERS TAB ═══════════════ -->
      <div v-else-if="activeTab === 'users'">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 overflow-hidden shadow-sm">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                  <th class="text-left px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">UID</th>
                  <th class="text-left px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">用戶名稱</th>
                  <th class="text-center px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">角色</th>
                  <th class="text-center px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">評論數</th>
                  <th class="text-center px-4 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">收藏數</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="user in users"
                  :key="user.uid"
                  class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
                >
                  <td class="px-6 py-4 text-slate-400 dark:text-slate-500 font-mono text-xs">#{{ user.uid }}</td>
                  <td class="px-4 py-4 font-semibold text-slate-800 dark:text-slate-200">{{ user.username }}</td>
                  <td class="px-4 py-4 text-center">
                    <span
                      :class="user.role === 'admin'
                        ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
                        : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'"
                      class="px-2.5 py-1 rounded-full text-xs font-bold"
                    >
                      {{ user.role === 'admin' ? 'Admin' : 'User' }}
                    </span>
                  </td>
                  <td class="px-4 py-4 text-center text-slate-600 dark:text-slate-300 hidden sm:table-cell">{{ user.review_count }}</td>
                  <td class="px-4 py-4 text-center text-slate-600 dark:text-slate-300 hidden sm:table-cell">{{ user.favorite_count }}</td>
                </tr>
                <tr v-if="users.length === 0">
                  <td colspan="5" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500">尚無用戶資料</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════ ADD / EDIT TOOL MODAL ═══════════════ -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showToolForm"
          class="fixed inset-0 z-[70] bg-slate-900/80 flex items-start justify-center p-4 sm:p-8 overflow-y-auto"
          @click.self="showToolForm = false"
        >
          <div class="w-full max-w-2xl bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-700 my-4">

            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
              <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                {{ isEditMode ? '編輯工具' : '新增 AI 工具' }}
              </h2>
              <button
                @click="showToolForm = false"
                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors border-none bg-transparent cursor-pointer"
              >
                <X class="w-5 h-5" />
              </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-6">

              <!-- Error -->
              <div v-if="toolFormError" class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl border border-red-100 dark:border-red-800 text-sm">
                <AlertTriangle class="w-5 h-5 shrink-0" />
                {{ toolFormError }}
              </div>

              <!-- Basic Info Section -->
              <div>
                <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">基本資訊</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <!-- Name -->
                  <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">工具名稱 *</label>
                    <input
                      v-model="toolForm.name"
                      type="text"
                      placeholder="例如：ChatGPT"
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all"
                    >
                  </div>

                  <!-- Category -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">分類 *</label>
                    <select
                      v-model="toolForm.cid"
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all appearance-none cursor-pointer"
                    >
                      <option value="" disabled>請選擇分類</option>
                      <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                  </div>

                  <!-- Logo URL -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Logo 圖片網址</label>
                    <input
                      v-model="toolForm.logo_url"
                      type="url"
                      placeholder="https://..."
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all"
                    >
                  </div>

                  <!-- Website URL -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">官方網站</label>
                    <input
                      v-model="toolForm.website_url"
                      type="url"
                      placeholder="https://..."
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all"
                    >
                  </div>

                  <!-- Video URL -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">介紹影片網址</label>
                    <input
                      v-model="toolForm.video_url"
                      type="url"
                      placeholder="https://youtube.com/..."
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all"
                    >
                  </div>

                  <!-- Description -->
                  <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">工具簡介</label>
                    <textarea
                      v-model="toolForm.description"
                      rows="3"
                      placeholder="簡短描述這個工具的功能與特色..."
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all resize-none"
                    ></textarea>
                  </div>
                </div>
              </div>

              <!-- Pricing Section -->
              <div>
                <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">定價方案</h3>

                <!-- Pricing Meta -->
                <div class="grid grid-cols-2 gap-3 mb-4">
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">幣別</label>
                    <select
                      v-model="toolForm.pricing.currency"
                      class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-sm appearance-none cursor-pointer"
                    >
                      <option value="$">$ (USD)</option>
                      <option value="€">€ (EUR)</option>
                      <option value="¥">¥ (JPY)</option>
                      <option value="NT$">NT$ (TWD)</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">收費類型</label>
                    <select
                      v-model="toolForm.pricing.status"
                      class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-sm appearance-none cursor-pointer"
                    >
                      <option value="Subscription">訂閱制（每月）</option>
                      <option value="One-time">買斷制</option>
                      <option value="Free">完全免費</option>
                      <option value="Contact">洽詢報價</option>
                    </select>
                  </div>
                </div>

                <!-- Plans -->
                <div class="space-y-3">
                  <div
                    v-for="(plan, pi) in toolForm.pricing.plans"
                    :key="pi"
                    class="p-4 bg-slate-50 dark:bg-slate-700/60 rounded-2xl border border-slate-200 dark:border-slate-600"
                  >
                    <div class="flex items-center justify-between mb-3">
                      <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">方案 {{ pi + 1 }}</span>
                      <button
                        @click="removePlan(pi)"
                        class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors border-none bg-transparent cursor-pointer"
                      >
                        <X class="w-4 h-4" />
                      </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                      <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">方案名稱</label>
                        <input
                          v-model="plan.name"
                          type="text"
                          placeholder="例如：Free、Pro、Enterprise"
                          class="w-full px-3 py-2 bg-white dark:bg-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-500 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 text-sm"
                        >
                      </div>
                      <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">價格（填 0 表示免費）</label>
                        <input
                          v-model="plan.price"
                          type="text"
                          placeholder="0"
                          class="w-full px-3 py-2 bg-white dark:bg-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-500 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 text-sm"
                        >
                      </div>
                    </div>

                    <!-- Features -->
                    <div>
                      <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">功能列表</label>
                      <div class="space-y-2">
                        <div
                          v-for="(feat, fi) in plan.features"
                          :key="fi"
                          class="flex items-center gap-2"
                        >
                          <input
                            v-model="plan.features[fi]"
                            type="text"
                            placeholder="功能描述..."
                            class="flex-1 px-3 py-1.5 bg-white dark:bg-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-500 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 text-sm"
                          >
                          <button
                            @click="removeFeature(pi, fi)"
                            class="p-1 text-slate-400 hover:text-red-500 transition-colors border-none bg-transparent cursor-pointer"
                          >
                            <X class="w-3.5 h-3.5" />
                          </button>
                        </div>
                      </div>
                      <button
                        @click="addFeature(pi)"
                        class="mt-2 text-xs text-primary hover:underline font-semibold border-none bg-transparent cursor-pointer flex items-center gap-1"
                      >
                        <Plus class="w-3 h-3" /> 新增功能
                      </button>
                    </div>
                  </div>
                </div>

                <button
                  @click="addPlan"
                  class="mt-3 w-full py-3 border-2 border-dashed border-slate-200 dark:border-slate-600 hover:border-primary dark:hover:border-primary text-slate-400 dark:text-slate-500 hover:text-primary rounded-2xl text-sm font-semibold transition-all bg-transparent cursor-pointer flex items-center justify-center gap-2"
                >
                  <Plus class="w-4 h-4" />
                  新增定價方案
                </button>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700">
              <button
                @click="showToolForm = false"
                class="px-5 py-2.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl font-semibold text-sm transition-all border border-slate-200 dark:border-slate-600 bg-transparent cursor-pointer"
              >
                取消
              </button>
              <button
                @click="submitTool"
                :disabled="savingTool"
                class="flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-primary/90 disabled:bg-slate-300 text-white rounded-xl font-bold text-sm transition-all shadow-lg shadow-primary/20 border-none cursor-pointer"
              >
                <Loader2 v-if="savingTool" class="w-4 h-4 animate-spin" />
                <Save v-else class="w-4 h-4" />
                {{ isEditMode ? '儲存變更' : '新增工具' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
