<script setup>
import { ref } from 'vue'
import { RouterView } from 'vue-router'

import Navbar from './components/Navbar.vue'
import AuthModal from './components/AuthModal.vue'
import InteractiveBackground from './components/InteractiveBackground.vue'
import Lobby from './components/Lobby.vue'
import AiAssistant from './components/AiAssistant.vue'
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
  <div class="relative min-h-screen bg-slate-50">
    <!-- 背景動畫：未登入才顯示 -->
    <InteractiveBackground
      v-if="!isLoggedIn"
      :paused="showAuthModal"
    />

    <div class="relative z-10 flex flex-col min-h-screen">
      <Navbar @openAuth="openAuth" />

      <main class="flex-grow">
        <!-- 未登入 -->
        <Lobby
          v-if="!isLoggedIn"
          @openAuth="openAuth"
        />

        <!-- 已登入：交給 router 控制 Home / ToolDetail / Profile -->
        <RouterView v-else />
      </main>
    </div>

    <AuthModal
      :isOpen="showAuthModal"
      :initialMode="authMode"
      @close="showAuthModal = false"
    />

    <AiAssistant v-if="isLoggedIn" />
  </div>
</template>