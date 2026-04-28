import { createRouter, createWebHistory } from 'vue-router'
import Home from '../components/Home.vue'
import ToolDetail from '../components/ToolDetail.vue'
import Profile from '../components/Profile.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      component: Home
    },
    {
      path: '/tool/:id',
      component: ToolDetail
    },
    {
      path: '/profile',
      component: Profile
    }
  ]
})

export default router