<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import {
  LayoutDashboard, Wrench, MessageSquare, Users,
  Plus, Pencil, Trash2, Search, X, Check, Loader2,
  Star, AlertTriangle, Package, Save, Tag, Zap, RefreshCw,
  Eye, ExternalLink, Bell, Twitter, Globe
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

// ── Reviews search (declared early so watch below can reference it) ─────
const reviews      = ref([])
const reviewSearch = ref('')
const reviewStarFilter = ref(0)
const reviewSort   = ref('newest')
const reviewsLoaded = ref(false)
const selectedReviewIds = ref([])
const detailReview = ref(null)
const bulkDeleting = ref(false)

const filteredReviews = computed(() => {
  const q = reviewSearch.value.toLowerCase()
  const list = reviews.value.filter(r => {
    if (reviewStarFilter.value && r.stars !== reviewStarFilter.value) return false
    if (!q) return true
    return (r.tool_name || '').toLowerCase().includes(q) ||
      (r.username  || '').toLowerCase().includes(q) ||
      (r.comment   || '').toLowerCase().includes(q)
  })
  const sorted = [...list]
  switch (reviewSort.value) {
    case 'oldest':     sorted.sort((a, b) => new Date(a.created_at) - new Date(b.created_at)); break
    case 'stars_desc': sorted.sort((a, b) => b.stars - a.stars); break
    case 'stars_asc':  sorted.sort((a, b) => a.stars - b.stars); break
    default:           sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  }
  return sorted
})

// ── Users (declared early so pagedUsers computed below can reference it) ─
const users       = ref([])
const usersLoaded = ref(false)
const userSearch  = ref('')
const updatingUid = ref(null)
const currentUid  = Number(JSON.parse(localStorage.getItem('user') || '{}').uid || 0)

const filteredUsers = computed(() => {
  const q = userSearch.value.toLowerCase()
  if (!q) return users.value
  return users.value.filter(u => (u.username || '').toLowerCase().includes(q))
})

// ── Pagination ─────────────────────────────────────────
const toolsPage     = ref(1)
const toolsPageSize = ref(10)
const toolsPageCount = computed(() => Math.max(1, Math.ceil(filteredTools.value.length / toolsPageSize.value)))
const pagedTools = computed(() =>
  filteredTools.value.slice((toolsPage.value - 1) * toolsPageSize.value, toolsPage.value * toolsPageSize.value)
)

const reviewsPage     = ref(1)
const reviewsPageSize = ref(10)
const reviewsPageCount = computed(() => Math.max(1, Math.ceil(filteredReviews.value.length / reviewsPageSize.value)))
const pagedReviews = computed(() =>
  filteredReviews.value.slice((reviewsPage.value - 1) * reviewsPageSize.value, reviewsPage.value * reviewsPageSize.value)
)

const usersPage     = ref(1)
const usersPageSize = ref(10)
const usersPageCount = computed(() => Math.max(1, Math.ceil(filteredUsers.value.length / usersPageSize.value)))
const pagedUsers = computed(() =>
  filteredUsers.value.slice((usersPage.value - 1) * usersPageSize.value, usersPage.value * usersPageSize.value)
)

watch(toolSearch,   () => { toolsPage.value   = 1 })
watch(reviewSearch, () => { reviewsPage.value = 1 })
watch(reviewStarFilter, () => { reviewsPage.value = 1 })
watch(reviewSort,   () => { reviewsPage.value = 1 })
watch(pagedReviews, () => { selectedReviewIds.value = [] })
watch(userSearch,   () => { usersPage.value   = 1 })

// ── Tool form ──────────────────────────────────────────
const showToolForm = ref(false)
const isEditMode   = ref(false)
const savingTool   = ref(false)
const toolFormError = ref('')

const blankForm = () => ({
  id: null, name: '', logo_url: '', website_url: '', video_url: '',
  cid: '', description: '',
  pricing: { status: 'Freemium', currency: 'USD', plans: [] },
  tag_ids: []
})

const toolForm = reactive(blankForm())

const resetToolForm = () => {
  Object.assign(toolForm, blankForm())
  toolFormError.value = ''
}

const openAddForm = async () => {
  resetToolForm()
  toolForm.cid = categories.value[0]?.id || ''
  isEditMode.value = false
  showToolForm.value = true
  if (allTags.value.length === 0) await fetchAllTags()
}

const openEditForm = async (tool) => {
  resetToolForm()
  let pricing = { status: 'Freemium', currency: 'USD', plans: [] }
  if (tool.pricing_plans) {
    try { pricing = JSON.parse(tool.pricing_plans) } catch (e) {}
  }
  pricing.plans = (pricing.plans || []).map(p => ({ ...p, features: [...(p.features || [])] }))
  Object.assign(toolForm, {
    id: tool.id, name: tool.name,
    logo_url: tool.logo_url || '', website_url: tool.website_url || '',
    video_url: tool.video_url || '', cid: tool.cid || '',
    description: tool.description || '', pricing,
    tag_ids: [...(tool.tag_ids || [])]
  })
  isEditMode.value = true
  showToolForm.value = true
  if (allTags.value.length === 0) await fetchAllTags()
}

const addPlan      = () => toolForm.pricing.plans.push({ name: '', price: '0', features: [] })
const removePlan   = (i) => toolForm.pricing.plans.splice(i, 1)
const addFeature   = (pi) => toolForm.pricing.plans[pi].features.push('')
const removeFeature = (pi, fi) => toolForm.pricing.plans[pi].features.splice(fi, 1)

const submitTool = async () => {
  toolFormError.value = ''
  if (!toolForm.name.trim()) { toolFormError.value = '請填寫工具名稱'; return }
  if (!toolForm.cid)         { toolFormError.value = '請選擇分類';     return }
  savingTool.value = true
  const endpoint = isEditMode.value ? '/api/admin/update_tool.php' : '/api/admin/add_tool.php'
  try {
    const res = await axios.post(endpoint, {
      token: getToken(), id: toolForm.id,
      name: toolForm.name.trim(), logo_url: toolForm.logo_url.trim(),
      website_url: toolForm.website_url.trim(), video_url: toolForm.video_url.trim(),
      cid: toolForm.cid, description: toolForm.description.trim(),
      pricing_plans: JSON.stringify(toolForm.pricing)
    })
    const toolId = isEditMode.value ? toolForm.id : res.data.id
    await axios.post('/api/admin/update_tool_tags.php', { token: getToken(), tool_id: toolId, tag_ids: toolForm.tag_ids })
    showToolForm.value = false
    await fetchTools()
    await fetchStats()
    if (tagsLoaded.value) await fetchAllTags()
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
  } catch (e) { alert(e.response?.data?.error || '刪除失敗') }
}

// ── Reviews ────────────────────────────────────────────
const deleteReview = async (review) => {
  if (!confirm('確定要刪除這則評論？')) return
  try {
    await axios.post('/api/admin/delete_review.php', { token: getToken(), id: review.review_id })
    reviews.value = reviews.value.filter(r => r.review_id !== review.review_id)
    selectedReviewIds.value = selectedReviewIds.value.filter(id => id !== review.review_id)
    stats.reviews = Math.max(0, stats.reviews - 1)
    if (detailReview.value?.review_id === review.review_id) detailReview.value = null
  } catch (e) { alert(e.response?.data?.error || '刪除失敗') }
}

const deleteSelectedReviews = async () => {
  const ids = selectedReviewIds.value
  if (ids.length === 0) return
  if (!confirm(`確定要刪除選取的 ${ids.length} 則評論？`)) return
  bulkDeleting.value = true
  try {
    await axios.post('/api/admin/delete_reviews.php', { token: getToken(), ids })
    reviews.value = reviews.value.filter(r => !ids.includes(r.review_id))
    stats.reviews = Math.max(0, stats.reviews - ids.length)
    selectedReviewIds.value = []
  } catch (e) { alert(e.response?.data?.error || '刪除失敗') }
  finally { bulkDeleting.value = false }
}

const toggleReviewSelection = (id) => {
  const idx = selectedReviewIds.value.indexOf(id)
  if (idx === -1) selectedReviewIds.value.push(id)
  else selectedReviewIds.value.splice(idx, 1)
}

const allPagedReviewsSelected = computed(() =>
  pagedReviews.value.length > 0 && pagedReviews.value.every(r => selectedReviewIds.value.includes(r.review_id))
)

const togglePageSelection = () => {
  if (allPagedReviewsSelected.value) {
    const pageIds = pagedReviews.value.map(r => r.review_id)
    selectedReviewIds.value = selectedReviewIds.value.filter(id => !pageIds.includes(id))
  } else {
    const newIds = pagedReviews.value.map(r => r.review_id).filter(id => !selectedReviewIds.value.includes(id))
    selectedReviewIds.value = [...selectedReviewIds.value, ...newIds]
  }
}

// ── Token Usage ────────────────────────────────────────
const tokenLogs    = ref([])
const tokenTotals  = ref({ calls: 0, prompt: 0, completion: 0, total: 0 })
const tokenToday   = ref({ calls: 0, prompt: 0, completion: 0, total: 0 })
const tokenLoading = ref(false)
const tokenLoaded  = ref(false)

const fetchTokenUsage = async () => {
  tokenLoading.value = true
  try {
    const res = await axios.post('/api/admin/get_token_usage.php', { token: getToken() })
    tokenLogs.value   = res.data.logs   || []
    tokenTotals.value = res.data.totals || {}
    tokenToday.value  = res.data.today  || {}
    tokenLoaded.value = true
  } catch (e) {
  } finally {
    tokenLoading.value = false
  }
}

// ── Tags (used in tool form) ───────────────────────────
const allTags    = ref([])
const tagsLoaded = ref(false)
const newTagName = ref('')
const addingTag  = ref(false)

// ── Categories (admin management) ──────────────────────
const allCategories    = ref([])
const categoriesLoaded = ref(false)
const newCategoryName  = ref('')
const newCategoryDesc  = ref('')
const addingCategory   = ref(false)

// ── Tool news (最新消息) ────────────────────────────────
const allNews        = ref([])
const newsLoaded     = ref(false)
const dismissingNewsId = ref(null)

// ── Helpers ────────────────────────────────────────────
const getToken = () => localStorage.getItem('jwt_token') || ''

const fetchStats = async () => {
  try { const res = await axios.post('/api/admin/get_stats.php', { token: getToken() }); Object.assign(stats, res.data) } catch (e) { console.error('[fetchStats]', e) }
}
const fetchTools = async () => {
  try { const res = await axios.post('/api/admin/get_all_tools.php', { token: getToken() }); tools.value = res.data.tools || [] } catch (e) { console.error('[fetchTools]', e) }
}
const fetchCategories = async () => {
  try { const res = await axios.get('/api/get_categories.php'); categories.value = res.data || [] } catch (e) { console.error('[fetchCategories]', e) }
}
const fetchReviews = async () => {
  try { const res = await axios.post('/api/admin/get_all_reviews.php', { token: getToken() }); reviews.value = res.data.reviews || []; reviewsLoaded.value = true } catch (e) { console.error('[fetchReviews]', e) }
}
const fetchUsers = async () => {
  try { const res = await axios.post('/api/admin/get_users.php', { token: getToken() }); users.value = res.data.users || []; usersLoaded.value = true } catch (e) { console.error('[fetchUsers]', e) }
}

const toggleUserRole = async (user) => {
  const newRole = user.role === 1 ? 0 : 1
  const action = newRole === 1 ? '設為管理員' : '設為一般用戶'
  if (!confirm(`確定要將「${user.username}」${action}？`)) return
  updatingUid.value = user.uid
  try {
    await axios.post('/api/admin/update_user_role.php', { token: getToken(), uid: user.uid, role: newRole })
    user.role = newRole
  } catch (e) { alert(e.response?.data?.error || '操作失敗') }
  finally { updatingUid.value = null }
}
const fetchAllTags = async () => {
  try { const res = await axios.post('/api/admin/get_all_tags.php', { token: getToken() }); allTags.value = res.data.tags || []; tagsLoaded.value = true } catch (e) { console.error('[fetchAllTags]', e) }
}

const addTag = async () => {
  const name = newTagName.value.trim()
  if (!name) return
  addingTag.value = true
  try {
    await axios.post('/api/admin/add_tag.php', { token: getToken(), name })
    newTagName.value = ''
    await fetchAllTags()
    await fetchStats()
  } catch (e) { alert(e.response?.data?.error || '新增失敗') }
  finally { addingTag.value = false }
}

const deleteTag = async (tag) => {
  if (!confirm(`確定要刪除標籤「${tag.name}」？相關工具的此標籤也會一併移除。`)) return
  try {
    await axios.post('/api/admin/delete_tag.php', { token: getToken(), id: tag.id })
    await fetchAllTags()
    await fetchStats()
  } catch (e) { alert(e.response?.data?.error || '刪除失敗') }
}

const fetchAllCategories = async () => {
  try { const res = await axios.post('/api/admin/get_all_categories.php', { token: getToken() }); allCategories.value = res.data.categories || []; categoriesLoaded.value = true } catch (e) { console.error('[fetchAllCategories]', e) }
}

const addCategory = async () => {
  const name = newCategoryName.value.trim()
  if (!name) return
  addingCategory.value = true
  try {
    await axios.post('/api/admin/add_category.php', { token: getToken(), name, description: newCategoryDesc.value.trim() })
    newCategoryName.value = ''
    newCategoryDesc.value = ''
    await fetchAllCategories()
    await fetchCategories()
    await fetchStats()
  } catch (e) { alert(e.response?.data?.error || '新增失敗') }
  finally { addingCategory.value = false }
}

const deleteCategory = async (category) => {
  if (!confirm(`確定要刪除分類「${category.name}」？相關工具將變為「未分類」。`)) return
  try {
    await axios.post('/api/admin/delete_category.php', { token: getToken(), id: category.id })
    await fetchAllCategories()
    await fetchCategories()
    await fetchStats()
    await fetchTools()
  } catch (e) { alert(e.response?.data?.error || '刪除失敗') }
}

// ── Tool news (最新消息) ────────────────────────────────
const fetchNews = async () => {
  try { const res = await axios.post('/api/admin/get_tool_news.php', { token: getToken() }); allNews.value = res.data.news || []; newsLoaded.value = true } catch (e) { console.error('[fetchNews]', e) }
}

const dismissNews = async (newsId) => {
  if (!confirm('確定要忽略這則消息？')) return
  dismissingNewsId.value = newsId
  try {
    await axios.post('/api/admin/dismiss_tool_news.php', { token: getToken(), id: newsId })
    allNews.value = allNews.value.filter(n => n.id !== newsId)
  } catch (e) { alert(e.response?.data?.error || '操作失敗') }
  finally { dismissingNewsId.value = null }
}

const switchTab = async (tab) => {
  activeTab.value = tab
  if (tab === 'tags' && !tagsLoaded.value)       await fetchAllTags()
  if (tab === 'tags' && !categoriesLoaded.value) await fetchAllCategories()
  if (tab === 'reviews' && !reviewsLoaded.value) await fetchReviews()
  if (tab === 'users'   && !usersLoaded.value)   await fetchUsers()
  if (tab === 'tokens')                          await fetchTokenUsage()
  if (tab === 'news'    && !newsLoaded.value)    await fetchNews()
}

onMounted(async () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  if (Number(user.role) !== 1) { router.push('/'); return }
  await Promise.all([fetchStats(), fetchTools(), fetchCategories(), fetchAllTags()])
})

const starsArray = (n) => Array.from({ length: 5 }, (_, i) => i < n)
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900 pb-20">

    <!-- ── Page Header ── -->
    <div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-16 z-30 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 py-5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
              <LayoutDashboard class="w-6 h-6 text-primary" />
            </div>
            <div>
              <h1 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 leading-tight">管理後台</h1>
              <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">AI Hub Admin Panel</p>
            </div>
          </div>
          <!-- Tabs -->
          <div class="flex gap-1 bg-slate-100 dark:bg-slate-700 rounded-xl p-1">
            <button
              v-for="tab in [
                { id: 'tools',   label: '工具管理', icon: Wrench },
                { id: 'tags',    label: '分類與標籤', icon: Tag },
                { id: 'reviews', label: '評論管理', icon: MessageSquare },
                { id: 'users',   label: '用戶列表', icon: Users },
                { id: 'tokens',  label: 'Token 用量', icon: Zap },
                { id: 'news',    label: '最新消息', icon: Bell }
              ]"
              :key="tab.id"
              @click="switchTab(tab.id)"
              :class="activeTab === tab.id
                ? 'bg-white dark:bg-slate-600 text-primary shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
              class="flex items-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-bold transition-all"
            >
              <component :is="tab.icon" class="w-4 h-4" />
              <span class="hidden sm:inline">{{ tab.label }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- ═══════════════ TOOLS TAB ═══════════════ -->
      <div v-if="activeTab === 'tools'">
        <div class="flex flex-col sm:flex-row gap-3 mb-6">
          <div class="relative flex-1">
            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
            <input
              v-model="toolSearch"
              type="text"
              placeholder="搜尋工具名稱或分類..."
              class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-base transition-all"
            >
          </div>
          <button
            @click="fetchTools"
            class="flex items-center gap-2 px-5 py-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary transition-all cursor-pointer shadow-sm whitespace-nowrap"
          >
            <RefreshCw class="w-4 h-4" /> 刷新
          </button>
          <button
            @click="openAddForm"
            class="flex items-center gap-2 px-6 py-3.5 bg-primary hover:bg-primary/90 text-white rounded-xl font-bold text-base transition-all shadow-lg shadow-primary/20 border-none cursor-pointer whitespace-nowrap"
          >
            <Plus class="w-5 h-5" /> 新增工具
          </button>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                  <th class="text-left px-6 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">工具</th>
                  <th class="text-left px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide hidden md:table-cell">分類</th>
                  <th class="text-center px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide hidden lg:table-cell">評分</th>
                  <th class="text-center px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide hidden lg:table-cell">評論</th>
                  <th class="text-center px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide hidden lg:table-cell">收藏</th>
                  <th class="text-right px-6 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">操作</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="tool in pagedTools"
                  :key="tool.id"
                  class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-blue-50/40 dark:hover:bg-slate-700/40 transition-colors"
                >
                  <td class="px-6 py-5">
                    <div class="flex items-center gap-4">
                      <img
                        :src="tool.logo_url || `https://api.dicebear.com/7.x/identicon/svg?seed=${encodeURIComponent(tool.name)}`"
                        :alt="tool.name"
                        class="w-11 h-11 rounded-xl object-contain bg-slate-50 dark:bg-slate-700 border border-slate-100 dark:border-slate-600 p-1 shrink-0"
                        @error="e => e.target.src = `https://api.dicebear.com/7.x/identicon/svg?seed=${encodeURIComponent(tool.name)}`"
                      >
                      <div>
                        <p class="font-bold text-base text-slate-900 dark:text-slate-100">{{ tool.name }}</p>
                        <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5 max-w-[200px] truncate">{{ tool.description }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-5 hidden md:table-cell">
                    <span class="px-3 py-1.5 bg-primary/10 text-primary rounded-full text-sm font-bold">
                      {{ tool.category_name || '未分類' }}
                    </span>
                  </td>
                  <td class="px-4 py-5 text-center hidden lg:table-cell">
                    <span class="font-black text-lg text-amber-500">{{ tool.rating.toFixed(1) }}</span>
                  </td>
                  <td class="px-4 py-5 text-center hidden lg:table-cell font-semibold text-base text-slate-600 dark:text-slate-300">{{ tool.review_count }}</td>
                  <td class="px-4 py-5 text-center hidden lg:table-cell font-semibold text-base text-slate-600 dark:text-slate-300">{{ tool.favorite_count }}</td>
                  <td class="px-6 py-5">
                    <div class="flex items-center justify-end gap-2">
                      <button @click="openEditForm(tool)"
                        class="p-2.5 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-xl transition-colors border-none bg-transparent cursor-pointer" title="編輯">
                        <Pencil class="w-4 h-4" />
                      </button>
                      <button @click="deleteTool(tool)"
                        class="p-2.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors border-none bg-transparent cursor-pointer" title="刪除">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="pagedTools.length === 0">
                  <td colspan="6" class="px-6 py-20 text-center text-base text-slate-400 dark:text-slate-500 font-medium">
                    {{ toolSearch ? '找不到符合條件的工具' : '尚無工具資料' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- Pagination -->
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/20">
            <div class="flex items-center gap-2 text-sm font-medium text-slate-500 dark:text-slate-400">
              <span>每頁</span>
              <select v-model.number="toolsPageSize" @change="toolsPage = 1"
                class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-sm outline-none cursor-pointer">
                <option v-for="n in [5, 10, 20, 50]" :key="n" :value="n">{{ n }}</option>
              </select>
              <span>筆・共 <strong class="text-slate-700 dark:text-slate-200">{{ filteredTools.length }}</strong> 筆</span>
            </div>
            <div class="flex items-center gap-2">
              <button @click="toolsPage--" :disabled="toolsPage === 1"
                class="px-4 py-2 rounded-xl border text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary bg-transparent">
                ← 上一頁
              </button>
              <span class="px-4 py-2 text-sm font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl">
                {{ toolsPage }} / {{ toolsPageCount }}
              </span>
              <button @click="toolsPage++" :disabled="toolsPage === toolsPageCount"
                class="px-4 py-2 rounded-xl border text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary bg-transparent">
                下一頁 →
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════ TAGS & CATEGORIES TAB ═══════════════ -->
      <div v-else-if="activeTab === 'tags'" class="space-y-10">

        <!-- Categories -->
        <div>
          <h3 class="text-sm font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">分類管理</h3>
          <div class="flex flex-col sm:flex-row gap-3 mb-6">
            <div class="relative flex-1">
              <Package class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
              <input
                v-model="newCategoryName"
                type="text"
                placeholder="輸入新分類名稱..."
                @keyup.enter="addCategory"
                class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-base transition-all"
              >
            </div>
            <div class="relative flex-1">
              <input
                v-model="newCategoryDesc"
                type="text"
                placeholder="分類描述（選填）..."
                @keyup.enter="addCategory"
                class="w-full px-4 py-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-base transition-all"
              >
            </div>
            <button
              @click="addCategory"
              :disabled="addingCategory || !newCategoryName.trim()"
              class="flex items-center gap-2 px-6 py-3.5 bg-primary hover:bg-primary/90 disabled:bg-slate-300 text-white rounded-xl font-bold text-base transition-all shadow-lg shadow-primary/20 border-none cursor-pointer whitespace-nowrap"
            >
              <Loader2 v-if="addingCategory" class="w-5 h-5 animate-spin" />
              <Plus v-else class="w-5 h-5" /> 新增分類
            </button>
          </div>

          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div v-if="allCategories.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
              <div
                v-for="cat in allCategories" :key="cat.id"
                class="flex items-center justify-between gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-700/60 border border-slate-100 dark:border-slate-600 rounded-xl"
              >
                <div class="min-w-0">
                  <p class="font-bold text-base text-slate-800 dark:text-slate-200 truncate">{{ cat.name }}</p>
                  <p class="text-sm text-slate-400 dark:text-slate-500 truncate">{{ cat.description || `${cat.tool_count} 個工具` }}</p>
                </div>
                <button @click="deleteCategory(cat)"
                  class="p-2.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors border-none bg-transparent cursor-pointer shrink-0" title="刪除分類">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
            <div v-else class="px-6 py-20 text-center text-base font-medium text-slate-400 dark:text-slate-500">
              尚無分類資料
            </div>
          </div>
        </div>

        <!-- Tags -->
        <div>
          <h3 class="text-sm font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">標籤管理</h3>
          <div class="flex flex-col sm:flex-row gap-3 mb-6">
            <div class="relative flex-1">
              <Tag class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
              <input
                v-model="newTagName"
                type="text"
                placeholder="輸入新標籤名稱..."
                @keyup.enter="addTag"
                class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-base transition-all"
              >
            </div>
            <button
              @click="addTag"
              :disabled="addingTag || !newTagName.trim()"
              class="flex items-center gap-2 px-6 py-3.5 bg-primary hover:bg-primary/90 disabled:bg-slate-300 text-white rounded-xl font-bold text-base transition-all shadow-lg shadow-primary/20 border-none cursor-pointer whitespace-nowrap"
            >
              <Loader2 v-if="addingTag" class="w-5 h-5 animate-spin" />
              <Plus v-else class="w-5 h-5" /> 新增標籤
            </button>
          </div>

          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div v-if="allTags.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
              <div
                v-for="tag in allTags" :key="tag.id"
                class="flex items-center justify-between gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-700/60 border border-slate-100 dark:border-slate-600 rounded-xl"
              >
                <div class="min-w-0">
                  <p class="font-bold text-base text-slate-800 dark:text-slate-200 truncate">{{ tag.name }}</p>
                  <p class="text-sm text-slate-400 dark:text-slate-500">{{ tag.tool_count }} 個工具使用中</p>
                </div>
                <button @click="deleteTag(tag)"
                  class="p-2.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors border-none bg-transparent cursor-pointer shrink-0" title="刪除標籤">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
            <div v-else class="px-6 py-20 text-center text-base font-medium text-slate-400 dark:text-slate-500">
              尚無標籤資料
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════ REVIEWS TAB ═══════════════ -->
      <div v-else-if="activeTab === 'reviews'">
        <div class="flex flex-col sm:flex-row gap-3 mb-6">
          <div class="relative flex-1">
            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
            <input
              v-model="reviewSearch"
              type="text"
              placeholder="搜尋工具名稱、用戶或評論內容..."
              class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-base transition-all"
            >
          </div>
          <select v-model.number="reviewStarFilter"
            class="px-4 py-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-base font-bold appearance-none cursor-pointer">
            <option :value="0">全部星等</option>
            <option v-for="n in [5, 4, 3, 2, 1]" :key="n" :value="n">{{ n }} 星</option>
          </select>
          <select v-model="reviewSort"
            class="px-4 py-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-base font-bold appearance-none cursor-pointer">
            <option value="newest">最新優先</option>
            <option value="oldest">最舊優先</option>
            <option value="stars_desc">星等高到低</option>
            <option value="stars_asc">星等低到高</option>
          </select>
        </div>

        <!-- Bulk action bar -->
        <div v-if="selectedReviewIds.length > 0" class="flex items-center justify-between gap-3 mb-4 px-5 py-3 bg-primary/5 border border-primary/20 rounded-xl">
          <span class="text-sm font-bold text-primary">已選取 {{ selectedReviewIds.length }} 則評論</span>
          <button @click="deleteSelectedReviews" :disabled="bulkDeleting"
            class="flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white rounded-xl font-bold text-sm transition-all border-none cursor-pointer">
            <Loader2 v-if="bulkDeleting" class="w-4 h-4 animate-spin" />
            <Trash2 v-else class="w-4 h-4" /> 批次刪除
          </button>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                  <th class="px-6 py-4 w-10">
                    <input type="checkbox" :checked="allPagedReviewsSelected" @change="togglePageSelection"
                      class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary cursor-pointer">
                  </th>
                  <th class="text-left px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">用戶 / 工具</th>
                  <th class="text-left px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">評論內容</th>
                  <th class="text-center px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide hidden sm:table-cell">評分</th>
                  <th class="text-center px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide hidden md:table-cell">日期</th>
                  <th class="text-right px-6 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">操作</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="review in pagedReviews"
                  :key="review.review_id"
                  class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-blue-50/40 dark:hover:bg-slate-700/40 transition-colors"
                >
                  <td class="px-6 py-5">
                    <input type="checkbox" :checked="selectedReviewIds.includes(review.review_id)" @change="toggleReviewSelection(review.review_id)"
                      class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary cursor-pointer">
                  </td>
                  <td class="px-4 py-5">
                    <p class="font-bold text-base text-slate-800 dark:text-slate-200">{{ review.username }}</p>
                    <p class="text-sm font-semibold text-primary mt-0.5">{{ review.tool_name }}</p>
                  </td>
                  <td class="px-4 py-5 max-w-[300px]">
                    <p class="text-base font-medium text-slate-600 dark:text-slate-300 line-clamp-2">{{ review.comment || '（無文字評論）' }}</p>
                  </td>
                  <td class="px-4 py-5 text-center hidden sm:table-cell">
                    <div class="flex items-center justify-center gap-0.5">
                      <Star
                        v-for="(filled, i) in starsArray(review.stars)" :key="i"
                        :class="filled ? 'text-amber-400 fill-amber-400' : 'text-slate-200 dark:text-slate-600'"
                        class="w-4 h-4"
                      />
                    </div>
                  </td>
                  <td class="px-4 py-5 text-center hidden md:table-cell text-sm font-semibold text-slate-400 dark:text-slate-500">
                    {{ (review.created_at || '').slice(0, 10) }}
                  </td>
                  <td class="px-6 py-5">
                    <div class="flex justify-end gap-1">
                      <button @click="detailReview = review"
                        class="p-2.5 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-xl transition-colors border-none bg-transparent cursor-pointer" title="查看完整內容">
                        <Eye class="w-4 h-4" />
                      </button>
                      <button @click="deleteReview(review)"
                        class="p-2.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors border-none bg-transparent cursor-pointer" title="刪除評論">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="pagedReviews.length === 0">
                  <td colspan="6" class="px-6 py-20 text-center text-base font-medium text-slate-400 dark:text-slate-500">
                    {{ reviewSearch ? '找不到符合條件的評論' : '尚無評論資料' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- Pagination -->
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/20">
            <div class="flex items-center gap-2 text-sm font-medium text-slate-500 dark:text-slate-400">
              <span>每頁</span>
              <select v-model.number="reviewsPageSize" @change="reviewsPage = 1"
                class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-sm outline-none cursor-pointer">
                <option v-for="n in [5, 10, 20, 50]" :key="n" :value="n">{{ n }}</option>
              </select>
              <span>筆・共 <strong class="text-slate-700 dark:text-slate-200">{{ filteredReviews.length }}</strong> 筆</span>
            </div>
            <div class="flex items-center gap-2">
              <button @click="reviewsPage--" :disabled="reviewsPage === 1"
                class="px-4 py-2 rounded-xl border text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary bg-transparent">
                ← 上一頁
              </button>
              <span class="px-4 py-2 text-sm font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl">
                {{ reviewsPage }} / {{ reviewsPageCount }}
              </span>
              <button @click="reviewsPage++" :disabled="reviewsPage === reviewsPageCount"
                class="px-4 py-2 rounded-xl border text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary bg-transparent">
                下一頁 →
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════ USERS TAB ═══════════════ -->
      <div v-else-if="activeTab === 'users'">
        <div class="flex gap-3 mb-6">
          <div class="relative flex-1">
            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
            <input
              v-model="userSearch"
              type="text"
              placeholder="搜尋用戶名稱..."
              class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-base transition-all"
            >
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                  <th class="text-left px-6 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">UID</th>
                  <th class="text-left px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">用戶名稱</th>
                  <th class="text-center px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">角色</th>
                  <th class="text-center px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide hidden sm:table-cell">評論數</th>
                  <th class="text-center px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide hidden sm:table-cell">收藏數</th>
                  <th class="text-right px-6 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">操作</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="user in pagedUsers"
                  :key="user.uid"
                  class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-blue-50/40 dark:hover:bg-slate-700/40 transition-colors"
                >
                  <td class="px-6 py-5 text-slate-400 dark:text-slate-500 font-mono text-sm font-bold">#{{ user.uid }}</td>
                  <td class="px-4 py-5 font-bold text-base text-slate-800 dark:text-slate-200">{{ user.username }}</td>
                  <td class="px-4 py-5 text-center">
                    <span
                      :class="user.role === 9
                        ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400'
                        : user.role === 1
                          ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
                          : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'"
                      class="px-3 py-1.5 rounded-full text-sm font-bold"
                    >
                      {{ user.role === 9 ? '超級管理員' : user.role === 1 ? '管理員' : '一般用戶' }}
                    </span>
                  </td>
                  <td class="px-4 py-5 text-center font-bold text-base text-slate-600 dark:text-slate-300 hidden sm:table-cell">{{ user.review_count }}</td>
                  <td class="px-4 py-5 text-center font-bold text-base text-slate-600 dark:text-slate-300 hidden sm:table-cell">{{ user.favorite_count }}</td>
                  <td class="px-6 py-5 text-right">
                    <button
                      v-if="user.role !== 9 && user.uid !== currentUid"
                      @click="toggleUserRole(user)"
                      :disabled="updatingUid === user.uid"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-bold border transition-all disabled:opacity-50 cursor-pointer"
                      :class="user.role === 1
                        ? 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary bg-transparent'
                        : 'border-primary/30 text-primary hover:bg-primary/10 bg-transparent'"
                    >
                      <Loader2 v-if="updatingUid === user.uid" class="w-3.5 h-3.5 animate-spin" />
                      {{ user.role === 1 ? '設為一般用戶' : '設為管理員' }}
                    </button>
                    <span v-else class="text-sm text-slate-300 dark:text-slate-600">—</span>
                  </td>
                </tr>
                <tr v-if="pagedUsers.length === 0">
                  <td colspan="6" class="px-6 py-20 text-center text-base font-medium text-slate-400 dark:text-slate-500">
                    {{ userSearch ? '找不到符合條件的用戶' : '尚無用戶資料' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- Pagination -->
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/20">
            <div class="flex items-center gap-2 text-sm font-medium text-slate-500 dark:text-slate-400">
              <span>每頁</span>
              <select v-model.number="usersPageSize" @change="usersPage = 1"
                class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-sm outline-none cursor-pointer">
                <option v-for="n in [5, 10, 20, 50]" :key="n" :value="n">{{ n }}</option>
              </select>
              <span>筆・共 <strong class="text-slate-700 dark:text-slate-200">{{ filteredUsers.length }}</strong> 筆</span>
            </div>
            <div class="flex items-center gap-2">
              <button @click="usersPage--" :disabled="usersPage === 1"
                class="px-4 py-2 rounded-xl border text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary bg-transparent">
                ← 上一頁
              </button>
              <span class="px-4 py-2 text-sm font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl">
                {{ usersPage }} / {{ usersPageCount }}
              </span>
              <button @click="usersPage++" :disabled="usersPage === usersPageCount"
                class="px-4 py-2 rounded-xl border text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary bg-transparent">
                下一頁 →
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════ TOKENS TAB ═══════════════ -->
      <div v-else-if="activeTab === 'tokens'">
        <div class="flex justify-end mb-6">
          <button
            @click="fetchTokenUsage"
            :disabled="tokenLoading"
            class="flex items-center gap-2 px-5 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary transition-all disabled:opacity-50 cursor-pointer shadow-sm"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': tokenLoading }" />
            刷新資料
          </button>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
            <h3 class="text-sm font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">今日</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500 mb-1">呼叫次數</p>
                <p class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ tokenToday.calls.toLocaleString() }}</p>
              </div>
              <div>
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500 mb-1">合計 Tokens</p>
                <p class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ tokenToday.total.toLocaleString() }}</p>
              </div>
              <div>
                <p class="text-xs font-bold text-indigo-400 mb-1">輸入 Tokens</p>
                <p class="text-lg font-black text-indigo-500 dark:text-indigo-400">{{ tokenToday.prompt.toLocaleString() }}</p>
              </div>
              <div>
                <p class="text-xs font-bold text-emerald-400 mb-1">輸出 Tokens</p>
                <p class="text-lg font-black text-emerald-600 dark:text-emerald-400">{{ tokenToday.completion.toLocaleString() }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
            <h3 class="text-sm font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">累計</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500 mb-1">呼叫次數</p>
                <p class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ tokenTotals.calls.toLocaleString() }}</p>
              </div>
              <div>
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500 mb-1">合計 Tokens</p>
                <p class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ tokenTotals.total.toLocaleString() }}</p>
              </div>
              <div>
                <p class="text-xs font-bold text-indigo-400 mb-1">輸入 Tokens</p>
                <p class="text-lg font-black text-indigo-500 dark:text-indigo-400">{{ tokenTotals.prompt.toLocaleString() }}</p>
              </div>
              <div>
                <p class="text-xs font-bold text-emerald-400 mb-1">輸出 Tokens</p>
                <p class="text-lg font-black text-emerald-600 dark:text-emerald-400">{{ tokenTotals.completion.toLocaleString() }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Log table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                  <th class="text-left px-6 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">時間</th>
                  <th class="text-left px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">用戶</th>
                  <th class="text-right px-4 py-4 text-sm font-bold text-indigo-400 uppercase tracking-wide">輸入</th>
                  <th class="text-right px-4 py-4 text-sm font-bold text-emerald-500 uppercase tracking-wide">輸出</th>
                  <th class="text-right px-4 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">合計</th>
                  <th class="text-left px-6 py-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide hidden lg:table-cell">模型</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="tokenLoading && tokenLogs.length === 0">
                  <td colspan="6" class="px-6 py-20 text-center text-slate-400">
                    <Loader2 class="w-7 h-7 animate-spin mx-auto mb-3" />
                    <p class="text-base font-medium">載入中...</p>
                  </td>
                </tr>
                <tr v-else-if="tokenLogs.length === 0">
                  <td colspan="6" class="px-6 py-20 text-center text-base font-medium text-slate-400 dark:text-slate-500">
                    尚無 Token 使用紀錄
                  </td>
                </tr>
                <tr
                  v-for="log in tokenLogs"
                  :key="log.log_id"
                  class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-blue-50/40 dark:hover:bg-slate-700/40 transition-colors"
                >
                  <td class="px-6 py-4 text-sm font-mono font-semibold text-slate-400 dark:text-slate-500 whitespace-nowrap">{{ log.created_at }}</td>
                  <td class="px-4 py-4 font-bold text-base text-slate-700 dark:text-slate-200">{{ log.username }}</td>
                  <td class="px-4 py-4 text-right font-mono font-bold text-base text-indigo-500 dark:text-indigo-400">{{ log.prompt_tokens.toLocaleString() }}</td>
                  <td class="px-4 py-4 text-right font-mono font-bold text-base text-emerald-600 dark:text-emerald-400">{{ log.completion_tokens.toLocaleString() }}</td>
                  <td class="px-4 py-4 text-right font-mono font-black text-base text-slate-800 dark:text-slate-200">{{ log.total_tokens.toLocaleString() }}</td>
                  <td class="px-6 py-4 hidden lg:table-cell">
                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded-lg text-sm font-mono font-semibold">{{ log.model }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════ NEWS TAB ═══════════════ -->
      <div v-else-if="activeTab === 'news'">
        <div class="flex items-center justify-between mb-6">
          <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
            自動化收集流程整理出的工具情報，請自行查證後再前往「工具管理」新增或更新。
          </p>
          <button
            @click="fetchNews"
            class="flex items-center gap-2 px-5 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary transition-all cursor-pointer shadow-sm whitespace-nowrap"
          >
            <RefreshCw class="w-4 h-4" /> 刷新
          </button>
        </div>

        <div v-if="!newsLoaded" class="py-20 text-center text-slate-400">
          <Loader2 class="w-7 h-7 animate-spin mx-auto mb-3" />
          <p class="text-base font-medium">載入中...</p>
        </div>
        <div v-else-if="allNews.length === 0" class="py-20 text-center text-base font-medium text-slate-400 dark:text-slate-500">
          目前沒有新的消息
        </div>
        <div v-else class="space-y-4 max-w-2xl">
          <div
            v-for="item in allNews"
            :key="item.id"
            class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5"
          >
            <div class="flex items-start gap-3">
              <img :src="item.avatar_url" class="w-11 h-11 rounded-full object-contain bg-slate-50 dark:bg-slate-700 border border-slate-100 dark:border-slate-700 flex-shrink-0" alt="">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">{{ item.author_name }}</span>
                  <component :is="item.platform === 'twitter' ? Twitter : Globe" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" />
                  <span class="text-slate-400 dark:text-slate-500 text-xs">{{ item.author_handle }}</span>
                  <span class="text-slate-300 dark:text-slate-600 text-xs">·</span>
                  <span class="text-slate-400 dark:text-slate-500 text-xs">{{ item.posted_at }}</span>
                </div>

                <p class="text-sm text-slate-700 dark:text-slate-200 leading-relaxed mt-1.5">{{ item.content }}</p>

                <div class="flex flex-wrap items-center gap-1.5 mt-3">
                  <span
                    v-for="tag in item.tags"
                    :key="tag"
                    :class="item.type === 'new_tool'
                      ? 'bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400'
                      : 'bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400'"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold"
                  >
                    {{ tag }}
                  </span>
                  <span v-if="item.related_tool_name" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded-lg text-xs font-bold">
                    相關工具：{{ item.related_tool_name }}
                  </span>
                </div>

                <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                  <a :href="item.source_url" target="_blank" rel="noopener" class="flex items-center gap-1 text-xs font-bold text-slate-400 dark:text-slate-500 hover:text-primary transition-colors">
                    <ExternalLink class="w-3.5 h-3.5" /> 查看原文
                  </a>
                  <button
                    @click="dismissNews(item.id)"
                    :disabled="dismissingNewsId === item.id"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded-lg text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all disabled:opacity-50 cursor-pointer"
                  >
                    <X class="w-3.5 h-3.5" /> 忽略
                  </button>
                </div>
              </div>
            </div>
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

            <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
              <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">
                {{ isEditMode ? '編輯工具' : '新增 AI 工具' }}
              </h2>
              <button @click="showToolForm = false"
                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors border-none bg-transparent cursor-pointer">
                <X class="w-5 h-5" />
              </button>
            </div>

            <div class="p-6 space-y-6">
              <div v-if="toolFormError" class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl border border-red-100 dark:border-red-800 text-sm font-semibold">
                <AlertTriangle class="w-5 h-5 shrink-0" />
                {{ toolFormError }}
              </div>

              <!-- Basic Info -->
              <div>
                <h3 class="text-sm font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">基本資訊</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="sm:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5">工具名稱 *</label>
                    <input v-model="toolForm.name" type="text" placeholder="例如：ChatGPT"
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-base transition-all">
                  </div>
                  <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5">分類 *</label>
                    <select v-model="toolForm.cid"
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-base transition-all appearance-none cursor-pointer">
                      <option value="" disabled>請選擇分類</option>
                      <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5">Logo 圖片網址</label>
                    <input v-model="toolForm.logo_url" type="url" placeholder="https://..."
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-base transition-all">
                  </div>
                  <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5">官方網站</label>
                    <input v-model="toolForm.website_url" type="url" placeholder="https://..."
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-base transition-all">
                  </div>
                  <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5">介紹影片網址</label>
                    <input v-model="toolForm.video_url" type="url" placeholder="https://youtube.com/..."
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-base transition-all">
                  </div>
                  <div class="sm:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5">工具簡介</label>
                    <textarea v-model="toolForm.description" rows="3" placeholder="簡短描述這個工具的功能與特色..."
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-base transition-all resize-none"></textarea>
                  </div>
                </div>
              </div>

              <!-- Tags -->
              <div>
                <h3 class="text-sm font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">功能標籤</h3>
                <div v-if="allTags.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                  <label v-for="tag in allTags" :key="tag.id"
                    class="flex items-center gap-2 cursor-pointer group p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <input type="checkbox" :value="tag.id" v-model="toolForm.tag_ids"
                      class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary">
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300 group-hover:text-primary transition-colors">{{ tag.name }}</span>
                  </label>
                </div>
                <div v-else class="flex items-center gap-2 text-sm font-medium text-slate-400 dark:text-slate-500">
                  <Loader2 class="w-4 h-4 animate-spin" />
                  <span>載入標籤中...</span>
                </div>
              </div>

              <!-- Pricing -->
              <div>
                <h3 class="text-sm font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">定價方案</h3>
                <div class="grid grid-cols-2 gap-3 mb-4">
                  <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">幣別</label>
                    <select v-model="toolForm.pricing.currency"
                      class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-sm appearance-none cursor-pointer font-semibold">
                      <option value="USD">USD ($)</option>
                      <option value="EUR">EUR (€)</option>
                      <option value="TWD">TWD (NT$)</option>
                      <option value="JPY">JPY (¥)</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">收費類型</label>
                    <select v-model="toolForm.pricing.status"
                      class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 text-sm appearance-none cursor-pointer font-semibold">
                      <option value="Free">完全免費</option>
                      <option value="Freemium">部分免費（Freemium）</option>
                      <option value="Paid">付費</option>
                    </select>
                  </div>
                </div>
                <div class="space-y-3">
                  <div v-for="(plan, pi) in toolForm.pricing.plans" :key="pi"
                    class="p-4 bg-slate-50 dark:bg-slate-700/60 rounded-2xl border border-slate-200 dark:border-slate-600">
                    <div class="flex items-center justify-between mb-3">
                      <span class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wide">方案 {{ pi + 1 }}</span>
                      <button @click="removePlan(pi)"
                        class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors border-none bg-transparent cursor-pointer">
                        <X class="w-4 h-4" />
                      </button>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                      <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">方案名稱</label>
                        <input v-model="plan.name" type="text" placeholder="例如：Free、Pro"
                          class="w-full px-3 py-2 bg-white dark:bg-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-500 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 text-sm font-medium">
                      </div>
                      <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">價格（填 0 表示免費）</label>
                        <input v-model="plan.price" type="text" placeholder="0"
                          class="w-full px-3 py-2 bg-white dark:bg-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-500 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 text-sm font-medium">
                      </div>
                    </div>
                    <div>
                      <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">功能列表</label>
                      <div class="space-y-2">
                        <div v-for="(feat, fi) in plan.features" :key="fi" class="flex items-center gap-2">
                          <input v-model="plan.features[fi]" type="text" placeholder="功能描述..."
                            class="flex-1 px-3 py-1.5 bg-white dark:bg-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-500 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 text-sm font-medium">
                          <button @click="removeFeature(pi, fi)"
                            class="p-1 text-slate-400 hover:text-red-500 transition-colors border-none bg-transparent cursor-pointer">
                            <X class="w-3.5 h-3.5" />
                          </button>
                        </div>
                      </div>
                      <button @click="addFeature(pi)"
                        class="mt-2 text-xs font-bold text-primary hover:underline border-none bg-transparent cursor-pointer flex items-center gap-1">
                        <Plus class="w-3 h-3" /> 新增功能
                      </button>
                    </div>
                  </div>
                </div>
                <button @click="addPlan"
                  class="mt-3 w-full py-3 border-2 border-dashed border-slate-200 dark:border-slate-600 hover:border-primary dark:hover:border-primary text-slate-400 dark:text-slate-500 hover:text-primary rounded-2xl text-sm font-bold transition-all bg-transparent cursor-pointer flex items-center justify-center gap-2">
                  <Plus class="w-4 h-4" /> 新增定價方案
                </button>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700">
              <button @click="showToolForm = false"
                class="px-5 py-2.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl font-bold text-sm transition-all border border-slate-200 dark:border-slate-600 bg-transparent cursor-pointer">
                取消
              </button>
              <button @click="submitTool" :disabled="savingTool"
                class="flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-primary/90 disabled:bg-slate-300 text-white rounded-xl font-bold text-sm transition-all shadow-lg shadow-primary/20 border-none cursor-pointer">
                <Loader2 v-if="savingTool" class="w-4 h-4 animate-spin" />
                <Save v-else class="w-4 h-4" />
                {{ isEditMode ? '儲存變更' : '新增工具' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ═══════════════ REVIEW DETAIL MODAL ═══════════════ -->
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
          v-if="detailReview"
          class="fixed inset-0 z-[70] bg-slate-900/80 flex items-start justify-center p-4 sm:p-8 overflow-y-auto"
          @click.self="detailReview = null"
        >
          <div class="w-full max-w-lg bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-700 my-4">
            <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
              <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">評論詳情</h2>
              <button @click="detailReview = null"
                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors border-none bg-transparent cursor-pointer">
                <X class="w-5 h-5" />
              </button>
            </div>
            <div class="p-6 space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-bold text-base text-slate-800 dark:text-slate-200">{{ detailReview.username }}</p>
                  <router-link :to="`/tool/${detailReview.tool_id}`" target="_blank"
                    class="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline mt-0.5">
                    {{ detailReview.tool_name }} <ExternalLink class="w-3.5 h-3.5" />
                  </router-link>
                </div>
                <div class="flex items-center gap-0.5">
                  <Star
                    v-for="(filled, i) in starsArray(detailReview.stars)" :key="i"
                    :class="filled ? 'text-amber-400 fill-amber-400' : 'text-slate-200 dark:text-slate-600'"
                    class="w-5 h-5"
                  />
                </div>
              </div>
              <p class="text-base font-medium text-slate-600 dark:text-slate-300 whitespace-pre-wrap break-words">{{ detailReview.comment || '（無文字評論）' }}</p>
              <p class="text-sm font-semibold text-slate-400 dark:text-slate-500">{{ detailReview.created_at }}</p>
            </div>
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-700">
              <button @click="deleteReview(detailReview)"
                class="flex items-center gap-2 px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold text-sm transition-all border-none cursor-pointer">
                <Trash2 class="w-4 h-4" /> 刪除此評論
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
