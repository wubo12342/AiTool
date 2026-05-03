<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import axios from 'axios'
import {
  Star,
  ArrowLeft,
  Send,
  CheckCircle2,
  Bot,
  Heart,
  ExternalLink
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

/*
  API 預留區
  之後後端完成後，可以直接調整這幾個 API 路徑與回傳欄位。
*/

const fetchToolDetail = async () => {
  try {
    const res = await axios.get(`/api/tool.php?id=${route.params.id}`)
    tool.value = res.data
  } catch (err) {
    const fallbackTools = getFallbackTools()
    tools.value = fallbackTools
    tool.value = fallbackTools.find(item => String(item.id) === String(route.params.id)) || null
  }
}

const fetchAllTools = async () => {
  try {
    const res = await axios.get('/api/tools.php')
    tools.value = res.data
  } catch (err) {
    tools.value = getFallbackTools()
  }
}

const fetchComments = async () => {
  try {
    const res = await axios.get(`/api/comments.php?tool_id=${route.params.id}`)
    comments.value = res.data
  } catch (err) {
    comments.value = [
      {
        user: '小明',
        content: '這個工具對寫報告很有幫助，整理資料速度快很多。',
        rating: 5
      },
      {
        user: 'Amy',
        content: '介面簡單好用，適合新手入門。',
        rating: 5
      },
      {
        user: 'Chris',
        content: '功能完整，對工作流程有明顯幫助。',
        rating: 4
      },
      {
        user: '王同學',
        content: '用來整理筆記跟摘要資料很方便。',
        rating: 5
      }
    ]
  }
}

const addComment = async () => {
  if (!newComment.value.trim()) return

  try {
    await axios.post('/api/add_comment.php', {
      tool_id: route.params.id,
      content: newComment.value,
      rating: userRating.value || 5
    })

    await fetchComments()
    newComment.value = ''
  } catch (err) {
    comments.value.unshift({
      user: '訪客',
      content: newComment.value,
      rating: userRating.value || 5
    })

    newComment.value = ''
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

  return tools.value
    .filter(item => item.id !== tool.value.id)
    .slice(0, 3)
})

onMounted(async () => {
  loading.value = true

  try {
    await Promise.all([
      fetchAllTools(),
      fetchToolDetail(),
      fetchComments()
    ])
  } catch (err) {
    error.value = '資料載入失敗，請稍後再試。'
  } finally {
    loading.value = false
  }
})

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
                class="px-3 py-1 rounded-lg bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wider"
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

            <button class="w-full py-3.5 bg-slate-50 text-slate-700 rounded-xl font-bold hover:bg-primary hover:text-white transition-all border-none cursor-pointer text-sm">
              立即開始
            </button>
          </div>
        </div>
      </section>

      <div class="grid md:grid-cols-2 gap-8 items-stretch">
        <section class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-lg md:h-[420px] flex flex-col">
          <h2 class="text-xl font-bold text-slate-900 mb-6">
            使用者評價
          </h2>

          <div class="flex items-center gap-6 mb-8">
            <div class="text-5xl font-bold text-slate-900">
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

              <p class="text-sm text-slate-400">
                共 {{ comments.length }} 則評價
              </p>
            </div>
          </div>

          <div class="space-y-2 mb-8 flex-1">
            <div v-for="n in 5" :key="n" class="flex items-center gap-2 text-sm">
              <span class="w-10 text-slate-500">{{ 6 - n }} 星</span>

              <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                <div
                  class="h-full bg-orange-400"
                  :style="{ width: getRatingPercent(6 - n) + '%' }"
                ></div>
              </div>

              <span class="w-8 text-slate-400 text-xs">
                {{ getRatingCount(6 - n) }}
              </span>
            </div>
          </div>

          <div>
            <p class="font-semibold text-slate-700 mb-2">
              你的評分
            </p>

            <div class="flex gap-2">
              <button
                v-for="star in 5"
                :key="star"
                @click="submitRating(star)"
                class="transition hover:scale-110 border-none bg-transparent cursor-pointer p-0.5"
              >
                <Star
                  class="w-7 h-7"
                  :class="star <= userRating ? 'text-orange-400 fill-current' : 'text-slate-200'"
                />
              </button>
            </div>
          </div>
        </section>

        <section class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-lg md:h-[420px] flex flex-col">
          <h2 class="text-xl font-bold text-slate-900 mb-6">
            社群評價
          </h2>

          <div class="flex gap-3 mb-8">
            <input
              v-model="newComment"
              type="text"
              placeholder="分享您的使用心得..."
              class="flex-1 rounded-xl border border-slate-100 px-5 py-3.5 outline-none focus:ring-4 focus:ring-primary/10 bg-slate-50 transition-all font-medium text-sm"
              @keyup.enter="addComment"
            >

            <button
              @click="addComment"
              class="px-6 bg-primary text-white rounded-xl font-bold hover:bg-primary/90 flex items-center gap-2 transition-all shadow-md shadow-primary/10 border-none cursor-pointer text-sm"
            >
              <Send class="w-4 h-4" />
              發佈
            </button>
          </div>

          <div class="space-y-4 flex-1 overflow-y-auto pr-2 custom-scrollbar">
            <div
              v-for="(comment, index) in comments"
              :key="index"
              class="p-5 rounded-2xl border border-slate-50 bg-slate-50/30"
            >
              <div class="flex items-center justify-between gap-3 mb-2.5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                    {{ comment.user.charAt(0) }}
                  </div>
                  <h4 class="font-bold text-slate-800 text-sm">
                    {{ comment.user }}
                  </h4>
                </div>

                <div class="flex items-center gap-1 text-orange-400 text-xs font-bold">
                  <Star class="w-4 h-4 fill-current" />
                  {{ comment.rating || 5 }}
                </div>
              </div>

              <p class="text-slate-500 leading-relaxed font-medium text-sm">
                {{ comment.content }}
              </p>
            </div>
          </div>
        </section>
      </div>

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

            <div class="flex flex-wrap gap-2">
              <span
                v-for="tag in item.tags"
                :key="tag"
                class="px-3 py-1 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold"
              >
                {{ tag }}
              </span>
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