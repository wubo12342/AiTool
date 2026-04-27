<script setup>
import { onMounted } from 'vue'

let canvas, ctx
let particles = []
let mouse = { x: null, y: null }

const COLORS = [
  'rgba(59,130,246,',   // 藍
  'rgba(168,85,247,',   // 紫
  'rgba(236,72,153,'    // 粉
]

const COUNT = 60 // 👉 減少一點，更乾淨

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
  ctx.clearRect(0, 0, canvas.width, canvas.height)

  particles.forEach(p => {
    p.x += p.vx
    p.y += p.vy

    // 👉 輕微滑鼠排斥（很弱）
    if (mouse.x) {
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

    // ✨ 超淡 glow 粒子（重點）
    const gradient = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, 6)
    gradient.addColorStop(0, p.color + '0.25)') // 👈 降低亮度
    gradient.addColorStop(1, p.color + '0)')

    ctx.beginPath()
    ctx.arc(p.x, p.y, 2, 0, Math.PI * 2)
    ctx.fillStyle = gradient
    ctx.fill()
  })

  // 🔗 線條（幾乎隱形）
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

  requestAnimationFrame(draw)
}

onMounted(() => {
  canvas = document.getElementById('bg')
  ctx = canvas.getContext('2d')

  canvas.width = window.innerWidth
  canvas.height = window.innerHeight

  init()
  draw()

  window.addEventListener('mousemove', e => {
    mouse.x = e.clientX
    mouse.y = e.clientY
  })

  window.addEventListener('resize', () => {
    canvas.width = window.innerWidth
    canvas.height = window.innerHeight
    init()
  })
})
</script>

<template>
  <canvas id="bg" class="fixed inset-0 -z-10"></canvas>
</template>