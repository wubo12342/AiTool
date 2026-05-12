<script setup>
import { Check, Search, ArrowLeftRight, Star, ExternalLink, Trash2 } from 'lucide-vue-next'
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

// --- Dynamic Tool Comparison Section Data ---
const searchResults = ref([])
const defaultTools = ref([])
const activeSearchIndex = ref(null) // 0 or 1
const selectedTools = ref([null, null])
const isSearching = ref(false)
const searchQuery = ref('')

const fetchDefaultTools = async () => {
  try {
    // 取得一些預設推薦工具 (例如依評分排序的前 6 個)
    const response = await axios.get('/api/get_tools.php?sort=rating')
    defaultTools.value = response.data.tools || []
  } catch (error) {
    console.error('Fetch default tools failed:', error)
  }
}

const searchTools = async (query) => {
  if (!query) {
    searchResults.value = []
    return
  }
  try {
    isSearching.value = true
    const response = await axios.get(`/api/get_tools.php?keyword=${encodeURIComponent(query)}`)
    searchResults.value = response.data.tools || []
  } catch (error) {
    console.error('Search failed:', error)
  } finally {
    isSearching.value = false
  }
}

const selectTool = async (tool, index) => {
  try {
    const response = await axios.get(`/api/tool.php?id=${tool.id}`)
    selectedTools.value[index] = response.data
    activeSearchIndex.value = null
    searchQuery.value = ''
    searchResults.value = []
  } catch (error) {
    console.error('Fetch tool detail failed:', error)
  }
}

const removeTool = (index) => {
  selectedTools.value[index] = null
}

const openSearch = (index) => {
  activeSearchIndex.value = index
  searchQuery.value = ''
  searchResults.value = []
}

onMounted(() => {
  fetchDefaultTools()
})

// 當搜尋字串改變時執行搜尋
watch(searchQuery, (newVal) => {
  searchTools(newVal)
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 pb-20 pt-10">
    <!-- Dynamic Tool Comparison Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="bg-white rounded-[3rem] shadow-2xl p-8 md:p-12 border border-slate-100">
        <div class="text-center mb-12">
          <span class="inline-block px-4 py-1 rounded-full bg-teal-50 text-teal-600 font-bold text-sm mb-4">動態工具對比</span>
          <h2 class="text-3xl font-bold">選取兩款 AI 工具進行比較</h2>
          <p class="text-slate-500 mt-2">從我們的工具庫中挑選您感興趣的 AI 進行深度對比</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 relative">
          <!-- Comparison Separator Icon (Desktop) -->
          <div class="hidden md:flex absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-slate-100 border-4 border-white items-center justify-center text-slate-400 z-10 shadow-sm">
            <ArrowLeftRight class="w-6 h-6" />
          </div>

          <div v-for="(tool, index) in selectedTools" :key="index" class="flex flex-col">
            <!-- Tool Selector Placeholder -->
            <div 
              v-if="!tool" 
              class="group h-[400px] rounded-3xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center gap-6 hover:border-teal-400 hover:bg-teal-50/30 transition-all cursor-pointer relative overflow-hidden"
              @click="openSearch(index)"
            >
              <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:text-teal-400 group-hover:scale-110 transition-all">
                <Search class="w-10 h-10" />
              </div>
              <div class="text-center">
                <h4 class="font-bold text-slate-400 group-hover:text-teal-600">選取第 {{ index + 1 }} 款工具</h4>
                <p class="text-slate-300 text-sm mt-1">搜尋名稱或分類...</p>
              </div>

              <!-- Search Overlay -->
              <div v-if="activeSearchIndex === index" class="absolute inset-0 bg-white z-20 p-6 flex flex-col" @click.stop>
                <div class="flex items-center gap-4 mb-6">
                  <div class="flex-grow relative">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                    <input 
                      v-model="searchQuery"
                      type="text" 
                      placeholder="輸入工具名稱關鍵字..."
                      class="w-full pl-12 pr-4 py-3 bg-slate-50 border-none rounded-xl outline-none focus:ring-2 focus:ring-teal-500/20"
                      autoFocus
                    >
                  </div>
                  <button @click="activeSearchIndex = null" class="text-slate-400 hover:text-slate-600 bg-transparent border-none cursor-pointer">
                    取消
                  </button>
                </div>

                <div class="flex-grow overflow-y-auto space-y-2 custom-scrollbar">
                  <div v-if="isSearching" class="text-center py-10">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-500 mx-auto"></div>
                  </div>
                  
                  <div v-else-if="!searchQuery">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">熱門推薦工具</div>
                    <div class="grid grid-cols-1 gap-2">
                      <div 
                        v-for="result in defaultTools" 
                        :key="result.id"
                        class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 cursor-pointer transition-colors border border-transparent hover:border-slate-100"
                        @click="selectTool(result, index)"
                      >
                        <img :src="result.logoUrl" class="w-10 h-10 rounded-lg object-cover" alt="logo">
                        <div class="text-left">
                          <div class="font-bold text-slate-900">{{ result.name }}</div>
                          <div class="text-xs text-slate-500">{{ result.category_name }}</div>
                        </div>
                        <div class="ml-auto flex items-center gap-1 text-amber-500 text-sm">
                          <Star class="w-3 h-3 fill-current" />
                          {{ result.rating }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <div v-else-if="searchResults.length === 0" class="text-center py-10 text-slate-400">
                    找不到符合的工具
                  </div>

                  <div v-else>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">搜尋結果</div>
                    <div 
                      v-for="result in searchResults" 
                      :key="result.id"
                      class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 cursor-pointer transition-colors"
                      @click="selectTool(result, index)"
                    >
                      <img :src="result.logoUrl" class="w-10 h-10 rounded-lg object-cover" alt="logo">
                      <div class="text-left">
                        <div class="font-bold text-slate-900">{{ result.name }}</div>
                        <div class="text-xs text-slate-500">{{ result.category_name }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tool Card (Selected) -->
            <div v-else class="h-[400px] rounded-3xl bg-slate-50 p-8 flex flex-col animate-in zoom-in-95 duration-300 relative group">
              <button 
                @click="removeTool(index)"
                class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all shadow-sm flex items-center justify-center opacity-0 group-hover:opacity-100 border-none cursor-pointer"
              >
                <Trash2 class="w-5 h-5" />
              </button>

              <div class="flex flex-col items-center text-center mb-8">
                <div class="w-24 h-24 rounded-3xl bg-white p-4 shadow-xl mb-6 relative">
                  <img :src="tool.logoUrl" class="w-full h-full object-contain" alt="logo">
                  <div class="absolute -bottom-2 -right-2 bg-teal-500 text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg border-2 border-white">
                    <Check class="w-4 h-4" />
                  </div>
                </div>
                <h3 class="text-2xl font-bold text-slate-900">{{ tool.name }}</h3>
                <div class="inline-flex items-center gap-1 mt-2 px-3 py-1 bg-white rounded-full text-slate-500 text-sm font-medium shadow-sm">
                  <Star class="w-4 h-4 text-amber-400 fill-amber-400" />
                  <span>{{ tool.rating }}</span>
                  <span class="mx-1 text-slate-200">|</span>
                  <span>{{ tool.category_name }}</span>
                </div>
              </div>

              <div class="flex-grow">
                <p class="text-slate-500 text-sm line-clamp-3 text-center px-4">
                  {{ tool.description }}
                </p>
              </div>

              <div class="mt-8 flex gap-3">
                <a 
                  :href="tool.officialUrl" 
                  target="_blank"
                  class="flex-grow py-3 bg-white text-slate-700 font-bold rounded-xl border border-slate-200 hover:bg-slate-50 transition-all flex items-center justify-center gap-2 no-underline text-sm"
                >
                  <ExternalLink class="w-4 h-4" />
                  官方網站
                </a>
                <button 
                  @click="openSearch(index)"
                  class="px-6 py-3 bg-teal-600 text-white font-bold rounded-xl hover:bg-teal-700 transition-all border-none cursor-pointer text-sm"
                >
                  更換
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Detail Comparison Table (Dynamic) -->
        <div v-if="selectedTools[0] || selectedTools[1]" class="mt-20 border-t border-slate-100 pt-16">
          <div class="overflow-hidden bg-slate-50/50 rounded-3xl border border-slate-100">
            <table class="w-full text-left border-collapse">
              <tbody class="divide-y divide-slate-100">
                <tr class="hover:bg-white transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600 w-1/4">應用分類</td>
                  <td class="p-6 text-center">
                    <span v-if="selectedTools[0]" class="inline-block px-4 py-1.5 rounded-full bg-teal-50 text-teal-700 font-bold text-sm border border-teal-100">
                      {{ selectedTools[0].category_name }}
                    </span>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6 text-center">
                    <span v-if="selectedTools[1]" class="inline-block px-4 py-1.5 rounded-full bg-purple-50 text-purple-700 font-bold text-sm border border-purple-100">
                      {{ selectedTools[1].category_name }}
                    </span>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>
                <tr class="hover:bg-white transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600">社群評分</td>
                  <td class="p-6 text-center">
                    <div v-if="selectedTools[0]" class="flex items-center justify-center gap-1 text-amber-600 font-bold">
                      <Star class="w-4 h-4 fill-current" />
                      {{ selectedTools[0].rating }}
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6 text-center">
                    <div v-if="selectedTools[1]" class="flex items-center justify-center gap-1 text-amber-600 font-bold">
                      <Star class="w-4 h-4 fill-current" />
                      {{ selectedTools[1].rating }}
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>
                <tr class="hover:bg-white transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600">價格方案</td>
                  <td class="p-6 text-center">
                    <div v-if="selectedTools[0]?.plans?.length" class="flex flex-wrap justify-center gap-2">
                      <span v-for="plan in selectedTools[0].plans" :key="plan.name" class="px-2 py-1 bg-white text-xs font-bold text-slate-700 rounded-lg border border-slate-200">
                        {{ plan.name }}: {{ plan.price }}
                      </span>
                    </div>
                    <span v-else-if="selectedTools[0]" class="text-slate-500">未提供詳細資料</span>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6 text-center">
                    <div v-if="selectedTools[1]?.plans?.length" class="flex flex-wrap justify-center gap-2">
                      <span v-for="plan in selectedTools[1].plans" :key="plan.name" class="px-2 py-1 bg-white text-xs font-bold text-slate-700 rounded-lg border border-slate-200">
                        {{ plan.name }}: {{ plan.price }}
                      </span>
                    </div>
                    <span v-else-if="selectedTools[1]" class="text-slate-500">未提供詳細資料</span>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>
                <tr class="hover:bg-white transition-colors">
                  <td class="p-6 text-sm font-bold text-slate-600">官方網站</td>
                  <td class="p-6 text-center">
                    <a v-if="selectedTools[0]" :href="selectedTools[0].officialUrl" target="_blank" class="text-teal-700 font-bold hover:underline">前往官方網站</a>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="p-6 text-center">
                    <a v-if="selectedTools[1]" :href="selectedTools[1].officialUrl" target="_blank" class="text-teal-700 font-bold hover:underline">前往官方網站</a>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>
