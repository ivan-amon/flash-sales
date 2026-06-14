<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import logo from '@/assets/logo.svg'
import { useAuth } from '@/features/auth/composables/useAuth'

const router = useRouter()
const { user, organizer, isAuthenticated, isOrganizer, isUser, logout } = useAuth()

const isMenuOpen = ref(false)

router.afterEach(() => {
  isMenuOpen.value = false
})

async function handleLogout(): Promise<void> {
  await logout()
  await router.push('/login')
}
</script>

<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid px-3 px-md-4 px-lg-5">
      <router-link class="navbar-brand d-flex align-items-center" to="/">
        <img :src="logo" alt="Flash Sales logo" width="32" height="32" class="me-2" />
        Flash Sales
      </router-link>

      <button
        type="button"
        class="navbar-toggler"
        aria-controls="mainNav"
        :aria-expanded="isMenuOpen"
        aria-label="Toggle navigation"
        @click="isMenuOpen = !isMenuOpen"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div id="mainNav" class="collapse navbar-collapse" :class="{ show: isMenuOpen }">
        <div class="navbar-nav mt-3 mt-lg-0">
          <router-link class="nav-link" to="/events">Events</router-link>
          <router-link v-if="isOrganizer" class="nav-link" to="/organizer/events/create">
            Create Event
          </router-link>
          <router-link v-if="isOrganizer" class="nav-link" to="/organizer/dashboard">
            Dashboard
          </router-link>
          <router-link v-if="isUser" class="nav-link" to="/my-orders">My Orders</router-link>
        </div>

        <div
          class="ms-lg-auto d-flex flex-column flex-lg-row align-items-lg-center mt-3 mt-lg-0"
        >
          <template v-if="isAuthenticated">
            <span class="navbar-text me-lg-3 mb-2 mb-lg-0">
              {{ user?.email ?? organizer?.email }}
            </span>
            <button type="button" class="btn btn-outline-light btn-sm" @click="handleLogout">
              Logout
            </button>
          </template>
          <router-link v-else class="btn btn-outline-light btn-sm" to="/login">
            Login
          </router-link>
        </div>
      </div>
    </div>
  </nav>

  <router-view />
</template>
