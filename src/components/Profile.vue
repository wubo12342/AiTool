<script setup>
import { ref, computed } from 'vue';
import { RouterLink } from 'vue-router';
import { 
  Bookmark, 
  MessageSquare, 
  Trash2, 
  Edit3, 
  Save, 
  Sparkles,
  Info,
  Star
} from 'lucide-vue-next';
import ToolCard from './ToolCard.vue';
import { useAuth } from '../composables/useAuth.js';

const { username } = useAuth();
const activeTab = ref('favorites');

// --- 模擬資料 Mock Data ---
const favorites = ref([
  {
    id: 1,
    name: 'ChatGPT',
    description: '地表最強 AI 寫作助理，支援程式碼編寫與各類創意構思。',
    logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=ChatGPT',
    rating: 4.9,
    tags: ['文本創作', '免費版'],
    isFavorited: true
  },
  {
    id: 4,
    name: 'Notion AI',
    description: '融合筆記、表格與 AI 助手，自動整理筆記與摘要內容。',
    logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=Notion',
    rating: 4.9,
    tags: ['生產力', 'Freemium'],
    isFavorited: true
  }
]);

const reviews = ref([
  {
    id: 1,
    toolName: 'ChatGPT',
    logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=ChatGPT',
    rating: 5,
    comment: '非常有用的工具，幫我省了很多寫文案的時間！',
    date: '2026-03-20'
  },
  {
    id: 102,
    toolName: 'Midjourney',
    logoUrl: 'https://api.dicebear.com/7.x/identicon/svg?seed=Midjourney',
    rating: 4,
    comment: '生成的圖片很美，但提示詞需要一段時間學習。',
    date: '2026-03-25'
  }
]);

// --- AI 偏好設定 ---
const customContext = ref('');

// --- 功能函數 ---
const handleUnfavorite = (id) => {
  favorites.value = favorites.value.filter(f => f.id !== id);
};

const handleDeleteReview = (id) => {
  reviews.value = reviews.value.filter(r => r.id !== id);
};
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 animate-in fade-in duration-500">
    <div class="flex flex-col md:flex-row gap-8">
      
      <!-- Sidebar Navigation -->
      <aside class="w-full md:w-64 flex-shrink-0">
        <div class="glass-card overflow-hidden rounded-3xl sticky top-24 shadow-xl border border-white/20">
          <div class="p-8 bg-gradient-to-br from-primary/10 to-secondary/10 border-b border-white/20">
            <div class="w-20 h-20 rounded-2xl bg-white shadow-lg flex items-center justify-center mb-4 mx-auto text-primary text-3xl font-bold">
              {{ username.charAt(0).toUpperCase() }}
            </div>
            <h2 class="text-xl font-bold text-center text-slate-800">{{ username }}</h2>
            <p class="text-center text-slate-500 text-xs mt-1">專業 AI 探索者</p>
          </div>
          
          <nav class="p-2">
            <button 
              @click="activeTab = 'favorites'"
              :class="activeTab === 'favorites' ? 'bg-primary text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50'"
              class="w-full flex items-center gap-3 px-4 py-4 rounded-2xl transition-all font-bold mb-1 cursor-pointer border-none text-left"
            >
              <Bookmark class="w-5 h-5" /> 我的收藏
            </button>
            <button 
              @click="activeTab = 'reviews'"
              :class="activeTab === 'reviews' ? 'bg-primary text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50'"
              class="w-full flex items-center gap-3 px-4 py-4 rounded-2xl transition-all font-bold mb-1 cursor-pointer border-none text-left"
            >
              <MessageSquare class="w-5 h-5" /> 我的評論
            </button>
            <button 
              @click="activeTab = 'pref'"
              :class="activeTab === 'pref' ? 'bg-primary text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50'"
              class="w-full flex items-center gap-3 px-4 py-4 rounded-2xl transition-all font-bold mb-1 cursor-pointer border-none text-left"
            >
              <Sparkles class="w-5 h-5" /> AI 偏好設定
            </button>
          </nav>
        </div>
      </aside>

      <!-- Main Content Area -->
      <div class="flex-grow">
        
        <!-- 我的收藏 Tab -->
        <div v-if="activeTab === 'favorites'" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-slate-900">我的收藏</h1>
            <span class="px-4 py-1 bg-primary/10 text-primary rounded-full text-sm font-bold border border-primary/10">共 {{ favorites.length }} 款工具</span>
          </div>
          
          <div v-if="favorites.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            <ToolCard 
              v-for="tool in favorites" 
              :key="tool.id" 
              v-bind="tool"
              showFavoriteButton
              disableFlip
              @toggleFavorite="handleUnfavorite(tool.id)"
            />
          </div>
          <div v-else class="glass-card p-16 text-center rounded-[3rem] border-dashed border-2 border-slate-200 bg-transparent shadow-none">
            <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-400">
              <Bookmark class="w-12 h-12" />
            </div>
            <h3 class="text-2xl font-bold text-slate-800 mb-2">尚無收藏</h3>
            <p class="text-slate-500 mb-8 max-w-xs mx-auto">您還沒有收藏任何 AI 工具，快去首頁看看我們為您挑選的精選工具吧！</p>
            <RouterLink to="/" class="inline-block bg-primary text-white px-10 py-4 rounded-2xl font-bold shadow-xl shadow-primary/20 hover:-translate-y-1 transition-all no-underline">
              探索精選工具
            </RouterLink>
          </div>
        </div>

        <!-- 我的評論 Tab -->
        <div v-if="activeTab === 'reviews'" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-slate-900">我的評論</h1>
          </div>
          
          <div v-if="reviews.length > 0" class="space-y-6">
            <div v-for="review in reviews" :key="review.id" class="glass-card p-8 rounded-[2.5rem] flex gap-8 hover:shadow-2xl transition-all border border-white/20">
              <div class="w-20 h-20 rounded-3xl bg-white shadow-md flex-shrink-0 overflow-hidden flex items-center justify-center border border-slate-100">
                <img :src="review.logoUrl" :alt="review.toolName" class="w-14 h-14 object-contain">
              </div>
              <div class="flex-grow">
                <div class="flex justify-between items-start mb-4">
                  <div>
                    <h3 class="font-bold text-xl text-slate-900">{{ review.toolName }}</h3>
                    <div class="flex gap-1 text-orange-400 mt-2">
                      <Star v-for="i in 5" :key="i" class="w-4 h-4" :class="{ 'fill-current': i <= review.rating, 'text-slate-200': i > review.rating }" />
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <button class="p-3 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all cursor-pointer border-none bg-transparent">
                      <Edit3 class="w-5 h-5" />
                    </button>
                    <button @click="handleDeleteReview(review.id)" class="p-3 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all cursor-pointer border-none bg-transparent">
                      <Trash2 class="w-5 h-5" />
                    </button>
                  </div>
                </div>
                <p class="text-slate-600 mb-4 leading-relaxed text-lg">{{ review.comment }}</p>
                <span class="text-sm font-medium text-slate-400">{{ review.date }}</span>
              </div>
            </div>
          </div>
          <div v-else class="glass-card p-16 text-center rounded-[3rem] border-dashed border-2 border-slate-200 bg-transparent shadow-none">
            <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-400">
              <MessageSquare class="w-12 h-12" />
            </div>
            <h3 class="text-2xl font-bold text-slate-800 mb-2">尚無評論</h3>
            <p class="text-slate-500">快去為您使用過的工具留下評價，分享您的使用心得吧！</p>
          </div>
        </div>

        <!-- AI 偏好設定 Tab -->
        <div v-if="activeTab === 'pref'" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">AI 偏好設定</h1>
            <p class="text-slate-500 text-lg">告訴我們您的背景與需求，讓我們能為您提供更精確的工具推薦。</p>
          </div>

          <div class="space-y-8">
            <!-- 自訂需求 Textarea -->
            <section class="glass-card p-10 rounded-[3rem] border border-white/20 shadow-xl">
              <div class="flex justify-between items-center mb-8">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-3">
                  <div class="p-2 bg-primary/10 rounded-xl">
                    <Sparkles class="w-6 h-6 text-primary" />
                  </div>
                  自訂背景與工具需求
                </h3>
              </div>
              
              <textarea 
                v-model="customContext"
                placeholder="請描述您的職業背景、具體的使用情境與偏好。
範例：我是一名在電商工作的行銷經理，經常需要撰寫產品文案與社群貼文。我偏好支援繁體中文且易於協作的工具，預算在每月 20 美金以內。"
                class="w-full h-56 p-6 rounded-[2rem] bg-slate-50/50 border border-slate-200 focus:ring-4 focus:ring-primary/10 focus:bg-white text-slate-800 placeholder:text-slate-400 resize-none outline-none transition-all text-lg leading-relaxed"
              ></textarea>
              
              <div class="mt-6 flex items-start gap-4 p-6 bg-blue-50/50 rounded-2xl border border-blue-100">
                <Info class="w-6 h-6 text-blue-500 mt-0.5 flex-shrink-0" />
                <div class="text-blue-700 leading-relaxed">
                  <p class="font-bold mb-1">專家提示：</p>
                  包含您的<span class="font-bold underline">職業</span>、<span class="font-bold underline">任務目標</span>（如寫論文、剪影片）以及<span class="font-bold underline">具體偏好</span>（如免費、中文介面），能讓推薦系統更懂您。
                </div>
              </div>
            </section>

            <!-- 預覽生成的 System Prompt -->
            <section class="bg-slate-900 rounded-[3.5rem] p-10 text-white shadow-2xl relative overflow-hidden">
              <div class="absolute top-0 right-0 p-8 opacity-10">
                <Sparkles class="w-32 h-32" />
              </div>
              <div class="relative z-10">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                  <h3 class="text-2xl font-bold flex items-center gap-3">
                    <Sparkles class="w-8 h-8 text-yellow-400" /> 您的專屬 AI 畫像
                  </h3>
                  <button class="px-6 py-2.5 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-bold transition-all border border-white/10 cursor-pointer">
                    複製畫像內容
                  </button>
                </div>
                <div class="bg-black/30 p-10 rounded-[2rem] font-mono text-base text-blue-100 leading-relaxed whitespace-pre-wrap border border-white/5 shadow-inner min-h-[160px]">
                  {{ customContext ? `使用者背景與偏好：\n${customContext}` : "尚未填寫任何資訊，建議至少填寫職業背景，讓我們為您打造專屬推薦。" }}
                </div>
                <div class="mt-10 flex justify-end">
                  <button class="bg-primary hover:bg-primary/90 text-white px-12 py-5 rounded-2xl font-bold shadow-2xl shadow-primary/40 transition-all flex items-center gap-3 border-none cursor-pointer text-lg">
                    <Save class="w-6 h-6" /> 儲存偏好設定
                  </button>
                </div>
              </div>
            </section>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(12px);
}
</style>

