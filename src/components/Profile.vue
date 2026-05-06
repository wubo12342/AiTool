<script setup>
import { ref, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import { 
  Bookmark, 
  MessageSquare, 
  Trash2, 
  Edit3, 
  Star,
  RefreshCw
} from 'lucide-vue-next';
import ToolCard from './ToolCard.vue';
import { useAuth } from '../composables/useAuth.js';
import { useFavorites } from '../composables/useFavorites.js';

const { username } = useAuth();
const { favorites, toggleFavorite } = useFavorites();
const activeTab = ref('favorites');

const reviews = ref([]);

// 讀取資料
onMounted(async () => {
  try {
    const response = await fetch('/api/update_profile.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        action: 'get',
        token: localStorage.getItem('jwt_token')
      })
    });
    const data = await response.json();
    
    // 處理並顯示評論
    if (data.reviews) {
      reviews.value = data.reviews.map(r => ({
        ...r,
        logoUrl: r.logo_url || `https://api.dicebear.com/7.x/identicon/svg?seed=${encodeURIComponent(r.toolName)}`,
        date: r.comment_time ? r.comment_time.split(' ')[0] : '未知日期'
      }));
    }
  } catch (error) {
    console.error('讀取個人資料失敗:', error);
  }
});

const handleDeleteReview = async (id) => {
  if (!confirm('確定要刪除這則評論嗎？')) return;
  
  try {
    const response = await fetch('/api/delete_review.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        review_id: id,
        token: localStorage.getItem('jwt_token')
      })
    });
    const data = await response.json();
    if (data.success) {
      // 重新載入列表
      location.reload();
    } else {
      alert('刪除失敗: ' + (data.error || '未知錯誤'));
    }
  } catch (error) {
    alert('網路錯誤，請稍後再試。');
  }
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
              isFavorited
              disableFlip
              @toggleFavorite="toggleFavorite(tool)"
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
            <div v-for="review in reviews" :key="review.review_ID" class="glass-card p-8 rounded-[2.5rem] flex gap-8 hover:shadow-2xl transition-all border border-white/20">
              <div class="w-20 h-20 rounded-3xl bg-white shadow-md flex-shrink-0 overflow-hidden flex items-center justify-center border border-slate-100">
                <img :src="review.logoUrl" :alt="review.toolName" class="w-14 h-14 object-contain">
              </div>
              <div class="flex-grow">
                <div class="flex justify-between items-start mb-4">
                  <div>
                    <h3 class="font-bold text-xl text-slate-900">{{ review.toolName }}</h3>
                    <div class="flex gap-1 text-orange-400 mt-2">
                      <Star v-for="i in 5" :key="i" class="w-4 h-4" :class="{ 'fill-current': i <= review.stars, 'text-slate-200': i > review.stars }" />
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <RouterLink :to="`/tool/${review.tool_ID}`" class="p-3 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all cursor-pointer border-none bg-transparent">
                      <Edit3 class="w-5 h-5" />
                    </RouterLink>
                    <button @click="handleDeleteReview(review.review_ID)" class="p-3 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all cursor-pointer border-none bg-transparent">
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

      </div>
    </div>
  </div>
</template>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(12px);
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
