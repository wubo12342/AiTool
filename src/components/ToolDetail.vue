<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Star, ArrowLeft, Send, CheckCircle2 } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()

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
  <div class="min-h-screen bg-slate-50 px-4 py-10">
    <div v-if="tool" class="max-w-7xl mx-auto">
      <button
        @click="router.back()"
        class="mb-8 flex items-center gap-2 text-slate-500 hover:text-primary font-semibold"
      >
        <ArrowLeft class="w-5 h-5" />
        返回
      </button>

      <!-- 產品資訊 -->
      <section class="glass-card p-8 md:p-10 mb-10">
        <div class="grid md:grid-cols-[220px_1fr] gap-8 items-center">
          <div class="bg-white rounded-3xl shadow-xl p-8 flex items-center justify-center">
            <img
              :src="tool.image"
              :alt="tool.name"
              class="w-36 h-36 object-contain"
            >
          </div>

          <div>
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-4">
              {{ tool.name }}
            </h1>

            <div class="flex flex-wrap items-center gap-3 mb-6">
              <span
                v-for="tag in tool.tags"
                :key="tag"
                class="px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-sm font-semibold"
              >
                {{ tag }}
              </span>

              <span class="flex items-center gap-1 px-3 py-1 rounded-full bg-orange-50 text-orange-500 text-sm font-bold">
                <Star class="w-4 h-4 fill-current" />
                {{ tool.rating }}
              </span>
            </div>

            <p class="text-slate-600 leading-8 text-lg">
              {{ tool.description }}
            </p>
          </div>
        </div>
      </section>

      <!-- 價格方案 -->
      <section class="mb-12">
        <h2 class="text-3xl font-bold text-slate-900 mb-6">
          價格方案
        </h2>

        <div class="grid md:grid-cols-3 gap-6">
          <div
            v-for="plan in tool.plans"
            :key="plan.name"
            class="glass-card p-6 hover:-translate-y-1 transition-transform"
          >
            <h3 class="text-xl font-bold text-slate-900 mb-2">
              {{ plan.name }}
            </h3>

            <p class="text-3xl font-extrabold text-primary mb-6">
              {{ plan.price }}
            </p>

            <ul class="space-y-3">
              <li
                v-for="feature in plan.features"
                :key="feature"
                class="flex items-center gap-2 text-slate-600"
              >
                <CheckCircle2 class="w-5 h-5 text-cta" />
                {{ feature }}
              </li>
            </ul>
          </div>
        </div>
      </section>

      <!-- 評分區 -->
      <section class="glass-card p-8 mb-10">
        <h2 class="text-2xl font-bold text-slate-900 mb-4">
          給這個工具評分
        </h2>

        <div class="flex items-center gap-2 mb-4">
          <button
            v-for="star in 5"
            :key="star"
            @click="userRating = star"
            class="transition-transform hover:scale-110"
          >
            <Star
              class="w-8 h-8"
              :class="star <= userRating ? 'text-orange-400 fill-current' : 'text-slate-300'"
            />
          </button>
        </div>

        <p class="text-slate-500">
          你的評分：{{ userRating || '尚未評分' }}
        </p>
      </section>

      <!-- 留言板 -->
      <section class="glass-card p-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">
          留言板
        </h2>

        <div class="flex gap-3 mb-8">
          <input
            v-model="newComment"
            type="text"
            placeholder="留下你對這個工具的看法..."
            class="flex-1 rounded-xl border border-slate-200 px-4 py-3 outline-none focus:ring-2 focus:ring-primary bg-white"
            @keyup.enter="addComment"
          >

          <button
            @click="addComment"
            class="px-5 py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary/90 flex items-center gap-2"
          >
            <Send class="w-5 h-5" />
            送出
          </button>
        </div>

        <div class="space-y-4">
          <div
            v-for="(comment, index) in comments"
            :key="index"
            class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm"
          >
            <h4 class="font-bold text-slate-900 mb-2">
              {{ comment.user }}
            </h4>
            <p class="text-slate-600">
              {{ comment.content }}
            </p>
          </div>
        </div>
      </section>
    </div>

    <div v-else class="max-w-3xl mx-auto text-center py-24">
      <h1 class="text-3xl font-bold text-slate-900 mb-4">
        找不到此 AI 工具
      </h1>
      <button
        @click="router.push('/')"
        class="px-6 py-3 bg-primary text-white rounded-xl font-semibold"
      >
        回首頁
      </button>
    </div>
  </div>
</template>