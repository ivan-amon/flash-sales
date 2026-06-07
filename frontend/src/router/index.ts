import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'landing',
      component: () => import('../views/Landing.vue'),
      beforeEnter: () => {
        const { isAuthenticated } = useAuth()

        if (isAuthenticated.value) {
          return { name: 'events' }
        }
      },
    },
    {
      path: '/events',
      name: 'events',
      component: () => import('../views/EventList.vue'),
    },
    {
      path: '/events/:id',
      name: 'event-detail',
      component: () => import('../views/EventDetail.vue'),
    },
    {
      path: '/orders/:id/checkout',
      name: 'order-checkout',
      component: () => import('../views/Checkout.vue'),
      meta: { requiresUser: true },
    },
    {
      path: '/my-orders',
      name: 'my-orders',
      component: () => import('../views/MyOrders.vue'),
      meta: { requiresUser: true },
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
    {
      path: '/organizer/login',
      name: 'organizer-login',
      component: () => import('../views/organizer/OrganizerLogin.vue'),
    },
    {
      path: '/organizer/register',
      name: 'organizer-register',
      component: () => import('../views/organizer/OrganizerRegister.vue'),
    },
    {
      path: '/organizer/events/create',
      name: 'organizer-event-create',
      component: () => import('../views/organizer/EventCreate.vue'),
      meta: { requiresOrganizer: true },
    },
  ],
})

router.beforeEach((to) => {
  const { isOrganizer, isUser } = useAuth()

  if (to.meta.requiresOrganizer && !isOrganizer.value) {
    return { name: 'organizer-login' }
  }

  if (to.meta.requiresUser && !isUser.value) {
    return { name: 'login' }
  }
})

export default router
