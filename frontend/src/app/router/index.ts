import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '@/features/auth/composables/useAuth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'landing',
      component: () => import('@/features/landing/views/Landing.vue'),
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
      component: () => import('@/features/events/views/EventList.vue'),
    },
    {
      path: '/events/:id',
      name: 'event-detail',
      component: () => import('@/features/events/views/EventDetail.vue'),
    },
    {
      path: '/orders/:id/checkout',
      name: 'order-checkout',
      component: () => import('@/features/orders/views/Checkout.vue'),
      meta: { requiresUser: true },
    },
    {
      path: '/my-orders',
      name: 'my-orders',
      component: () => import('@/features/orders/views/MyOrders.vue'),
      meta: { requiresUser: true },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/features/auth/views/LoginForm.vue'),
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/features/auth/views/RegisterForm.vue'),
    },
    {
      path: '/email/verify',
      name: 'email-verify-notice',
      component: () => import('@/features/auth/views/EmailVerifyNotice.vue'),
      meta: { requiresUser: true },
    },
    {
      path: '/email/verified',
      name: 'email-verified',
      component: () => import('@/features/auth/views/EmailVerified.vue'),
    },
    {
      path: '/organizer/login',
      name: 'organizer-login',
      component: () => import('@/features/organizer/views/OrganizerLogin.vue'),
    },
    {
      path: '/organizer/register',
      name: 'organizer-register',
      component: () => import('@/features/organizer/views/OrganizerRegister.vue'),
    },
    {
      path: '/organizer/email/verify',
      name: 'organizer-email-verify-notice',
      component: () => import('@/features/organizer/views/OrganizerEmailVerifyNotice.vue'),
      meta: { requiresOrganizer: true },
    },
    {
      path: '/organizer/dashboard',
      name: 'organizer-dashboard',
      component: () => import('@/features/organizer/views/Dashboard.vue'),
      meta: { requiresOrganizer: true, requiresOrganizerVerified: true },
    },
    {
      path: '/organizer/events/create',
      name: 'organizer-event-create',
      component: () => import('@/features/organizer/views/EventCreate.vue'),
      meta: { requiresOrganizer: true, requiresOrganizerVerified: true },
    },
  ],
})

router.beforeEach((to) => {
  const { isOrganizer, isUser, isOrganizerEmailVerified } = useAuth()

  if (to.meta.requiresOrganizer && !isOrganizer.value) {
    return { name: 'organizer-login' }
  }

  if (to.meta.requiresOrganizerVerified && !isOrganizerEmailVerified.value) {
    return { name: 'organizer-email-verify-notice' }
  }

  if (to.meta.requiresUser && !isUser.value) {
    return { name: 'login' }
  }
})

export default router
