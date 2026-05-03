import { ref } from 'vue';

// 全域狀態，確保所有組件共用同一份收藏清單
const favorites = ref([]);

// 從 LocalStorage 載入初始資料
const loadFavorites = () => {
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

// 立即執行載入
loadFavorites();

export function useFavorites() {
  
  // 切換收藏狀態
  const toggleFavorite = (tool) => {
    const index = favorites.value.findIndex(f => f.id === tool.id);
    if (index === -1) {
      // 加入收藏
      favorites.value.push({
        ...tool,
        isFavorited: true
      });
    } else {
      // 移除收藏
      favorites.value.splice(index, 1);
    }
    // 同步到 LocalStorage
    localStorage.setItem('user_favorites', JSON.stringify(favorites.value));
  };

  // 檢查是否已收藏
  const isFavorited = (id) => {
    return favorites.value.some(f => f.id === id);
  };

  return {
    favorites,
    toggleFavorite,
    isFavorited
  };
}
