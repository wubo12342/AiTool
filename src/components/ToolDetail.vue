<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, RouterLink, useRouter } from 'vue-router'
import axios from 'axios'
import {
  Star, ArrowLeft, Send, CheckCircle2, Bot,
  Heart, ExternalLink, Tag, DollarSign, MessageSquare
} from 'lucide-vue-next'
import ToolCard from './ToolCard.vue'
import { useFavorites } from '../composables/useFavorites.js'

const route  = useRoute()
const router = useRouter()
const { toggleFavorite, isFavorited } = useFavorites()

const loading = ref(true)
const error   = ref('')
const tool    = ref(null)
const tools   = ref([])

const comments    = ref([])
const newComment  = ref('')
const userRating  = ref(0)

let activeController = null

const fetchToolDetail = async (signal) => {
  try {
    const res = await axios.get(`/api/tool.php?id=${route.params.id}`, { signal })
    tool.value = res.data
  } catch (err) {
    if (axios.isCancel?.(err) || err.name === 'CanceledError') return
    const fb = getFallbackTools()
    tools.value = fb
    tool.value  = fb.find(item => String(item.id) === String(route.params.id)) || null
  }
}

const fetchAllTools = async (signal) => {
  try {
    const res = await axios.get('/api/tools.php', { signal })
    tools.value = res.data
  } catch (err) {
    if (axios.isCancel?.(err) || err.name === 'CanceledError') return
    tools.value = getFallbackTools()
  }
}

const fetchComments = async (signal) => {
  try {
    const res = await axios.get(`/api/comments.php?tool_id=${route.params.id}`, { signal })
    comments.value = res.data
    commentPage.value = 1
  } catch (err) {
    if (axios.isCancel?.(err) || err.name === 'CanceledError') return
    comments.value = []
  }
}

const addComment = async () => {
  if (!newComment.value.trim() && userRating.value === 0) return
  try {
    const res = await axios.post('/api/submit_review.php', {
      token:   localStorage.getItem('jwt_token'),
      tool_id: route.params.id,
      comment: newComment.value.trim() || '（僅給予星級評分）',
      stars:   userRating.value || 5
    })
    if (res.data.success) {
      await fetchComments()
      newComment.value = ''
      userRating.value = 0
    } else {
      alert('發布失敗: ' + (res.data.error || '原因不明'))
    }
  } catch (err) {
    console.error(err)
    alert('網路錯誤，請稍後再試。')
  }
}

const submitRating = (rating) => { userRating.value = rating }

const PAGE_SIZE      = 5
const commentPage    = ref(1)
const commentSort    = ref('desc')
const sortedComments = computed(() =>
  [...comments.value].sort((a, b) =>
    commentSort.value === 'desc'
      ? (Number(b.rating) || 5) - (Number(a.rating) || 5)
      : (Number(a.rating) || 5) - (Number(b.rating) || 5)
  )
)
const commentPageCount = computed(() => Math.max(1, Math.ceil(sortedComments.value.length / PAGE_SIZE)))
const pagedComments    = computed(() =>
  sortedComments.value.slice((commentPage.value - 1) * PAGE_SIZE, commentPage.value * PAGE_SIZE)
)

const getRatingCount   = (star) => comments.value.filter(c => Number(c.rating || 5) === star).length
const getRatingPercent = (star) => {
  const total = comments.value.length || 1
  return (getRatingCount(star) / total) * 100
}

const recommendedTools = computed(() => {
  if (!tool.value) return []
  const currentTags = tool.value.tags || []
  return tools.value
    .filter(item => item.id !== tool.value.id)
    .map(item => {
      const matchCount = (item.tags || []).filter(t => currentTags.includes(t)).length
      return { ...item, matchCount }
    })
    .sort((a, b) => b.matchCount !== a.matchCount ? b.matchCount - a.matchCount : b.rating - a.rating)
    .slice(0, 3)
})

const reloadAll = async () => {
  if (activeController) activeController.abort()
  activeController = new AbortController()
  const signal = activeController.signal
  loading.value = true
  try {
    await Promise.all([fetchAllTools(signal), fetchToolDetail(signal), fetchComments(signal)])
  } catch (err) {
    if (!axios.isCancel?.(err) && err.name !== 'CanceledError') error.value = '資料載入失敗，請稍後再試。'
  } finally {
    if (!signal.aborted) loading.value = false
  }
}

onMounted(reloadAll)

watch(() => route.params.id, (newId) => {
  if (!newId) return
  if (activeController) activeController.abort()
  activeController = new AbortController()
  const signal = activeController.signal
  fetchToolDetail(signal)
  fetchComments(signal)
  window.scrollTo({ top: 0, behavior: 'smooth' })
})

onBeforeUnmount(() => { if (activeController) activeController.abort() })

const getEmbedUrl = (url) => {
  if (!url) return ''
  if (url.includes('youtube.com') || url.includes('youtu.be')) {
    const m = url.match(/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/)
    return (m && m[2].length === 11) ? `https://www.youtube.com/embed/${m[2]}` : url
  }
  return url
}

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
const getTagColor = (tag) => TAG_COLORS[tag] || 'bg-slate-100 text-slate-600'

const getFallbackTools = () => [
  { id: 1, name: 'ChatGPT', logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=ChatGPT', rating: 4.9, tags: ['文字生成', 'Free'], officialUrl: 'https://chat.openai.com', description: 'ChatGPT 是一款強大的 AI 對話工具，可協助文章撰寫、程式碼生成、資料整理與創意發想。', plans: [{ name: '免費方案', price: '$0', features: ['基礎對話', '有限使用次數'] }, { name: '進階方案', price: '$20 / 月', features: ['更快速度', '更多功能'] }, { name: '團隊方案', price: '客製報價', features: ['團隊管理', '企業支援'] }] },
  { id: 2, name: 'Midjourney', logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=Midjourney', rating: 4.8, tags: ['圖像生成', 'Paid'], officialUrl: 'https://www.midjourney.com', description: 'Midjourney 是頂尖 AI 圖像生成工具，透過文字提示產生高品質藝術圖片。', plans: [{ name: 'Basic', price: '$10 / 月', features: ['基礎生成', '個人使用'] }, { name: 'Standard', price: '$30 / 月', features: ['更多額度', '商業使用'] }, { name: 'Pro', price: '$60 / 月', features: ['高額度', '隱私模式'] }] },
  { id: 3, name: 'Notion AI', logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=Notion', rating: 4.9, tags: ['資料整理', 'Freemium'], officialUrl: 'https://www.notion.so', description: 'Notion AI 結合筆記與 AI 助理，協助摘要、整理想法與撰寫文件。', plans: [{ name: 'Free', price: '$0', features: ['基本筆記', '少量 AI'] }, { name: 'Plus', price: '$10 / 月', features: ['完整 AI', '個人進階'] }, { name: 'Business', price: '$18 / 月', features: ['團隊管理', '進階協作'] }] },
]
</script>

<template>
  <div class="min-h-screen">

    <!-- ── Loading ── -->
    <div v-if="loading" class="max-w-5xl mx-auto px-4 py-20 space-y-6">
      <div class="h-8 w-48 rounded-xl bg-slate-200 dark:bg-slate-700 animate-pulse"></div>
      <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 p-8 animate-pulse">
        <div class="flex gap-6 items-start">
          <div class="w-24 h-24 rounded-2xl bg-slate-200 dark:bg-slate-700 flex-shrink-0"></div>
          <div class="flex-1 space-y-3">
            <div class="h-8 w-2/5 bg-slate-200 dark:bg-slate-700 rounded-xl"></div>
            <div class="h-5 w-1/4 bg-slate-200 dark:bg-slate-700 rounded-lg"></div>
            <div class="flex gap-2 pt-1">
              <div class="h-7 w-20 bg-slate-200 dark:bg-slate-700 rounded-lg"></div>
              <div class="h-7 w-16 bg-slate-200 dark:bg-slate-700 rounded-lg"></div>
            </div>
            <div class="h-4 w-full bg-slate-200 dark:bg-slate-700 rounded-lg mt-2"></div>
            <div class="h-4 w-4/5 bg-slate-200 dark:bg-slate-700 rounded-lg"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Error ── -->
    <div v-else-if="error" class="max-w-5xl mx-auto py-32 text-center text-red-500 font-bold px-4">
      {{ error }}
    </div>

    <!-- ── Not found ── -->
    <div v-else-if="!tool" class="max-w-3xl mx-auto text-center py-32 px-4">
      <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
        <Bot class="w-10 h-10 text-slate-300 dark:text-slate-600" />
      </div>
      <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-3">找不到此工具</h1>
      <p class="text-slate-500 mb-8">這款工具可能已被移除或不存在。</p>
      <RouterLink to="/tools" class="inline-flex items-center gap-2 px-8 py-3.5 bg-primary text-white rounded-xl font-bold no-underline hover:bg-blue-700 transition-all">
        <ArrowLeft class="w-4 h-4" /> 回到工具列表
      </RouterLink>
    </div>

    <!-- ── Main content ── -->
    <div v-else class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

      <!-- Breadcrumb -->
      <nav class="flex items-center gap-1.5 text-sm text-slate-400 mb-6">
        <RouterLink to="/" class="hover:text-primary transition-colors no-underline">首頁</RouterLink>
        <span>/</span>
        <RouterLink to="/tools" class="hover:text-primary transition-colors no-underline">所有工具</RouterLink>
        <span>/</span>
        <span class="text-slate-700 dark:text-slate-300 font-medium truncate max-w-[180px]">{{ tool.name }}</span>
      </nav>

      <!-- ══════════ HERO ══════════ -->
      <section class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 sm:p-8 mb-6">
        <div class="flex flex-col sm:flex-row gap-6 sm:gap-8">
          <!-- Logo -->
          <div class="w-24 h-24 rounded-2xl bg-slate-50 dark:bg-slate-700 border border-slate-100 dark:border-slate-600 flex items-center justify-center flex-shrink-0 self-start">
            <img :src="tool.logoUrl" :alt="tool.name" class="w-16 h-16 object-contain">
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <!-- Name + Favorite -->
            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
              <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                {{ tool.name }}
              </h1>
              <button
                @click="toggleFavorite(tool)"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border font-semibold text-sm transition-all cursor-pointer flex-shrink-0"
                :class="isFavorited(tool.id)
                  ? 'bg-rose-50 border-rose-200 text-rose-500 dark:bg-rose-900/20 dark:border-rose-700'
                  : 'bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-slate-500 hover:text-rose-500 hover:border-rose-200'"
              >
                <Heart class="w-4 h-4" :class="{ 'fill-current': isFavorited(tool.id) }" />
                {{ isFavorited(tool.id) ? '已收藏' : '收藏' }}
              </button>
            </div>

            <!-- Rating row -->
            <div class="flex items-center gap-3 mb-4">
              <div class="flex items-center gap-0.5">
                <Star
                  v-for="n in 5" :key="n"
                  class="w-4 h-4"
                  :class="n <= Math.round(tool.rating) ? 'text-amber-400 fill-current' : 'text-slate-200 dark:text-slate-600'"
                />
              </div>
              <span class="text-lg font-bold text-slate-900 dark:text-white">{{ tool.rating }}</span>
              <span class="text-sm text-slate-400 dark:text-slate-500">·</span>
              <span class="text-sm text-slate-500 dark:text-slate-400 font-medium">{{ comments.length }} 則評價</span>
            </div>

            <!-- Tags (all: category + price + feature) -->
            <div class="flex flex-wrap gap-2 mb-5">
              <span
                v-for="tag in tool.tags"
                :key="tag"
                class="px-3 py-1 rounded-lg text-sm font-semibold"
                :class="getTagColor(tag)"
              >
                {{ tag }}
              </span>
            </div>

            <!-- Description -->
            <p class="text-base text-slate-600 dark:text-slate-300 leading-7 mb-6">
              {{ tool.description }}
            </p>

            <!-- CTA -->
            <a
              :href="tool.officialUrl || '#'"
              target="_blank"
              class="inline-flex items-center gap-2 px-6 py-3 bg-primary hover:bg-blue-700 text-white rounded-xl font-bold transition-all shadow-md shadow-primary/20 no-underline text-sm"
            >
              前往官方網站
              <ExternalLink class="w-4 h-4" />
            </a>
          </div>
        </div>
      </section>

      <!-- ══════════ VIDEO ══════════ -->
      <section v-if="tool.video_url" class="mb-6">
        <h2 class="section-title">介紹影片</h2>
        <div class="aspect-video rounded-3xl overflow-hidden border border-slate-100 dark:border-slate-700 shadow-lg">
          <iframe
            :src="getEmbedUrl(tool.video_url)"
            class="w-full h-full border-none"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
          ></iframe>
        </div>
      </section>

      <!-- ══════════ PRICING ══════════ -->
      <section v-if="tool.plans && tool.plans.length" class="mb-6">
        <h2 class="section-title">
          <DollarSign class="w-5 h-5 text-primary" /> 價格方案
        </h2>
        <div class="grid sm:grid-cols-3 gap-4">
          <div
            v-for="plan in tool.plans"
            :key="plan.name"
            class="rounded-2xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all hover:-translate-y-0.5 hover:shadow-md"
          >
            <p class="text-sm font-bold text-slate-400 dark:text-slate-500 mb-1 uppercase tracking-wide">
              {{ plan.name }}
            </p>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white mb-5">
              {{ plan.price }}
            </p>

            <ul class="space-y-3">
              <li
                v-for="feature in plan.features"
                :key="feature"
                class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-400 font-medium"
              >
                <CheckCircle2 class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" />
                {{ feature }}
              </li>
            </ul>
          </div>
        </div>
      </section>

      <!-- ══════════ REVIEWS ══════════ -->
      <section class="mb-6">
        <h2 class="section-title">
          <Star class="w-5 h-5 text-primary" /> 評價
        </h2>

        <!-- Score overview + your rating -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 mb-4">
          <div class="flex flex-col sm:flex-row gap-6 sm:gap-8">
            <!-- Big score -->
            <div class="flex items-center gap-4 sm:flex-col sm:items-center sm:min-w-[88px] sm:text-center">
              <span class="text-6xl font-black text-slate-900 dark:text-white leading-none">{{ tool.rating }}</span>
              <div>
                <div class="flex gap-0.5 mb-1">
                  <Star v-for="n in 5" :key="n" class="w-4 h-4"
                    :class="n <= Math.round(tool.rating) ? 'text-amber-400 fill-current' : 'text-slate-200 dark:text-slate-600'" />
                </div>
                <span class="text-xs text-slate-400 font-medium whitespace-nowrap">{{ comments.length }} 則評價</span>
              </div>
            </div>

            <!-- Breakdown bars -->
            <div class="flex-1 space-y-2">
              <div v-for="n in 5" :key="n" class="flex items-center gap-2.5">
                <span class="w-7 text-right text-xs text-slate-500 dark:text-slate-400 font-semibold flex-shrink-0">{{ 6 - n }}★</span>
                <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                  <div class="h-full bg-amber-400 rounded-full transition-all duration-700"
                    :style="{ width: getRatingPercent(6 - n) + '%' }"></div>
                </div>
                <span class="w-5 text-xs text-slate-400 font-medium flex-shrink-0">{{ getRatingCount(6 - n) }}</span>
              </div>
            </div>

            <!-- Your rating -->
            <div class="flex flex-col items-center justify-center sm:border-l sm:border-slate-100 sm:dark:border-slate-700 sm:pl-8 gap-1">
              <p class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">給個評分</p>
              <div class="flex gap-1">
                <button v-for="star in 5" :key="star" @click="submitRating(star)"
                  class="border-none bg-transparent cursor-pointer p-0.5 hover:scale-110 transition-transform">
                  <Star class="w-7 h-7 transition-colors"
                    :class="star <= userRating ? 'text-amber-400 fill-current' : 'text-slate-200 dark:text-slate-600 hover:text-amber-300'" />
                </button>
              </div>
              <p class="text-xs text-slate-400 h-4">{{ userRating > 0 ? `已選 ${userRating} 星` : '' }}</p>
            </div>
          </div>
        </div>

        <!-- Comment input + list (同一張卡) -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
          <!-- Input -->
          <div class="flex gap-3 mb-5">
            <input
              v-model="newComment"
              type="text"
              placeholder="分享你的使用心得..."
              class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-slate-200 placeholder-slate-400 outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all text-sm"
              @keyup.enter="addComment"
            >
            <button @click="addComment"
              class="px-4 py-2.5 bg-primary hover:bg-blue-700 text-white rounded-xl font-bold flex items-center gap-1.5 transition-all border-none cursor-pointer text-sm flex-shrink-0">
              <Send class="w-4 h-4" /> 發佈
            </button>
          </div>

          <!-- Sort toggle -->
          <div v-if="comments.length > 0" class="flex items-center gap-2 mb-4">
            <span class="text-xs text-slate-400 font-medium">排序：</span>
            <button
              @click="commentSort = 'desc'; commentPage = 1"
              class="px-3 py-1 rounded-lg text-xs font-bold border transition-all cursor-pointer"
              :class="commentSort === 'desc'
                ? 'bg-primary text-white border-primary'
                : 'bg-transparent border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:border-primary hover:text-primary'"
            >
              ★ 高到低
            </button>
            <button
              @click="commentSort = 'asc'; commentPage = 1"
              class="px-3 py-1 rounded-lg text-xs font-bold border transition-all cursor-pointer"
              :class="commentSort === 'asc'
                ? 'bg-primary text-white border-primary'
                : 'bg-transparent border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:border-primary hover:text-primary'"
            >
              ★ 低到高
            </button>
          </div>

          <!-- Empty state -->
          <div v-if="comments.length === 0" class="py-10 text-center">
            <MessageSquare class="w-9 h-9 text-slate-200 dark:text-slate-600 mx-auto mb-2" />
            <p class="text-slate-400 text-sm font-medium">還沒有評價，來當第一個！</p>
          </div>

          <!-- Comment list: divider style -->
          <div v-else>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
              <div
                v-for="(comment, index) in pagedComments"
                :key="index"
                class="py-4 first:pt-0"
              >
                <div class="flex items-center justify-between gap-3 mb-1.5">
                  <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                      <span class="text-primary font-bold text-xs">{{ comment.user?.charAt(0)?.toUpperCase() }}</span>
                    </div>
                    <span class="font-bold text-base text-slate-900 dark:text-slate-100">{{ comment.user }}</span>
                    <span class="text-sm text-slate-400">{{ comment.date }}</span>
                  </div>
                  <div class="flex gap-0.5 flex-shrink-0">
                    <Star v-for="s in 5" :key="s" class="w-3 h-3"
                      :class="s <= (comment.rating || 5) ? 'text-amber-400 fill-current' : 'text-slate-200 dark:text-slate-600'" />
                  </div>
                </div>
                <p class="text-base font-medium text-slate-700 dark:text-slate-200 leading-relaxed pl-9">{{ comment.content }}</p>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="commentPageCount > 1" class="flex items-center justify-between gap-2 mt-5 pt-4 border-t border-slate-100 dark:border-slate-700">
              <button
                @click="commentPage--"
                :disabled="commentPage === 1"
                class="px-4 py-2 rounded-xl border text-sm font-semibold transition-all cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                :class="commentPage === 1
                  ? 'border-slate-200 dark:border-slate-600 text-slate-400 bg-transparent'
                  : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary bg-transparent'"
              >
                ← 上一頁
              </button>

              <div class="flex items-center gap-1.5">
                <button
                  v-for="p in commentPageCount"
                  :key="p"
                  @click="commentPage = p"
                  class="w-8 h-8 rounded-lg text-sm font-bold transition-all cursor-pointer border-none"
                  :class="p === commentPage
                    ? 'bg-primary text-white shadow-sm'
                    : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 hover:bg-primary/10 hover:text-primary'"
                >
                  {{ p }}
                </button>
              </div>

              <button
                @click="commentPage++"
                :disabled="commentPage === commentPageCount"
                class="px-4 py-2 rounded-xl border text-sm font-semibold transition-all cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                :class="commentPage === commentPageCount
                  ? 'border-slate-200 dark:border-slate-600 text-slate-400 bg-transparent'
                  : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-primary hover:text-primary bg-transparent'"
              >
                下一頁 →
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- ══════════ RECOMMENDED ══════════ -->
      <section v-if="recommendedTools.length">
        <h2 class="section-title">
          <Tag class="w-5 h-5 text-primary" /> 你可能也喜歡
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          <ToolCard
            v-for="item in recommendedTools"
            :key="item.id"
            v-bind="item"
            :disableFlip="true"
            showFavoriteButton
            :isFavorited="isFavorited(item.id)"
            @toggleFavorite="toggleFavorite(item)"
            @click="router.push(`/tool/${item.id}`)"
          />
        </div>
      </section>

    </div>
  </div>
</template>

<style scoped>
.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 1.25rem;
}

:global(.dark) .section-title {
  color: #f1f5f9;
}
</style>
