<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { Bot, X, Send, Sparkles, User, RefreshCw, MinusSquare } from 'lucide-vue-next'

const isOpen = ref(false)
const isTyping = ref(false)
const chatInput = ref('')
const scrollArea = ref(null)

const messages = reactive([
  {
    id: 1,
    role: 'ai',
    content: '您好！我是您的 AI 助理。有什麼我可以幫您的嗎？不論是尋找工具、還是設定 AI 偏好，我都可以為您服務。',
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  }
])

const scrollToBottom = async () => {
  await nextTick()
  if (scrollArea.value) {
    scrollArea.value.scrollTop = scrollArea.value.scrollHeight
  }
}

const toggleChat = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    scrollToBottom()
  }
}

const sendMessage = () => {
  if (!chatInput.value.trim() || isTyping.value) return

  const userMsg = {
    id: Date.now(),
    role: 'user',
    content: chatInput.value,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  }

  messages.push(userMsg)
  chatInput.value = ''
  scrollToBottom()

  // Simulate AI Response
  isTyping.value = true
  setTimeout(() => {
    const aiMsg = {
      id: Date.now() + 1,
      role: 'ai',
      content: getAutoResponse(userMsg.content),
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }
    messages.push(aiMsg)
    isTyping.value = false
    scrollToBottom()
  }, 1000)
}

const getAutoResponse = (query) => {
  const q = query.toLowerCase()
  if (q.includes('你好') || q.includes('hello')) return '您好！很高興見到您。如果您需要尋找 AI 工具，可以嘗試跟我說「幫我推薦寫作工具」！'
  if (q.includes('推薦') || q.includes('找')) return '沒問題！我們平台上有很多優秀的工具。例如寫作推薦 ChatGPT，繪圖推薦 Midjourney。您可以在個人專區設定您的偏好，讓我推薦得更準確喔！'
  if (q.includes('收藏') || q.includes('我的')) return '您可以在個人專區查看您的收藏工具。點擊導覽列的「個人專區」即可進入。'
  return '這是一個很好的問題！目前我還在學習中，但我可以幫您聯絡客服，或是您也可以直接到我們平台的各個分類探索工具。'
}
</script>

<template>
  <div class="fixed bottom-6 right-6 z-[100] flex flex-col items-end">
    
    <!-- Chat Window -->
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="translate-y-10 opacity-0 scale-95"
      enter-to-class="translate-y-0 opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="translate-y-0 opacity-100 scale-100"
      leave-to-class="translate-y-10 opacity-0 scale-95"
    >
      <div v-if="isOpen" class="mb-4 w-[360px] sm:w-[400px] h-[550px] bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 flex flex-col overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 bg-gradient-to-r from-primary to-secondary text-white shrink-0 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-md">
              <Bot class="w-6 h-6" />
            </div>
            <div>
              <h3 class="font-bold">AI 智慧小幫手</h3>
              <div class="flex items-center gap-1.5 text-xs text-white/80">
                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                在線為您服務
              </div>
            </div>
          </div>
          <button @click="isOpen = false" class="p-2 hover:bg-white/10 rounded-full transition-colors border-none bg-transparent text-white cursor-pointer">
            <X class="w-6 h-6" />
          </button>
        </div>

        <!-- Messages Area -->
        <div 
          ref="scrollArea"
          class="flex-grow p-6 overflow-y-auto bg-slate-50 space-y-4 scroll-smooth"
        >
          <div 
            v-for="msg in messages" 
            :key="msg.id"
            :class="msg.role === 'ai' ? 'justify-start' : 'justify-end'"
            class="flex items-end gap-2"
          >
            <div 
              v-if="msg.role === 'ai'" 
              class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center shrink-0"
            >
              <Sparkles class="w-4 h-4 text-primary" />
            </div>
            
            <div 
              :class="msg.role === 'ai' ? 'bg-white text-slate-700 rounded-bl-none' : 'bg-primary text-white rounded-br-none'"
              class="max-w-[80%] p-4 rounded-3xl shadow-sm text-sm leading-relaxed"
            >
              {{ msg.content }}
              <div :class="msg.role === 'ai' ? 'text-slate-400' : 'text-white/60'" class="text-[10px] mt-1 text-right">
                {{ msg.time }}
              </div>
            </div>

            <div 
              v-if="msg.role === 'user'" 
              class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0 border border-primary/10"
            >
              <User class="w-4 h-4 text-primary" />
            </div>
          </div>

          <!-- Typing Indicator -->
          <div v-if="isTyping" class="flex items-end gap-2">
            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center shrink-0">
              <Sparkles class="w-4 h-4 text-primary" />
            </div>
            <div class="bg-white p-4 rounded-3xl rounded-bl-none shadow-sm flex gap-1">
              <span class="w-1.5 h-1.5 bg-slate-300 rounded-full animate-bounce"></span>
              <span class="w-1.5 h-1.5 bg-slate-300 rounded-full animate-bounce [animation-delay:0.2s]"></span>
              <span class="w-1.5 h-1.5 bg-slate-300 rounded-full animate-bounce [animation-delay:0.4s]"></span>
            </div>
          </div>
        </div>

        <!-- Input Area -->
        <div class="p-6 bg-white border-t border-slate-100">
          <form @submit.prevent="sendMessage" class="relative">
            <input 
              v-model="chatInput"
              type="text" 
              placeholder="請輸入任何問題..."
              class="w-full pl-5 pr-14 py-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 focus:ring-primary/20 text-sm transition-all shadow-inner"
            >
            <button 
              type="submit"
              :disabled="!chatInput.trim() || isTyping"
              class="absolute right-2 top-2 p-2 bg-primary text-white rounded-xl hover:bg-primary/90 disabled:bg-slate-300 transition-all border-none cursor-pointer shadow-lg shadow-primary/20"
            >
              <Send class="w-5 h-5" />
            </button>
          </form>
          <p class="text-[10px] text-slate-400 text-center mt-3">
            由 AI HUB 智慧引擎提供技術支持
          </p>
        </div>
      </div>
    </Transition>

    <!-- Trigger Button (FAB) -->
    <button 
      @click="toggleChat"
      class="group relative w-16 h-16 bg-primary hover:bg-primary/90 text-white rounded-full shadow-2xl flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 cursor-pointer border-none"
    >
      <div class="absolute -top-1 -right-1" v-if="!isOpen">
        <span class="relative flex h-4 w-4">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-4 w-4 bg-green-500 border-2 border-white"></span>
        </span>
      </div>
      
      <Bot v-if="!isOpen" class="w-8 h-8 group-hover:rotate-12 transition-transform" />
      <MinusSquare v-else class="w-8 h-8" />
      
      <!-- Tooltip -->
      <div class="absolute right-full mr-4 bg-slate-900 text-white text-xs px-3 py-2 rounded-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity font-medium whitespace-nowrap shadow-xl">
        需要幫助嗎？
      </div>
    </button>
  </div>
</template>

<style scoped>
/* 自定義滾動條樣式 */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>
