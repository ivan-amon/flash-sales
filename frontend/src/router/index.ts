import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'events',
      component: () => import('../views/EventList.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginForm.vue'),
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterForm.vue'),
    },
  ],
})

export default router
