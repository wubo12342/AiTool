<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Bot, LogIn, UserPlus, Menu, X, LogOut, LayoutDashboard } from 'lucide-vue-next'
import { useAuth } from '../composables/useAuth.js'

const isMenuOpen = ref(false)

const emit = defineEmits(['openAuth', 'logout', 'navigate'])

const { isLoggedIn, username, checkAuth, handleLogout } = useAuth()

onMounted(() => {
  checkAuth()
})

const doLogout = () => {
  handleLogout()
  isMenuOpen.value = false
  emit('logout')
}

const navigate = (view) => {
  emit('navigate', view)
  isMenuOpen.value = false
}
</script>

<template>
  <nav class="sticky top-0 z-50 bg-white/98 border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo -->
        <button @click="navigate('home')" class="flex-shrink-0 flex items-center gap-2 cursor-pointer border-none bg-transparent">
          <Bot class="w-8 h-8 text-primary" />
          <span class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
            AI Hub
          </span>
        </button>

        <!-- Desktop Nav -->
        <div v-if="isLoggedIn" class="hidden md:flex items-center gap-8">
          <button @click="navigate('home')" class="text-slate-600 hover:text-primary font-medium transition-colors cursor-pointer border-none bg-transparent">首頁</button>
          <a href="/tools" class="text-slate-600 hover:text-primary font-medium transition-colors">所有工具</a>
          <a href="/compare" class="text-slate-600 hover:text-primary font-medium transition-colors">功能比較</a>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-3">
          <template v-if="!isLoggedIn">
            <button
              @click="emit('openAuth', 'login')"
              class="hidden sm:flex items-center gap-2 text-slate-600 hover:text-primary font-medium cursor-pointer transition-colors border-none bg-transparent"
            >
              <LogIn class="w-5 h-5" />
              登入
            </button>

            <button
              @click="emit('openAuth', 'register')"
              class="hidden sm:flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-lg font-semibold transition-all shadow-lg shadow-primary/20 cursor-pointer border-none"
            >
              <UserPlus class="w-5 h-5" />
              建立帳號
            </button>
          </template>

          <template v-else>
            <div class="hidden sm:flex items-center gap-4">
              <button
                @click="navigate('profile')"
                class="flex items-center gap-2 text-slate-600 hover:text-primary font-medium transition-colors cursor-pointer border-none bg-transparent whitespace-nowrap"
              >
                <LayoutDashboard class="w-5 h-5" />
                個人專區
              </button>

              <button
                @click="doLogout"
                class="flex items-center gap-2 text-red-500 hover:text-red-700 font-medium cursor-pointer transition-colors border-none bg-transparent whitespace-nowrap"
              >
                <LogOut class="w-5 h-5" />
                登出
              </button>
            </div>
          </template>

          <!-- Mobile Menu Icon -->
          <button
            @click="isMenuOpen = !isMenuOpen"
            class="md:hidden p-2 text-slate-600 border-none bg-transparent"
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
        <button @click="navigate('home')" class="block w-full text-left text-slate-600 font-medium py-2 border-none bg-transparent">首頁</button>
        <a href="/tools" class="block text-slate-600 font-medium py-2">所有工具</a>
        <a href="/compare" class="block text-slate-600 font-medium py-2">功能比較</a>
        <hr class="border-slate-200" />
      </template>

      <template v-if="!isLoggedIn">
        <button
          @click="emit('openAuth', 'login'); isMenuOpen = false"
          class="w-full text-left flex items-center gap-2 py-2 text-slate-600 border-none bg-transparent"
          type="button"
        >
          <LogIn class="w-5 h-5" />
          登入
        </button>

        <button
          @click="emit('openAuth', 'register'); isMenuOpen = false"
          class="w-full text-left flex items-center gap-2 py-2 text-primary border-none bg-transparent"
          type="button"
        >
          <UserPlus class="w-5 h-5" />
          建立帳號
        </button>
      </template>

      <template v-else>
        <button
          @click="navigate('profile')"
          class="w-full text-left flex items-center gap-2 py-2 text-slate-600 border-none bg-transparent"
        >
          <LayoutDashboard class="w-5 h-5" />
          個人專區
        </button>

        <button
          @click="doLogout"
          class="w-full text-left flex items-center gap-2 py-2 text-red-500 border-none bg-transparent"
          type="button"
        >
          <LogOut class="w-5 h-5" />
          登出
        </button>
      </template>
    </div>
  </nav>
</template>
