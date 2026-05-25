<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { Bot, X, Send, Sparkles, User, RefreshCw, MinusSquare, Settings, Check, ArrowLeft } from 'lucide-vue-next'
import axios from 'axios'
import { marked } from 'marked'
import DOMPurify from 'dompurify'
import { useRouter } from 'vue-router'

const router = useRouter()
const isOpen = ref(false)
const isTyping = ref(false)
const chatInput = ref('')
const scrollArea = ref(null)
const showSettings = ref(false)

// 個人偏好設定
const userPreferences = ref('')
const isSavingPrefs = ref(false)

const messages = reactive([
  {
    id: 1,
    role: 'ai',
    content: '您好！我是您的 AI 助理。您可以點擊右上角的 **設定圖示** 來告訴我您的職業背景或偏好，讓我能提供更精準的建議。',
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
    fetchPreferences()
  }
}

const fetchPreferences = async () => {
  const token = localStorage.getItem('jwt_token')
  if (!token) {
    userPreferences.value = '請先登入以開啟個人化偏好'
    return
  }

  try {
    const response = await axios.post('/api/update_profile.php', {
       action: 'get',
       token: token
    })
    userPreferences.value = response.data.system_prompt || ''
  } catch (error) {
    console.error('讀取偏好失敗:', error)
  }
}

const savePreferences = async () => {
  const token = localStorage.getItem('jwt_token')
  if (!token) return

  isSavingPrefs.value = true
  try {
    const response = await axios.post('/api/update_profile.php', {
      action: 'save',
      token: token,
      system_prompt: userPreferences.value
    })
    
    if (response.data.success) {
      messages.push({
        id: Date.now(),
        role: 'ai',
        content: '偏好設定已成功更新！下次對話時我會記住您的設定。',
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      })
      showSettings.value = false
      scrollToBottom()
    } else {
      throw new Error('更新失敗')
    }
  } catch (error) {
    alert('更新失敗')
  } finally {
    isSavingPrefs.value = false
  }
}

const sendMessage = async () => {
  if (!chatInput.value.trim() || isTyping.value) return

  const userContent = chatInput.value
  const userMsg = {
    id: Date.now(),
    role: 'user',
    content: userContent,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  }

  messages.push(userMsg)
  chatInput.value = ''
  scrollToBottom()

  isTyping.value = true

  try {
    const response = await fetch('/api/chat_agent.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        token: localStorage.getItem('jwt_token'),
        messages: messages.map(m => ({
          role: m.role === 'ai' ? 'assistant' : m.role,
          content: m.content
        }))
      })
    })

    if (!response.ok) throw new Error('網路請求失敗')

    const data = await response.json()
    
    if (data.error) throw new Error(data.error)

    const aiMsg = {
      id: Date.now() + 1,
      role: 'ai',
      content: data.content || '抱歉，我暫時無法回答這個問題。',
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }
    messages.push(aiMsg)
  } catch (error) {
    console.error('AI Error:', error)
    messages.push({
      id: Date.now() + 1,
      role: 'ai',
      content: '發生錯誤：' + error.message,
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    })
  } finally {
    isTyping.value = false
    scrollToBottom()
  }
}

// 處理 Markdown 連結點擊
const handleLinkClick = (event) => {
  const link = event.target.closest('a')
  if (link && link.getAttribute('href')?.startsWith('/tool/')) {
    event.preventDefault()
    const path = link.getAttribute('href')
    router.push(path)
    isOpen.value = false // 點擊後關閉對話框方便查看詳情
  }
}

// Markdown 選項配置
marked.setOptions({
  breaks: true,
  gfm: true
})

// H13 — 用 DOMPurify 把 marked 產出的 HTML 過濾過，避免 v-html XSS
const renderSafeMarkdown = (text) => {
  const rawHtml = marked.parse(text ?? '')
  return DOMPurify.sanitize(rawHtml, {
    ADD_ATTR: ['target', 'rel']
  })
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
      <div v-if="isOpen" class="mb-4 w-[360px] sm:w-[420px] h-[600px] bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 flex flex-col overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 bg-gradient-to-r from-primary to-secondary text-white shrink-0 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-md">
              <Bot class="w-6 h-6" />
            </div>
            <div>
              <h3 class="font-bold">AI 智慧顧問</h3>
              <div class="flex items-center gap-1.5 text-xs text-white/80">
                <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                專屬您的 AI 專家
              </div>
            </div>
          </div>
          <div class="flex items-center gap-1">
            <button @click="showSettings = !showSettings" class="p-2 hover:bg-white/10 rounded-full transition-colors border-none bg-transparent text-white cursor-pointer" title="AI 偏好設定">
              <Settings class="w-5 h-5" :class="{ 'rotate-90': showSettings }" />
            </button>
            <button @click="isOpen = false" class="p-2 hover:bg-white/10 rounded-full transition-colors border-none bg-transparent text-white cursor-pointer">
              <X class="w-6 h-6" />
            </button>
          </div>
        </div>

        <!-- Content Area -->
        <div class="flex-grow flex flex-col overflow-hidden relative bg-slate-50">
          
          <!-- Preferences Settings Panel -->
          <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition duration-300 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
          >
            <div v-if="showSettings" class="absolute inset-0 z-20 bg-white flex flex-col p-8">
              <button @click="showSettings = false" class="flex items-center gap-2 text-slate-500 hover:text-primary mb-6 border-none bg-transparent cursor-pointer font-bold transition-colors">
                <ArrowLeft class="w-4 h-4" /> 返回對話
              </button>

              <div class="mb-6">
                <h4 class="text-xl font-bold text-slate-900 mb-2">AI 偏好設定</h4>
                <p class="text-sm text-slate-500">設定您的身份，讓 AI 提供更精準的排版與工具推薦。</p>
              </div>

              <div class="flex-grow">
                <label class="block text-sm font-bold text-slate-700 mb-2">個人化提示詞</label>
                <textarea 
                  v-model="userPreferences"
                  class="w-full h-48 p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-primary/20 text-sm leading-relaxed resize-none transition-all"
                  placeholder="例如：我是一名學生，偏好免費工具。"
                ></textarea>
              </div>

              <button 
                @click="savePreferences"
                :disabled="isSavingPrefs"
                class="w-full py-4 bg-primary text-white rounded-2xl font-bold hover:bg-primary/90 transition-all border-none cursor-pointer shadow-lg shadow-primary/20 flex items-center justify-center gap-2"
              >
                <Check v-if="!isSavingPrefs" class="w-5 h-5" />
                <RefreshCw v-else class="w-5 h-5 animate-spin" />
                儲存偏好
              </button>
            </div>
          </Transition>

          <!-- Chat Messages Area -->
          <div 
            @click="handleLinkClick"
            ref="scrollArea"
            class="flex-grow p-6 overflow-y-auto space-y-6 scroll-smooth"
          >
            <div 
              v-for="msg in messages" 
              :key="msg.id"
              :class="msg.role === 'ai' ? 'justify-start' : 'justify-end'"
              class="flex items-end gap-2"
            >
              <div 
                v-if="msg.role === 'ai'" 
                class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center shrink-0 shadow-sm"
              >
                <Sparkles class="w-4 h-4 text-primary" />
              </div>
              
              <div 
                :class="msg.role === 'ai' ? 'bg-white text-slate-700 rounded-bl-none border border-slate-100' : 'bg-primary text-white rounded-br-none'"
                class="max-w-[85%] p-4 rounded-3xl shadow-sm text-sm"
              >
                <!-- Markdown Rendering Content -->
                <div
                  v-if="msg.role === 'ai'"
                  class="markdown-content prose prose-sm max-w-none"
                  v-html="renderSafeMarkdown(msg.content)"
                ></div>
                <div v-else class="whitespace-pre-wrap">{{ msg.content }}</div>
                
                <div :class="msg.role === 'ai' ? 'text-slate-400' : 'text-white/60'" class="text-[10px] mt-2 text-right italic">
                  {{ msg.time }}
                </div>
              </div>

              <div 
                v-if="msg.role === 'user'" 
                class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0 border border-primary/10 shadow-sm"
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
                class="absolute right-2 top-2 p-2 bg-primary text-white rounded-xl hover:bg-primary/90 disabled:bg-slate-300 transition-all border-none cursor-pointer"
              >
                <Send class="w-5 h-5" />
              </button>
            </form>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Trigger Button -->
    <button 
      @click="toggleChat"
      class="group relative w-16 h-16 bg-primary hover:bg-primary/90 text-white rounded-full shadow-2xl flex items-center justify-center transition-all duration-300 hover:scale-110 cursor-pointer border-none"
    >
      <Bot v-if="!isOpen" class="w-8 h-8" />
      <MinusSquare v-else class="w-8 h-8" />
    </button>
  </div>
</template>

<style scoped>
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}

/* Markdown 排版樣式優化 */
.markdown-content :deep(h2) {
  font-size: 1.1rem;
  font-weight: 700;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
  color: #1e293b;
}

.markdown-content :deep(p) {
  margin-bottom: 0.75rem;
  line-height: 1.6;
}

.markdown-content :deep(ul), .markdown-content :deep(ol) {
  margin-bottom: 0.75rem;
  padding-left: 1.25rem;
}

.markdown-content :deep(li) {
  margin-bottom: 0.25rem;
}

.markdown-content :deep(a) {
  color: #3b82f6;
  font-weight: 700;
  text-decoration: none;
  background: #eff6ff;
  padding: 2px 6px;
  border-radius: 6px;
  border: 1px solid #dbeafe;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 2px;
}

.markdown-content :deep(a:hover) {
  background: #3b82f6;
  color: white;
}

.markdown-content :deep(a::after) {
  content: ' ↗';
  font-size: 0.75rem;
}

.markdown-content :deep(strong) {
  color: #1e293b;
}
</style>
