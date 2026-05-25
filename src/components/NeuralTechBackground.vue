<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue'

let canvas, ctx
let particles = []
let mouse = { x: null, y: null }
let rafId = null

const canvasRef = ref(null)

const COLORS = [
  'rgba(59,130,246,',   // 藍
  'rgba(168,85,247,',   // 紫
  'rgba(236,72,153,'    // 粉
]

const COUNT = 60

const init = () => {
  particles = []
  for (let i = 0; i < COUNT; i++) {
    particles.push({
      x: Math.random() * window.innerWidth,
      y: Math.random() * window.innerHeight,
      vx: (Math.random() - 0.5) * 0.25,
      vy: (Math.random() - 0.5) * 0.25,
      color: COLORS[Math.floor(Math.random() * COLORS.length)]
    })
  }
}

const draw = () => {
  if (!ctx || !canvas) return

  ctx.clearRect(0, 0, canvas.width, canvas.height)

  particles.forEach(p => {
    p.x += p.vx
    p.y += p.vy

    if (mouse.x !== null) {
      const dx = p.x - mouse.x
      const dy = p.y - mouse.y
      const dist = Math.sqrt(dx * dx + dy * dy)

      if (dist < 100) {
        const force = (100 - dist) / 100
        p.x += dx * force * 0.015
        p.y += dy * force * 0.015
      }
    }

    if (p.x < 0 || p.x > canvas.width) p.vx *= -1
    if (p.y < 0 || p.y > canvas.height) p.vy *= -1

    const gradient = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, 6)
    gradient.addColorStop(0, p.color + '0.25)')
    gradient.addColorStop(1, p.color + '0)')

    ctx.beginPath()
    ctx.arc(p.x, p.y, 2, 0, Math.PI * 2)
    ctx.fillStyle = gradient
    ctx.fill()
  })

  for (let i = 0; i < particles.length; i++) {
    for (let j = i + 1; j < particles.length; j++) {
      const dx = particles[i].x - particles[j].x
      const dy = particles[i].y - particles[j].y
      const dist = Math.sqrt(dx * dx + dy * dy)

      if (dist < 110) {
        ctx.beginPath()
        ctx.moveTo(particles[i].x, particles[i].y)
        ctx.lineTo(particles[j].x, particles[j].y)
        ctx.strokeStyle = `rgba(148,163,184,${0.20 * (1 - dist / 110)})`
        ctx.lineWidth = 0.8
        ctx.stroke()
      }
    }
  }

  rafId = requestAnimationFrame(draw)
}

// 命名函數，這樣才能在 unmount 時 removeEventListener
const handleMouseMove = (e) => {
  mouse.x = e.clientX
  mouse.y = e.clientY
}

const handleResize = () => {
  if (!canvas) return
  canvas.width = window.innerWidth
  canvas.height = window.innerHeight
  init()
}

onMounted(() => {
  canvas = canvasRef.value
  if (!canvas) return
  ctx = canvas.getContext('2d')

  canvas.width = window.innerWidth
  canvas.height = window.innerHeight

  init()
  draw()

  window.addEventListener('mousemove', handleMouseMove)
  window.addEventListener('resize', handleResize)
})

// H10 — unmount 時把所有東西清乾淨
onBeforeUnmount(() => {
  if (rafId !== null) {
    cancelAnimationFrame(rafId)
    rafId = null
  }
  window.removeEventListener('mousemove', handleMouseMove)
  window.removeEventListener('resize', handleResize)
  particles = []
  ctx = null
  canvas = null
})
</script>

<template>
  <canvas ref="canvasRef" class="fixed inset-0 -z-10"></canvas>
</template>
