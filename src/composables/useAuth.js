import { ref } from 'vue';
import axios from 'axios';

// 把狀態定義在函數外，這樣所有組件 import 都是共用同一份狀態 (類似 Pinia 的全域狀態)
const isLoggedIn = ref(false);
const username = ref('');

export function useAuth() {
  
  // 檢查是否登入，並向後端驗證 JWT
  const checkAuth = async () => {
    const token = localStorage.getItem('jwt_token');
    const user = JSON.parse(localStorage.getItem('user'));

    if (token && user) {
      try {
        // 向後端請求驗證 Token 到底是不是真的、或是不是被竄改過
        const response = await axios.post('/api/verify.php', { token });
        
        if (response.data.valid) {
          // Token 驗證成功
          isLoggedIn.value = true;
          username.value = user.username;
        } else {
          // Token 無效或是被篡改，強制登出！
          console.warn("JWT Token is invalid or tampered! Logging out...");
          handleLogout();
        }
      } catch (err) {
        // API 呼叫失敗，安全起見也當作無效
        console.error('Token 驗證失敗', err);
        handleLogout();
      }
    } else {
      isLoggedIn.value = false;
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('user');
    isLoggedIn.value = false;
    // 重整網頁清除其他可能的畫面殘留
    window.location.reload();
  };

  return {
    isLoggedIn,
    username,
    checkAuth,
    handleLogout
  };
}
