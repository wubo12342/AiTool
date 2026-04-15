<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  paused: {
    type: Boolean,
    default: false
  }
})

const x = ref(50)
const y = ref(50)
let ticking = false

const handleMouseMove = (event) => {
  if (props.paused) return
  if (!ticking) {
    window.requestAnimationFrame(() => {
      // 轉換為百分比，用於 CSS 變數
      x.value = (event.clientX / window.innerWidth) * 100
      y.value = (event.clientY / window.innerHeight) * 100
      ticking = false
    })
    ticking = true
  }
}

onMounted(() => {
  window.addEventListener('mousemove', handleMouseMove)
})

onBeforeUnmount(() => {
  window.removeEventListener('mousemove', handleMouseMove)
})
</script>

<template>
  <div 
    class="fixed inset-0 pointer-events-none -z-10 overflow-hidden bg-slate-50 transition-opacity duration-700"
    :class="{ 'opacity-50': paused }"
    :style="{ '--mx': x + '%', '--my': y + '%' }"
  >
    <!-- 極致流暢版：使用 CSS 變數與 GPU 合成層處理動畫 -->
    
    <!-- 滑鼠追隨藍光 (Spotlight) -->
    <div
      v-if="!paused"
      class="mouse-follow blue-spotlight"
      style="
        position: absolute;
        width: 30rem;
        height: 30rem;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(59,130,246,0.25) 0%, rgba(59,130,246,0) 70%);
        left: -15rem;
        top: -15rem;
        will-change: transform;
        transform: translate3d(var(--mx), var(--my), 0);
        transition: transform 0.1s linear;
      "
    ></div>

    <!-- 環境光球 1 (緩慢跟隨) -->
    <div
      v-if="!paused"
      class="mouse-follow ambient-1"
      style="
        position: absolute;
        width: 60rem;
        height: 60rem;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(147,197,253,0.15) 0%, rgba(147,197,253,0) 70%);
        left: -30rem;
        top: -30rem;
        will-change: transform;
        transform: translate3d(var(--mx), var(--my), 0);
        transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
        opacity: 0.5;
      "
    ></div>

    <!-- 背景網格 -->
    <div
      class="absolute inset-0 opacity-[0.03]"
      style="
        background-image:
          linear-gradient(rgba(15,23,42,0.5) 1px, transparent 1px),
          linear-gradient(90deg, rgba(15,23,42,0.5) 1px, transparent 1px);
        background-size: 40px 40px;
      "
    ></div>
  </div>
</template>

<style scoped>
/* 確保所有跟隨元素都使用 GPU 加速 */
.mouse-follow {
  pointer-events: none;
  z-index: -1;
}
</style>