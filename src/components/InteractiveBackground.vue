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

const handleMouseMove = (event) => {
  if (props.paused) return

  const { innerWidth, innerHeight } = window
  x.value = (event.clientX / innerWidth) * 100
  y.value = (event.clientY / innerHeight) * 100
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
    class="pointer-events-none absolute inset-0 overflow-hidden transition-opacity duration-700"
    :class="{ 'opacity-50': paused }"
  >
    <!-- 底色漸層 -->
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,#dbeafe_0%,#f8fafc_45%,#eef2ff_100%)]"></div>

    <!-- 主藍色光球：跟隨滑鼠 -->
    <div
      class="absolute h-[34rem] w-[34rem] rounded-full blur-3xl opacity-40 transition-all duration-300 ease-out"
      :style="{
        left: `calc(${x}% - 17rem)`,
        top: `calc(${y}% - 17rem)`,
        background: 'radial-gradient(circle, rgba(59,130,246,0.35) 0%, rgba(59,130,246,0.12) 35%, rgba(59,130,246,0) 70%)'
      }"
    />

    <!-- 紫色光球：反向緩慢移動 -->
    <div
      class="absolute h-[28rem] w-[28rem] rounded-full blur-3xl opacity-30 transition-all duration-500 ease-out"
      :style="{
        left: `calc(${100 - x}% - 14rem)`,
        top: `calc(${100 - y}% - 14rem)`,
        background: 'radial-gradient(circle, rgba(139,92,246,0.28) 0%, rgba(139,92,246,0.10) 35%, rgba(139,92,246,0) 70%)'
      }"
    />

    <!-- 固定裝飾光球 -->
    <div class="absolute top-[10%] left-[8%] h-40 w-40 rounded-full bg-cyan-300/20 blur-3xl"></div>
    <div class="absolute bottom-[10%] right-[8%] h-52 w-52 rounded-full bg-indigo-300/20 blur-3xl"></div>

    <!-- 細網格 -->
    <div
      class="absolute inset-0 opacity-[0.08]"
      style="
        background-image:
          linear-gradient(rgba(15,23,42,0.4) 1px, transparent 1px),
          linear-gradient(90deg, rgba(15,23,42,0.4) 1px, transparent 1px);
        background-size: 42px 42px;
      "
    ></div>
  </div>
</template>