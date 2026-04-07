<script setup>
import { ref, onMounted } from 'vue';
import { Bot, LogIn, UserPlus, Menu, LogOut, LayoutDashboard } from 'lucide-vue-next';

const isMenuOpen = ref(false);
const isLoggedIn = ref(false);
const username = ref('');

const emit = defineEmits(['openAuth']);

const checkAuth = () => {
  const token = localStorage.getItem('jwt_token');
  const user = JSON.parse(localStorage.getItem('user'));
  if (token && user) {
    isLoggedIn.value = true;
    username.value = user.username;
  } else {
    isLoggedIn.value = false;
  }
};

const handleLogout = () => {
  localStorage.removeItem('jwt_token');
  localStorage.removeItem('user');
  isLoggedIn.value = false;
  window.location.reload();
};

onMounted(() => {
  checkAuth();
});
</script>

<template>
  <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo -->
        <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer">
          <Bot class="w-8 h-8 text-primary" />
          <span class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">AI Hub</span>
        </div>

        <!-- Desktop Nav -->
        <div class="hidden md:flex items-center gap-8">
          <a href="/" class="text-slate-600 hover:text-primary font-medium transition-colors">首頁</a>
          <a href="/tools" class="text-slate-600 hover:text-primary font-medium transition-colors">所有工具</a>
          <a href="/compare" class="text-slate-600 hover:text-primary font-medium transition-colors">功能比較</a>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-4">
          <template v-if="!isLoggedIn">
            <button @click="emit('openAuth', 'login')" class="hidden sm:flex items-center gap-2 text-slate-600 hover:text-primary font-medium cursor-pointer">
              <LogIn class="w-5 h-5" />
              登入
            </button>
            <button @click="emit('openAuth', 'register')" class="bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-lg font-semibold transition-all shadow-lg shadow-primary/20 cursor-pointer">
              建立帳號
            </button>
          </template>
          
          <template v-else>
            <div class="flex items-center gap-4">
              <span class="text-slate-700 font-medium">你好, {{ username }}</span>
              <button @click="handleLogout" class="flex items-center gap-2 text-red-500 hover:text-red-700 font-medium cursor-pointer">
                <LogOut class="w-5 h-5" />
                登出
              </button>
            </div>
          </template>

          <!-- Mobile Menu Icon -->
          <button @click="isMenuOpen = !isMenuOpen" class="md:hidden p-2 text-slate-600">
            <Menu class="w-6 h-6" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-if="isMenuOpen" class="md:hidden bg-white border-b border-slate-200 py-4 px-4 space-y-3">
      <a href="/" class="block text-slate-600 font-medium">首頁</a>
      <a href="/tools" class="block text-slate-600 font-medium">所有工具</a>
      <a href="/compare" class="block text-slate-600 font-medium">功能比較</a>
      <hr />
      <template v-if="!isLoggedIn">
        <button @click="emit('openAuth', 'login')" class="w-full text-left flex items-center gap-2 py-2 text-slate-600">
          <LogIn class="w-5 h-5" /> 登入
        </button>
      </template>
      <template v-else>
        <button @click="handleLogout" class="w-full text-left flex items-center gap-2 py-2 text-red-500">
          <LogOut class="w-5 h-5" /> 登出
        </button>
      </template>
    </div>
  </nav>
</template>
