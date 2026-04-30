<script setup>
import { computed, ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { Star, ArrowLeft, Send, CheckCircle2, Bot } from 'lucide-vue-next'

const route = useRoute()

const tools = [
  {
    id: 1,
    name: 'ChatGPT',
    image: 'https://api.dicebear.com/7.x/identicon/svg?seed=ChatGPT',
    rating: 4.9,
    tags: ['文本創作', '程式開發', '免費版'],
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
        features: ['更快回應速度', '更多模型功能', '適合 daily 工作']
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
    image: 'https://api.dicebear.com/7.x/identicon/svg?seed=Midjourney',
    rating: 4.8,
    tags: ['圖像生成', '設計', '付費'],
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
    image: 'https://api.dicebear.com/7.x/identicon/svg?seed=Jasper',
    rating: 4.7,
    tags: ['行銷文案', '內容生成', '訂閱制'],
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
    image: 'https://api.dicebear.com/7.x/identicon/svg?seed=Notion',
    rating: 4.9,
    tags: ['生產力', '筆記整理', 'Freemium'],
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

const tool = computed(() => {
  return tools.find(item => String(item.id) === String(route.params.id))
})

const comments = ref([
  {
    user: '小明',
    content: '這個工具對寫報告很有幫助，整理資料速度快很多。'
  },
  {
    user: 'Amy',
    content: '介面簡單好用，適合新手入門。'
  }
])

const newComment = ref('')
const userRating = ref(0)

const addComment = () => {
  if (!newComment.value.trim()) return

  comments.value.unshift({
    user: '訪客',
    content: newComment.value
  })

  newComment.value = ''
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 px-4 py-10 animate-in fade-in duration-500">
    <div v-if="tool" class="max-w-7xl mx-auto">
      <RouterLink
        to="/"
        class="mb-8 inline-flex items-center gap-2 text-slate-500 hover:text-primary font-semibold no-underline transition-colors"
      >
        <ArrowLeft class="w-5 h-5" />
        返回首頁
      </RouterLink>

      <!-- 產品資訊 -->
      <section class="glass-card p-8 md:p-10 mb-10 rounded-[3rem] border border-white/20 shadow-xl bg-white/70 backdrop-blur-xl">
        <div class="grid md:grid-cols-[220px_1fr] gap-12 items-center">
          <div class="bg-white rounded-[2.5rem] shadow-2xl p-10 flex items-center justify-center border border-slate-50">
            <img
              :src="tool.image"
              :alt="tool.name"
              class="w-36 h-36 object-contain hover:scale-110 transition-transform duration-500"
            >
          </div>

          <div>
            <h1 class="text-4xl md:text-6xl font-black text-slate-900 mb-6 tracking-tight">
              {{ tool.name }}
            </h1>

            <div class="flex flex-wrap items-center gap-3 mb-8">
              <span
                v-for="tag in tool.tags"
                :key="tag"
                class="px-4 py-1.5 rounded-full bg-primary/5 text-primary text-xs font-bold border border-primary/5"
              >
                {{ tag }}
              </span>

              <span class="flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-orange-50 text-orange-500 text-xs font-black">
                <Star class="w-4 h-4 fill-current" />
                {{ tool.rating }}
              </span>
            </div>

            <p class="text-slate-600 leading-relaxed text-xl font-medium">
              {{ tool.description }}
            </p>
          </div>
        </div>
      </section>

      <!-- 價格方案 -->
      <section class="mb-16">
        <h2 class="text-3xl font-black text-slate-900 mb-8 px-4 flex items-center gap-3">
          <div class="w-2 h-8 bg-primary rounded-full"></div>
          價格方案
        </h2>

        <div class="grid md:grid-cols-3 gap-8 px-4">
          <div
            v-for="plan in tool.plans"
            :key="plan.name"
            class="glass-card p-10 rounded-[2.5rem] hover:-translate-y-2 transition-all duration-300 border border-white/20 shadow-xl bg-white/60 group"
          >
            <h3 class="text-xl font-black text-slate-900 mb-2 group-hover:text-primary transition-colors">
              {{ plan.name }}
            </h3>

            <p class="text-4xl font-black text-primary mb-8">
              {{ plan.price }}
            </p>

            <ul class="space-y-4 mb-8">
              <li
                v-for="feature in plan.features"
                :key="feature"
                class="flex items-center gap-3 text-slate-600 font-medium"
              >
                <CheckCircle2 class="w-5 h-5 text-cta flex-shrink-0" />
                {{ feature }}
              </li>
            </ul>

            <button class="w-full py-4 bg-slate-900 text-white rounded-2xl font-bold hover:bg-primary transition-all shadow-lg border-none cursor-pointer">
              立即開始
            </button>
          </div>
        </div>
      </section>

      <div class="grid md:grid-cols-2 gap-8">
        <!-- 評分區 -->
        <section class="glass-card p-10 rounded-[3rem] border border-white/20 shadow-xl bg-white/70">
          <h2 class="text-2xl font-black text-slate-900 mb-6">
            給這個工具評分
          </h2>

          <div class="flex items-center gap-3 mb-6">
            <button
              v-for="star in 5"
              :key="star"
              @click="userRating = star"
              class="transition-all hover:scale-125 border-none bg-transparent cursor-pointer p-1"
            >
              <Star
                class="w-10 h-10"
                :class="star <= userRating ? 'text-orange-400 fill-current' : 'text-slate-200'"
              />
            </button>
          </div>

          <div class="p-4 bg-slate-50 rounded-2xl inline-block font-bold text-slate-600 border border-slate-100">
            您的評分：<span class="text-orange-500">{{ userRating || '尚未評分' }}</span>
          </div>
        </section>

        <!-- 留言板 -->
        <section class="glass-card p-10 rounded-[3rem] border border-white/20 shadow-xl bg-white/70">
          <h2 class="text-2xl font-black text-slate-900 mb-6">
            社群評價
          </h2>

          <div class="flex gap-4 mb-10">
            <input
              v-model="newComment"
              type="text"
              placeholder="分享您的使用心得..."
              class="flex-1 rounded-2xl border border-slate-100 px-6 py-4 outline-none focus:ring-4 focus:ring-primary/10 bg-white/80 transition-all font-medium"
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

          <div class="space-y-6 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
            <div
              v-for="(comment, index) in comments"
              :key="index"
              class="bg-white/50 backdrop-blur-sm rounded-2xl p-6 border border-white shadow-sm hover:shadow-md transition-all"
            >
              <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                  {{ comment.user.charAt(0) }}
                </div>
                <h4 class="font-black text-slate-900">
                  {{ comment.user }}
                </h4>
              </div>
              <p class="text-slate-600 leading-relaxed font-medium">
                {{ comment.content }}
              </p>
            </div>
          </div>
        </section>
      </div>
    </div>

    <div v-else class="max-w-3xl mx-auto text-center py-32">
      <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-8 text-slate-300">
        <Bot class="w-12 h-12" />
      </div>
      <h1 class="text-4xl font-black text-slate-900 mb-6">
        找不到此 AI 工具
      </h1>
      <p class="text-slate-500 mb-10 text-lg">這款工具可能已經遷移或已被移除。</p>
      <RouterLink
        to="/"
        class="inline-block px-12 py-5 bg-primary text-white rounded-2xl font-black shadow-2xl shadow-primary/30 no-underline hover:-translate-y-1 transition-all"
      >
        返回平台首頁
      </RouterLink>
    </div>
  </div>
</template>