import { ref } from 'vue';
import axios from 'axios';

// 全域狀態，確保所有組件共用同一份收藏清單
const favorites = ref([]);

// 從 LocalStorage 載入初始資料 (墊檔用)
const loadLocalFavorites = () => {
  const stored = localStorage.getItem('user_favorites');
  if (stored) {
    try {
      favorites.value = JSON.parse(stored);
    } catch (e) {
      console.error('Failed to parse favorites from localStorage', e);
      favorites.value = [];
    }
  }
};

// 立即執行本地載入
loadLocalFavorites();

export function useFavorites() {
  
  // 從資料庫同步收藏清單
  // H12 — 改用 POST，把 token 放在 body，不要塞在 URL query
  const syncFavorites = async () => {
    const token = localStorage.getItem('jwt_token');
    if (!token) return;

    try {
      const response = await axios.post('/api/get_favorites.php', { token });
      if (response.data.success) {
        favorites.value = response.data.favorites;
        localStorage.setItem('user_favorites', JSON.stringify(favorites.value));
      }
    } catch (err) {
      console.error('同步收藏清單失敗', err);
    }
  };

  // 切換收藏狀態
  const toggleFavorite = async (tool) => {
    const token = localStorage.getItem('jwt_token');
    
    // 1. 先做前端 UI 回饋 (Optimistic UI)
    const index = favorites.value.findIndex(f => f.id === tool.id);
    const isAdding = index === -1;
    
    if (isAdding) {
      favorites.value.push({
        ...tool,
        isFavorited: true
      });
    } else {
      favorites.value.splice(index, 1);
    }
    
    // 更新本地儲存
    localStorage.setItem('user_favorites', JSON.stringify(favorites.value));

    // 2. 如果已登入，則同步到資料庫
    if (token) {
      try {
        const response = await axios.post('/api/toggle_favorite.php', {
          tool_id: tool.id,
          token: token
        });
        
        if (!response.data.success) {
          console.error('資料庫同步失敗:', response.data.message);
          // 可以在這裡實作復原邏輯，但為了 UX 通常先讓前端動
        }
      } catch (err) {
        console.error('收藏 API 呼叫失敗', err);
      }
    }
  };

  // 檢查是否已收藏
  const isFavorited = (id) => {
    return favorites.value.some(f => f.id === id);
  };

  return {
    favorites,
    toggleFavorite,
    isFavorited,
    syncFavorites
  };
}
