import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'events',
      component: () => import('../views/EventList.vue'),
    },
  ],
})

export default router
