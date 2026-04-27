<script setup>
import { ref } from 'vue'
import Navbar from './components/Navbar.vue'
import AuthModal from './components/AuthModal.vue'
import InteractiveBackground from './components/InteractiveBackground.vue'
import Lobby from './components/Lobby.vue'
import Home from './components/Home.vue'
import Profile from './components/Profile.vue'
import AiAssistant from './components/AiAssistant.vue'
import { useAuth } from './composables/useAuth.js'

const showAuthModal = ref(false)
const authMode = ref('login')
const currentView = ref('home')

const { isLoggedIn } = useAuth()

const openAuth = (mode) => {
  authMode.value = mode
  showAuthModal.value = true
}

const handleNavigate = (view) => {
  currentView.value = view
}
</script>

<template>
  <div class="relative min-h-screen bg-slate-50">
    
    <!-- 背景動畫（未登入才顯示） -->
    <InteractiveBackground 
      v-if="!isLoggedIn" 
      :paused="showAuthModal" 
    />

    <!-- 內容層 -->
    <div class="relative z-10 flex flex-col min-h-screen">
      
      <!-- Navbar -->
      <Navbar 
        @openAuth="openAuth" 
        @navigate="handleNavigate" 
      />

      <!-- 主內容 -->
      <main class="flex-grow">
        
        <!-- 未登入 -->
        <Lobby 
          v-if="!isLoggedIn" 
          @openAuth="openAuth" 
        />

        <!-- 已登入 -->
        <template v-else>
          <Home v-if="currentView === 'home'" />
          <Profile v-else-if="currentView === 'profile'" />
        </template>

      </main>
    </div>

    <!-- 登入 / 註冊 Modal -->
    <AuthModal
      :isOpen="showAuthModal"
      :initialMode="authMode"
      @close="showAuthModal = false"
    />

    <!-- ✅ AI 助手（登入後才顯示） -->
    <AiAssistant v-if="isLoggedIn" />

  </div>
</template>