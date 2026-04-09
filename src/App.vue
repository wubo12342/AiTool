<script setup>
import { ref } from 'vue'
import Navbar from './components/Navbar.vue'
import AuthModal from './components/AuthModal.vue'
import InteractiveBackground from './components/InteractiveBackground.vue'
import Lobby from './components/Lobby.vue'
import Home from './components/Home.vue'
import { useAuth } from './composables/useAuth.js'

const showAuthModal = ref(false)
const authMode = ref('login')

const { isLoggedIn } = useAuth()

const openAuth = (mode) => {
  authMode.value = mode
  showAuthModal.value = true
}
</script>

<template>
  <div class="relative min-h-screen">
    <!-- 背景裝飾 -->
    <InteractiveBackground />

    <!-- 內容層 -->
    <div class="relative z-10 flex flex-col min-h-screen">
      <!-- 導覽列 -->
      <Navbar @openAuth="openAuth" />

      <!-- 主要內容區：未登入顯示大廳，已登入顯示主頁 -->
      <main class="flex-grow">
        <Lobby v-if="!isLoggedIn" @openAuth="openAuth" />
        <Home v-else />
      </main>
    </div>

    <!-- 登入/註冊彈跳視窗 -->
    <AuthModal
      :isOpen="showAuthModal"
      :initialMode="authMode"
      @close="showAuthModal = false"
    />
  </div>
</template>
