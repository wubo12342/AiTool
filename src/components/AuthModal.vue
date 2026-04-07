<script setup>
import { ref, reactive } from 'vue';
import { X, User, Lock, Loader2, AlertCircle, CheckCircle2 } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps(['isOpen', 'initialMode']);
const emit = defineEmits(['close']);

const mode = ref(props.initialMode || 'login'); // 'login' or 'register'
const loading = ref(false);
const error = ref('');
const success = ref('');

const formData = reactive({
  username: '',
  password: '',
  confirmPassword: ''
});

const resetForm = () => {
  formData.username = '';
  formData.password = '';
  formData.confirmPassword = '';
  error.value = '';
  success.value = '';
};

const handleAuth = async () => {
  error.value = '';
  success.value = '';
  
  if (mode.value === 'register' && formData.password !== formData.confirmPassword) {
    error.value = '兩次輸入的密碼不一致';
    return;
  }

  loading.value = true;
  const endpoint = mode.value === 'login' ? '/api/login.php' : '/api/register.php';
  
  try {
    const response = await axios.post(endpoint, {
      username: formData.username,
      password: formData.password
    });

    if (mode.value === 'login') {
      localStorage.setItem('jwt_token', response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
      success.value = '登入成功！正為您跳轉...';
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } else {
      success.value = '註冊成功！現在您可以登入了。';
      mode.value = 'login';
      formData.password = '';
      formData.confirmPassword = '';
    }
  } catch (err) {
    error.value = err.response?.data?.error || '發生錯誤，請稍後再試';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-sm">
    <div class="glass-card w-full max-w-md overflow-hidden relative animate-in fade-in zoom-in duration-300">
      <!-- Close Button -->
      <button @click="emit('close')" class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-600 transition-colors">
        <X class="w-6 h-6" />
      </button>

      <div class="p-8">
        <div class="text-center mb-8">
          <h2 class="text-3xl font-bold text-slate-900 mb-2">{{ mode === 'login' ? '歡迎回來' : '加入我們' }}</h2>
          <p class="text-slate-500">{{ mode === 'login' ? '請登入您的帳號以繼續' : '建立帳號以享受完整功能' }}</p>
        </div>

        <!-- Mode Switcher -->
        <div class="flex p-1 bg-slate-100 rounded-xl mb-8">
          <button @click="mode = 'login'; resetForm()" 
            :class="mode === 'login' ? 'bg-white shadow-sm text-primary' : 'text-slate-500 hover:text-slate-700'"
            class="flex-1 py-2 rounded-lg font-bold transition-all text-sm">
            登入
          </button>
          <button @click="mode = 'register'; resetForm()" 
            :class="mode === 'register' ? 'bg-white shadow-sm text-primary' : 'text-slate-500 hover:text-slate-700'"
            class="flex-1 py-2 rounded-lg font-bold transition-all text-sm">
            註冊
          </button>
        </div>

        <!-- Alert Messages -->
        <div v-if="error" class="mb-6 p-4 bg-red-50 text-red-600 rounded-xl flex items-center gap-3 border border-red-100 text-sm animate-in slide-in-from-top-2">
          <AlertCircle class="w-5 h-5 flex-shrink-0" />
          {{ error }}
        </div>
        <div v-if="success" class="mb-6 p-4 bg-green-50 text-green-600 rounded-xl flex items-center gap-3 border border-green-100 text-sm animate-in slide-in-from-top-2">
          <CheckCircle2 class="w-5 h-5 flex-shrink-0" />
          {{ success }}
        </div>

        <!-- Form -->
        <form @submit.prevent="handleAuth" class="space-y-6">
          <div class="space-y-2">
            <label class="text-sm font-bold text-slate-700 ml-1">帳號名稱</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                <User class="w-5 h-5" />
              </div>
              <input v-model="formData.username" type="text" required placeholder="請輸入帳號"
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-bold text-slate-700 ml-1">密碼</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                <Lock class="w-5 h-5" />
              </div>
              <input v-model="formData.password" type="password" required placeholder="請輸入密碼"
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
            </div>
          </div>

          <div v-if="mode === 'register'" class="space-y-2 animate-in fade-in duration-300">
            <label class="text-sm font-bold text-slate-700 ml-1">確認密碼</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                <Lock class="w-5 h-5" />
              </div>
              <input v-model="formData.confirmPassword" type="password" required placeholder="請再次輸入密碼"
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
            </div>
          </div>

          <button type="submit" :disabled="loading"
            class="w-full py-4 bg-primary hover:bg-primary/90 disabled:bg-slate-300 text-white rounded-xl font-bold text-lg shadow-xl shadow-primary/20 transition-all flex items-center justify-center gap-2">
            <Loader2 v-if="loading" class="w-5 h-5 animate-spin" />
            {{ mode === 'login' ? '登入帳號' : '立即註冊' }}
          </button>
        </form>

        <p class="mt-8 text-center text-slate-500 text-sm">
          {{ mode === 'login' ? '還沒有帳號嗎？' : '已經有帳號了？' }}
          <button @click="mode = mode === 'login' ? 'register' : 'login'; resetForm()" class="text-primary font-bold hover:underline cursor-pointer">
            {{ mode === 'login' ? '立即註冊' : '登入帳號' }}
          </button>
        </p>
      </div>
    </div>
  </div>
</template>
