<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import axios from 'axios'
import {
  Star,
  ArrowLeft,
  Send,
  CheckCircle2,
  Bot,
  Heart,
  ExternalLink,
  ArrowRight
} from 'lucide-vue-next'
import { useFavorites } from '../composables/useFavorites.js'

const route = useRoute()
const { toggleFavorite, isFavorited } = useFavorites()

const loading = ref(true)
const error = ref('')
const tool = ref(null)
const tools = ref([])

const comments = ref([])
const newComment = ref('')
const userRating = ref(0)

// H7 — 用 AbortController 取消上一個請求，避免快速切換時舊回應蓋掉新資料
let activeController = null

const fetchToolDetail = async (signal) => {
  try {
    const res = await axios.get(`/api/tool.php?id=${route.params.id}`, { signal })
    tool.value = res.data
  } catch (err) {
    if (axios.isCancel?.(err) || err.name === 'CanceledError') return
    const fallbackTools = getFallbackTools()
    tools.value = fallbackTools
    tool.value = fallbackTools.find(item => String(item.id) === String(route.params.id)) || null
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
  } catch (err) {
    if (axios.isCancel?.(err) || err.name === 'CanceledError') return
    comments.value = []
  }
}

const addComment = async () => {
  if (!newComment.value.trim() && userRating.value === 0) return

  console.log('準備發布評論:', {
    tool_id: route.params.id,
    comment: newComment.value,
    stars: userRating.value
  });

  try {
    const res = await axios.post('/api/submit_review.php', {
      token: localStorage.getItem('jwt_token'),
      tool_id: route.params.id,
      comment: newComment.value.trim() || '（僅給予星級評分）',
      stars: userRating.value || 5
    })
    
    console.log('伺服器回傳:', res.data);

    if (res.data.success) {
      await fetchComments()
      newComment.value = ''
      userRating.value = 0
    } else {
      alert('發布失敗: ' + (res.data.error || '原因不明'));
    }
  } catch (err) {
    console.error('發布評論失敗 (詳細錯誤):', err);
    alert('網路錯誤或伺服器無回應，請檢查控制台。');
  }
}

const submitRating = async (rating) => {
  userRating.value = rating

  try {
    await axios.post('/api/rate.php', {
      tool_id: route.params.id,
      rating
    })
  } catch (err) {
    console.warn('評分 API 尚未完成')
  }
}

const getRatingCount = (star) => {
  return comments.value.filter(comment => Number(comment.rating || 5) === star).length
}

const getRatingPercent = (star) => {
  const total = comments.value.length || 1
  const count = getRatingCount(star)
  return (count / total) * 100
}

const recommendedTools = computed(() => {
  if (!tool.value) return []

  // 取得目前工具的標籤 (包含分類與價格狀態)
  const currentTags = tool.value.tags || []

  return tools.value
    .filter(item => item.id !== tool.value.id) // 排除目前正在查看的工具
    .map(item => {
      // 計算與目前工具重合的標籤數量
      const itemTags = item.tags || []
      const matchCount = itemTags.filter(tag => currentTags.includes(tag)).length
      return { ...item, matchCount }
    })
    .sort((a, b) => {
      // 優先依照匹配標籤數量排序，數量相同則依評分排序
      if (b.matchCount !== a.matchCount) {
        return b.matchCount - a.matchCount
      }
      return b.rating - a.rating
    })
    .slice(0, 3)
})

const reloadAll = async () => {
  // 取消上一輪還在跑的請求
  if (activeController) activeController.abort()
  activeController = new AbortController()
  const signal = activeController.signal

  loading.value = true
  try {
    await Promise.all([
      fetchAllTools(signal),
      fetchToolDetail(signal),
      fetchComments(signal)
    ])
  } catch (err) {
    if (!axios.isCancel?.(err) && err.name !== 'CanceledError') {
      error.value = '資料載入失敗，請稍後再試。'
    }
  } finally {
    if (!signal.aborted) loading.value = false
  }
}

onMounted(reloadAll)

// 切換到不同工具時，先 abort 舊請求再發新請求
watch(() => route.params.id, (newId) => {
  if (!newId) return
  if (activeController) activeController.abort()
  activeController = new AbortController()
  const signal = activeController.signal

  fetchToolDetail(signal)
  fetchComments(signal)
  window.scrollTo({ top: 0, behavior: 'smooth' })
})

onBeforeUnmount(() => {
  if (activeController) activeController.abort()
})

const getEmbedUrl = (url) => {
  if (!url) return ''
  if (url.includes('youtube.com') || url.includes('youtu.be')) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/
    const match = url.match(regExp)
    return (match && match[2].length === 11) ? `https://www.youtube.com/embed/${match[2]}` : url
  }
  return url
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

const getFallbackTools = () => [
  {
    id: 1,
    name: 'ChatGPT',
    logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=ChatGPT',
    rating: 4.9,
    tags: ['文本創作', '程式開發', '免費版'],
    officialUrl: 'https://chat.openai.com',
    description:
      'ChatGPT 是一款強大的 AI 對話與生產力工具，可協助使用者進行文章撰寫、程式碼生成、資料整理、創意發想與學習輔助。適合學生、上班族、開發者與內容創作者使用。',
    plans: [
      {
        name: '免費方案',
        price: '$0',
        features: ['基礎 AI 對話', '有限使用次數', '適合入門體驗']
      },
      {
        name: '進階方案',
        price: '$20 / 月',
        features: ['更快回應速度', '更多模型功能', '適合日常工作']
      },
      {
        name: '團隊方案',
        price: '客製報價',
        features: ['團隊管理', '共享工作區', '企業級支援']
      }
    ]
  },
  {
    id: 2,
    name: 'Midjourney',
    logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=Midjourney',
    rating: 4.8,
    tags: ['圖像生成', '設計', '付費'],
    officialUrl: 'https://www.midjourney.com',
    description:
      'Midjourney 是一款 AI 圖像生成工具，可透過文字提示產生高品質圖片，適合設計師、插畫師、行銷人員與創作者用於概念設計、視覺發想與藝術創作。',
    plans: [
      {
        name: 'Basic',
        price: '$10 / 月',
        features: ['基礎圖片生成', '有限快速生成', '個人使用']
      },
      {
        name: 'Standard',
        price: '$30 / 月',
        features: ['更多生成額度', '商業使用', '適合創作者']
      },
      {
        name: 'Pro',
        price: '$60 / 月',
        features: ['高額度生成', '隱私模式', '專業工作流']
      }
    ]
  },
  {
    id: 3,
    name: 'Jasper AI',
    logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=Jasper',
    rating: 4.7,
    tags: ['行銷文案', '內容生成', '訂閱制'],
    officialUrl: 'https://www.jasper.ai',
    description:
      'Jasper AI 專注於行銷文案與品牌內容生成，能協助撰寫廣告文案、部落格文章、社群貼文與產品描述，適合行銷團隊與內容創作者。',
    plans: [
      {
        name: 'Creator',
        price: '$39 / 月',
        features: ['個人文案生成', '基礎模板', '快速產文']
      },
      {
        name: 'Teams',
        price: '$99 / 月',
        features: ['多人協作', '品牌語氣設定', '行銷模板']
      },
      {
        name: 'Business',
        price: '客製報價',
        features: ['企業整合', '權限管理', '專屬支援']
      }
    ]
  },
  {
    id: 4,
    name: 'Notion AI',
    logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=Notion',
    rating: 4.9,
    tags: ['生產力', '筆記整理', 'Freemium'],
    officialUrl: 'https://www.notion.so',
    description:
      'Notion AI 結合筆記、文件、專案管理與 AI 助理功能，可協助摘要內容、整理想法、撰寫文件與提升團隊協作效率。',
    plans: [
      {
        name: 'Free',
        price: '$0',
        features: ['基本筆記功能', '個人工作區', '少量 AI 功能']
      },
      {
        name: 'Plus',
        price: '$10 / 月',
        features: ['更多儲存空間', '完整 AI 輔助', '適合個人進階']
      },
      {
        name: 'Business',
        price: '$18 / 月',
        features: ['團隊管理', '權限控管', '進階協作']
      }
    ]
  }
]
</script>

<template>
  <div class="min-h-screen bg-slate-50 px-4 py-10 animate-in fade-in duration-500">
    <div v-if="loading" class="max-w-7xl mx-auto py-32 text-center text-slate-400 font-bold">
      載入中...
    </div>

    <div v-else-if="error" class="max-w-7xl mx-auto py-32 text-center text-red-500 font-bold">
      {{ error }}
    </div>

    <div v-else-if="tool" class="max-w-7xl mx-auto">
      <RouterLink
        to="/"
        class="mb-8 inline-flex items-center gap-2 text-slate-500 hover:text-primary font-bold no-underline transition-colors"
      >
        <ArrowLeft class="w-5 h-5" />
        返回首頁
      </RouterLink>

      <section class="glass-card p-8 md:p-10 mb-10 rounded-[2.5rem] border border-white shadow-xl bg-white/70 backdrop-blur-xl">
        <div class="grid md:grid-cols-[180px_1fr] gap-10 items-center">
          <div class="bg-white rounded-3xl shadow-lg p-8 flex items-center justify-center border border-slate-50">
            <img
              :src="tool.logoUrl"
              :alt="tool.name"
              class="w-28 h-28 object-contain hover:scale-105 transition-transform duration-500"
            >
          </div>

          <div>
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-5">
              <h1 class="text-3xl md:text-4xl font-bold text-slate-900 tracking-tight">
                {{ tool.name }}
              </h1>

              <button
                @click="toggleFavorite(tool)"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all shadow-sm hover:shadow-md border border-slate-200 bg-white cursor-pointer text-sm"
                :class="isFavorited(tool.id) ? 'text-red-500' : 'text-slate-400 hover:text-red-500'"
              >
                <Heart class="w-4 h-4" :class="{ 'fill-current': isFavorited(tool.id) }" />
                {{ isFavorited(tool.id) ? '已收藏' : '加入收藏' }}
              </button>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 mb-6">
              <span
                v-for="tag in tool.tags"
                :key="tag"
                class="px-3 py-1 rounded-lg text-sm font-bold uppercase tracking-wider"
                :class="getTagColor(tag)"
              >
                {{ tag }}
              </span>

              <span class="flex items-center gap-1 px-3 py-1 rounded-lg bg-orange-50 text-orange-500 text-[10px] font-bold">
                <Star class="w-3.5 h-3.5 fill-current" />
                {{ tool.rating }}
              </span>
            </div>

            <p class="text-slate-600 leading-relaxed text-lg font-medium max-w-3xl">
              {{ tool.description }}
            </p>

            <a
              :href="tool.officialUrl || '#'"
              target="_blank"
              class="mt-8 inline-flex px-8 py-3.5 bg-primary text-white rounded-xl font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20 border-none cursor-pointer items-center gap-2 no-underline"
            >
              前往官方網站
              <ExternalLink class="w-4 h-4" />
            </a>
          </div>
        </div>
      </section>
      
      <!-- Video Section -->
      <section v-if="tool.video_url" class="mb-16">
        <h2 class="text-2xl font-bold text-slate-900 mb-8 px-2 flex items-center gap-3">
          <div class="w-1.5 h-6 bg-cta rounded-full"></div>
          介紹影片
        </h2>
        <div class="aspect-video max-w-4xl mx-auto rounded-[2rem] overflow-hidden shadow-2xl border-4 border-white">
          <iframe 
            :src="getEmbedUrl(tool.video_url)" 
            class="w-full h-full border-none"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen
          ></iframe>
        </div>
      </section>

      <section class="mb-16">
        <h2 class="text-2xl font-bold text-slate-900 mb-8 px-2 flex items-center gap-3">
          <div class="w-1.5 h-6 bg-primary rounded-full"></div>
          價格方案
        </h2>

        <div class="grid md:grid-cols-3 gap-6">
          <div
            v-for="(plan, index) in tool.plans"
            :key="plan.name"
            class="p-8 rounded-[2rem] hover:-translate-y-1 transition-all duration-300 border shadow-lg group"
            :class="index === 1
              ? 'bg-white border-primary/20 shadow-primary/10'
              : 'bg-white border-slate-100'"
          >
            <div
              v-if="index === 1"
              class="inline-block mb-4 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold"
            >
              推薦方案
            </div>

            <h3 class="text-lg font-bold text-slate-400 mb-2 group-hover:text-primary transition-colors">
              {{ plan.name }}
            </h3>

            <p class="text-3xl font-bold text-slate-900 mb-6">
              {{ plan.price }}
            </p>

            <ul class="space-y-3.5 mb-8">
              <li
                v-for="feature in plan.features"
                :key="feature"
                class="flex items-center gap-2.5 text-slate-500 font-medium text-sm"
              >
                <CheckCircle2 class="w-4 h-4 text-cta flex-shrink-0" />
                {{ feature }}
              </li>
            </ul>
          </div>
        </div>
      </section>

      <!-- Unified Reviews & Community Section -->
      <section class="mb-20">
        <h2 class="text-2xl font-bold text-slate-900 mb-8 px-2 flex items-center gap-3">
          <div class="w-1.5 h-6 bg-cta rounded-full"></div>
          評價與社群討論
        </h2>

        <div class="bg-white rounded-[3rem] border border-slate-100 shadow-xl overflow-hidden flex flex-col lg:flex-row min-h-[500px]">
          <!-- Left Side: Rating Summary -->
          <div class="lg:w-1/3 p-10 bg-slate-50/50 border-r border-slate-100">
            <h3 class="text-xl font-bold text-slate-900 mb-8">整體評分</h3>
            
            <div class="flex items-center gap-6 mb-10">
              <div class="text-6xl font-black text-slate-900 leading-none">
                {{ tool.rating }}
              </div>
              <div>
                <div class="flex gap-1 mb-2">
                  <Star
                    v-for="n in 5"
                    :key="n"
                    class="w-5 h-5"
                    :class="n <= Math.round(tool.rating) ? 'text-orange-400 fill-current' : 'text-slate-200'"
                  />
                </div>
                <p class="text-sm text-slate-500 font-medium">
                  共 {{ comments.length }} 則社群評價
                </p>
              </div>
            </div>

            <!-- Rating Breakdown -->
            <div class="space-y-4 mb-12">
              <div v-for="n in 5" :key="n" class="flex items-center gap-4 text-sm">
                <span class="w-10 font-bold text-slate-500 text-right">{{ 6 - n }} 星</span>
                <div class="flex-1 h-2.5 bg-slate-200/50 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-orange-400 transition-all duration-1000"
                    :style="{ width: getRatingPercent(6 - n) + '%' }"
                  ></div>
                </div>
                <span class="w-12 text-slate-400 font-medium">
                  {{ getRatingCount(6 - n) }}
                </span>
              </div>
            </div>

            <!-- Your Rating Action -->
            <div class="pt-8 border-t border-slate-200/50">
              <p class="font-bold text-slate-800 mb-4">
                您的評分
              </p>
              <div class="flex gap-2.5">
                <button
                  v-for="star in 5"
                  :key="star"
                  @click="submitRating(star)"
                  class="transition-all hover:scale-125 border-none bg-transparent cursor-pointer p-0.5 group"
                >
                  <Star
                    class="w-8 h-8 transition-colors"
                    :class="star <= userRating ? 'text-orange-400 fill-current' : 'text-slate-200 group-hover:text-orange-300'"
                  />
                </button>
              </div>
              <p class="text-xs text-slate-400 mt-4 leading-relaxed">
                分享您的使用體驗，幫助社群發現更好的工具。
              </p>
            </div>
          </div>

          <!-- Right Side: Comment Feed -->
          <div class="lg:w-2/3 p-10 flex flex-col">
            <h3 class="text-xl font-bold text-slate-900 mb-8">
              社群討論
            </h3>

            <!-- Comment Input -->
            <div class="flex gap-4 mb-10 group">
              <input
                v-model="newComment"
                type="text"
                placeholder="分享您的使用心得或提問..."
                class="flex-1 rounded-2xl border-2 border-slate-100 px-6 py-4 outline-none focus:border-primary/20 bg-slate-50/50 transition-all font-medium text-slate-700"
                @keyup.enter="addComment"
              >
              <button
                @click="addComment"
                class="px-8 bg-primary text-white rounded-2xl font-bold hover:bg-primary/90 flex items-center gap-2 transition-all shadow-lg shadow-primary/20 border-none cursor-pointer"
              >
                <Send class="w-5 h-5" />
                發佈
              </button>
            </div>

            <!-- Comment List -->
            <div class="space-y-6 flex-1 overflow-y-auto pr-2 custom-scrollbar max-h-[450px]">
              <div
                v-for="(comment, index) in comments"
                :key="index"
                class="p-6 rounded-3xl border border-slate-50 bg-slate-50/50 hover:bg-white hover:shadow-md transition-all duration-300 group"
              >
                <div class="flex items-center justify-between gap-3 mb-3">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                      {{ comment.user.charAt(0) }}
                    </div>
                    <div>
                      <div class="font-bold text-slate-900 text-sm">{{ comment.user }}</div>
                      <div class="text-[10px] text-slate-400 uppercase tracking-tighter">{{ comment.date }}</div>
                    </div>
                  </div>
                  <div class="flex gap-0.5">
                    <Star v-for="s in 5" :key="s" class="w-3 h-3" :class="s <= comment.rating ? 'text-orange-400 fill-current' : 'text-slate-200'" />
                  </div>
                </div>
                <p class="text-slate-600 text-sm leading-relaxed font-medium">
                  {{ comment.content }}
                </p>
              </div>

              <!-- Empty State -->
              <div v-if="comments.length === 0" class="py-20 text-center">
                <Bot class="w-12 h-12 text-slate-200 mx-auto mb-4" />
                <p class="text-slate-400 font-medium">目前尚無討論，成為第一個留言的人吧！</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="mt-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-2">
              或許你會喜歡
            </h2>
            <p class="text-slate-500">
              根據目前瀏覽的工具，推薦你也可以看看這些 AI 工具。
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <RouterLink
            v-for="item in recommendedTools"
            :key="item.id"
            :to="`/tool/${item.id}`"
            class="group no-underline bg-white rounded-[2rem] p-6 border border-slate-100 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all"
          >
            <div class="flex items-start gap-4 mb-5">
              <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center border border-slate-100">
                <img
                  :src="item.logoUrl"
                  :alt="item.name"
                  class="w-10 h-10 object-contain group-hover:scale-110 transition-transform"
                >
              </div>

              <div class="flex-1">
                <h3 class="text-lg font-bold text-slate-900 group-hover:text-primary transition-colors">
                  {{ item.name }}
                </h3>

                <div class="flex items-center gap-1 text-orange-400 text-sm font-bold mt-1">
                  <Star class="w-4 h-4 fill-current" />
                  {{ item.rating }}
                </div>
              </div>
            </div>

            <p class="text-sm text-slate-500 leading-6 line-clamp-2 mb-5">
              {{ item.description }}
            </p>

            <div class="mt-6 pt-5 border-t border-slate-50 flex items-end justify-between gap-4">
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="tag in item.tags"
                  :key="tag"
                  class="px-3 py-1 rounded-lg text-[12px] font-bold"
                  :class="getTagColor(tag)"
                >
                  {{ tag }}
                </span>
              </div>
              
              <div class="px-6 py-2 bg-primary text-white rounded-xl text-sm font-bold shadow-lg shadow-primary/25 whitespace-nowrap group-hover:scale-105 transition-all">
                查看詳情
              </div>
            </div>
          </RouterLink>
        </div>
      </section>
    </div>

    <div v-else class="max-w-3xl mx-auto text-center py-32 px-4">
      <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
        <Bot class="w-10 h-10" />
      </div>
      <h1 class="text-3xl font-bold text-slate-900 mb-4">
        找不到此 AI 工具
      </h1>
      <p class="text-slate-500 mb-8 text-lg">
        這款工具可能已經遷移或已被移除。
      </p>
      <RouterLink
        to="/"
        class="inline-block px-10 py-4 bg-primary text-white rounded-xl font-bold shadow-xl shadow-primary/20 no-underline hover:-translate-y-0.5 transition-all"
      >
        返回平台首頁
      </RouterLink>
    </div>
  </div>
</template>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(12px);
}

.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>