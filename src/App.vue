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
    <!-- 背景裝飾：僅在未登入時顯示互動動畫 (開窗時暫停以提升效能) -->
    <InteractiveBackground v-if="!isLoggedIn" :paused="showAuthModal" />

    <!-- 內容層 -->
    <div class="relative z-10 flex flex-col min-h-screen">
      <!-- 導覽列 -->
      <Navbar @openAuth="openAuth" @navigate="handleNavigate" />

      <!-- 主要內容區：未登入顯示大廳，已登入顯示主頁或個人專區 -->
      <main class="flex-grow">
        <Lobby v-if="!isLoggedIn" @openAuth="openAuth" />
        <template v-else>
          <Home v-if="currentView === 'home'" />
          <Profile v-else-if="currentView === 'profile'" />
        </template>
      </main>
    </div>

    <!-- 登入/註冊彈跳視窗 -->
    <AuthModal
      :isOpen="showAuthModal"
      :initialMode="authMode"
      @close="showAuthModal = false"
    />

    <!-- AI 智慧小幫手 -->
    <AiAssistant />
  </div>
</template>
