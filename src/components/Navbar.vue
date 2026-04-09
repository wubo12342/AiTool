<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Bot, LogIn, UserPlus, Menu, X, LogOut, LayoutDashboard } from 'lucide-vue-next'
import { useAuth } from '../composables/useAuth.js'

const isMenuOpen = ref(false)

const emit = defineEmits(['openAuth', 'logout'])

const { isLoggedIn, username, checkAuth, handleLogout } = useAuth()

onMounted(() => {
  checkAuth()
})

const doLogout = () => {
  handleLogout()
  isMenuOpen.value = false
  emit('logout')
}
</script>

<template>
  <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo -->
        <a href="/" class="flex-shrink-0 flex items-center gap-2 cursor-pointer">
          <Bot class="w-8 h-8 text-primary" />
          <span class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
            AI Hub
          </span>
        </a>

        <!-- Desktop Nav -->
        <div v-if="isLoggedIn" class="hidden md:flex items-center gap-8">
          <a href="/" class="text-slate-600 hover:text-primary font-medium transition-colors">首頁</a>
          <a href="/tools" class="text-slate-600 hover:text-primary font-medium transition-colors">所有工具</a>
          <a href="/compare" class="text-slate-600 hover:text-primary font-medium transition-colors">功能比較</a>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-3">
          <template v-if="!isLoggedIn">
            <button
              @click="emit('openAuth', 'login')"
              class="hidden sm:flex items-center gap-2 text-slate-600 hover:text-primary font-medium cursor-pointer transition-colors"
            >
              <LogIn class="w-5 h-5" />
              登入
            </button>

            <button
              @click="emit('openAuth', 'register')"
              class="hidden sm:flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-lg font-semibold transition-all shadow-lg shadow-primary/20 cursor-pointer"
            >
              <UserPlus class="w-5 h-5" />
              建立帳號
            </button>
          </template>

          <template v-else>
            <div class="hidden sm:flex items-center gap-4">
              <span class="text-slate-700 font-medium">你好，{{ username }}</span>

              <a
                href="/dashboard"
                class="flex items-center gap-2 text-slate-600 hover:text-primary font-medium transition-colors"
              >
                <LayoutDashboard class="w-5 h-5" />
                控制台
              </a>

              <button
                @click="doLogout"
                class="flex items-center gap-2 text-red-500 hover:text-red-700 font-medium cursor-pointer transition-colors"
              >
                <LogOut class="w-5 h-5" />
                登出
              </button>
            </div>
          </template>

          <!-- Mobile Menu Icon -->
          <button
            @click="isMenuOpen = !isMenuOpen"
            class="md:hidden p-2 text-slate-600"
            type="button"
          >
            <Menu v-if="!isMenuOpen" class="w-6 h-6" />
            <X v-else class="w-6 h-6" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-if="isMenuOpen" class="md:hidden bg-white border-b border-slate-200 py-4 px-4 space-y-3">
      <template v-if="isLoggedIn">
        <a href="/" class="block text-slate-600 font-medium">首頁</a>
        <a href="/tools" class="block text-slate-600 font-medium">所有工具</a>
        <a href="/compare" class="block text-slate-600 font-medium">功能比較</a>
        <hr class="border-slate-200" />
      </template>

      <template v-if="!isLoggedIn">
        <button
          @click="emit('openAuth', 'login'); isMenuOpen = false"
          class="w-full text-left flex items-center gap-2 py-2 text-slate-600"
          type="button"
        >
          <LogIn class="w-5 h-5" />
          登入
        </button>

        <button
          @click="emit('openAuth', 'register'); isMenuOpen = false"
          class="w-full text-left flex items-center gap-2 py-2 text-primary"
          type="button"
        >
          <UserPlus class="w-5 h-5" />
          建立帳號
        </button>
      </template>

      <template v-else>
        <div class="py-2 text-slate-700 font-medium">
          你好，{{ username }}
        </div>

        <a
          href="/dashboard"
          class="w-full text-left flex items-center gap-2 py-2 text-slate-600"
        >
          <LayoutDashboard class="w-5 h-5" />
          控制台
        </a>

        <button
          @click="doLogout"
          class="w-full text-left flex items-center gap-2 py-2 text-red-500"
          type="button"
        >
          <LogOut class="w-5 h-5" />
          登出
        </button>
      </template>
    </div>
  </nav>
</template>
