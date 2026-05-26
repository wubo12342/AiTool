<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { Bot, LogIn, UserPlus, Menu, X, LogOut, LayoutDashboard, Moon, Sun, ShieldCheck } from 'lucide-vue-next'
import { useAuth } from '../composables/useAuth.js'
import { useFavorites } from '../composables/useFavorites.js'
import { useDarkMode } from '../composables/useDarkMode.js'

const router = useRouter()
const isMenuOpen = ref(false)

const emit = defineEmits(['openAuth'])

const { isLoggedIn, userRole, checkAuth, handleLogout } = useAuth()
const { syncFavorites } = useFavorites()
const { isDark, toggle: toggleDark } = useDarkMode()

onMounted(async () => {
  await checkAuth()
  if (isLoggedIn.value) {
    syncFavorites()
  }
})

const doLogout = () => {
  handleLogout()
  isMenuOpen.value = false
  router.push('/')
}

const closeMenu = () => {
  isMenuOpen.value = false
}
</script>

<template>
  <nav class="sticky top-0 z-50 bg-white/98 dark:bg-slate-900/95 border-b border-slate-200 dark:border-slate-700 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo -->
        <RouterLink
          to="/"
          class="flex-shrink-0 flex items-center gap-2 no-underline"
          @click="closeMenu"
        >
          <Bot class="w-8 h-8 text-primary" />
          <span class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
            AI Hub
          </span>
        </RouterLink>

        <!-- Desktop Navigation -->
        <div v-if="isLoggedIn" class="hidden md:flex items-center gap-8">
          <RouterLink to="/" class="text-slate-600 dark:text-slate-300 hover:text-primary font-medium transition-colors no-underline">
            首頁
          </RouterLink>

          <RouterLink to="/tools" class="text-slate-600 dark:text-slate-300 hover:text-primary font-medium transition-colors no-underline">
            所有工具
          </RouterLink>

          <RouterLink to="/compare" class="text-slate-600 dark:text-slate-300 hover:text-primary font-medium transition-colors no-underline">
            功能比較
          </RouterLink>

          <RouterLink
            v-if="Number(userRole) === 1"
            to="/admin"
            class="flex items-center gap-1.5 text-red-500 hover:text-red-700 font-semibold transition-colors no-underline"
          >
            <ShieldCheck class="w-4 h-4" />
            管理後台
          </RouterLink>
        </div>

        <!-- Auth Actions -->
        <div class="flex items-center gap-3">
          <!-- Dark Mode Toggle -->
          <button
            @click="toggleDark"
            class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors border-none bg-transparent cursor-pointer"
            :title="isDark ? '切換亮色模式' : '切換深色模式'"
          >
            <Sun v-if="isDark" class="w-5 h-5" />
            <Moon v-else class="w-5 h-5" />
          </button>

          <template v-if="!isLoggedIn">
            <button
              @click="emit('openAuth', 'login')"
              class="hidden sm:flex items-center gap-2 text-slate-600 dark:text-slate-300 hover:text-primary font-medium cursor-pointer transition-colors border-none bg-transparent"
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
              <RouterLink
                to="/profile"
                class="flex items-center gap-2 text-slate-600 dark:text-slate-300 hover:text-primary font-medium transition-colors no-underline whitespace-nowrap"
              >
                <LayoutDashboard class="w-5 h-5" />
                個人專區
              </RouterLink>

              <button
                @click="doLogout"
                class="flex items-center gap-2 text-red-500 hover:text-red-700 font-medium cursor-pointer transition-colors border-none bg-transparent whitespace-nowrap"
              >
                <LogOut class="w-5 h-5" />
                登出
              </button>
            </div>
          </template>

          <!-- Mobile Menu Toggle -->
          <button
            @click="isMenuOpen = !isMenuOpen"
            class="md:hidden p-2 text-slate-600 dark:text-slate-300 border-none bg-transparent cursor-pointer"
            aria-label="Toggle menu"
          >
            <Menu v-if="!isMenuOpen" class="w-6 h-6" />
            <X v-else class="w-6 h-6" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation -->
    <div v-if="isMenuOpen" class="md:hidden bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 py-4 px-4 space-y-3 animate-in slide-in-from-top-2 duration-200">
      <template v-if="isLoggedIn">
        <RouterLink to="/" @click="closeMenu" class="block w-full text-slate-600 dark:text-slate-300 font-medium py-2 no-underline">
          首頁
        </RouterLink>

        <RouterLink to="/tools" @click="closeMenu" class="block w-full text-slate-600 dark:text-slate-300 font-medium py-2 no-underline">
          所有工具
        </RouterLink>

        <RouterLink to="/compare" @click="closeMenu" class="block w-full text-slate-600 dark:text-slate-300 font-medium py-2 no-underline">
          功能比較
        </RouterLink>

        <RouterLink
          v-if="Number(userRole) === 1"
          to="/admin"
          @click="closeMenu"
          class="flex items-center gap-2 w-full text-red-500 font-semibold py-2 no-underline"
        >
          <ShieldCheck class="w-5 h-5" />
          管理後台
        </RouterLink>

        <hr class="border-slate-200 dark:border-slate-700" />
      </template>

      <template v-if="!isLoggedIn">
        <button
          @click="emit('openAuth', 'login'); isMenuOpen = false"
          class="w-full text-left flex items-center gap-2 py-2 text-slate-600 dark:text-slate-300 border-none bg-transparent cursor-pointer"
        >
          <LogIn class="w-5 h-5" />
          登入
        </button>

        <button
          @click="emit('openAuth', 'register'); isMenuOpen = false"
          class="w-full text-left flex items-center gap-2 py-2 text-primary border-none bg-transparent cursor-pointer"
        >
          <UserPlus class="w-5 h-5" />
          建立帳號
        </button>
      </template>

      <template v-else>
        <RouterLink
          to="/profile"
          @click="closeMenu"
          class="w-full flex items-center gap-2 py-2 text-slate-600 dark:text-slate-300 no-underline"
        >
          <LayoutDashboard class="w-5 h-5" />
          個人專區
        </RouterLink>

        <button
          @click="doLogout"
          class="w-full text-left flex items-center gap-2 py-2 text-red-500 border-none bg-transparent cursor-pointer"
        >
          <LogOut class="w-5 h-5" />
          登出
        </button>
      </template>
    </div>
  </nav>
</template>