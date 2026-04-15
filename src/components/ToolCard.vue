<script setup>
import { Star, Heart } from 'lucide-vue-next';

const props = defineProps({
  name: String,
  description: String,
  logoUrl: String,
  rating: [Number, String],
  tags: {
    type: Array,
    default: () => []
  },
  isFavorited: {
    type: Boolean,
    default: false
  },
  showFavoriteButton: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['click', 'toggleFavorite']);
</script>

<template>
  <div 
    class="glass-card group p-5 flex flex-col h-full hover:shadow-2xl hover:shadow-primary/5 transition-all cursor-pointer"
    @click="emit('click')"
  >
    <div class="flex justify-between items-start mb-4">
      <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden">
        <img :src="logoUrl" :alt="name" class="w-10 h-10 object-contain">
      </div>
      <div class="flex flex-col items-end gap-2">
        <div class="flex gap-1 text-orange-400 items-center bg-orange-50 px-2 py-1 rounded-lg text-sm font-bold">
          <Star class="w-4 h-4 fill-current" /> {{ rating }}
        </div>
        <button 
          v-if="showFavoriteButton"
          @click.stop="emit('toggleFavorite')"
          class="p-2 rounded-full transition-colors"
          :class="isFavorited ? 'text-red-500 bg-red-50' : 'text-slate-400 bg-slate-100 hover:text-red-500'"
        >
          <Heart class="w-5 h-5" :class="{ 'fill-current': isFavorited }" />
        </button>
      </div>
    </div>
    
    <h3 class="text-xl font-bold text-slate-900 group-hover:text-primary transition-colors mb-2">{{ name }}</h3>
    <p class="text-slate-500 text-sm mb-6 flex-grow line-clamp-2">{{ description }}</p>
    
    <div class="flex flex-wrap gap-2 mb-6">
      <span 
        v-for="(tag, index) in tags" 
        :key="index"
        class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md text-xs font-semibold"
      >
        {{ tag }}
      </span>
    </div>
    
    <button class="w-full mt-auto py-3 text-center bg-slate-100/50 hover:bg-primary hover:text-white text-slate-700 font-bold rounded-xl transition-all block">
      查看詳情
    </button>
  </div>
</template>
