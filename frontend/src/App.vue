<script setup lang="ts">
import { useRouter } from 'vue-router'
import logo from './assets/logo.svg'
import { useAuth } from './composables/useAuth'

const router = useRouter()
const { user, isAuthenticated, logout } = useAuth()

async function handleLogout(): Promise<void> {
  await logout()
  await router.push('/login')
}
</script>

<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <router-link class="navbar-brand d-flex align-items-center" to="/">
        <img :src="logo" alt="Flash Sales logo" width="32" height="32" class="me-2" />
        Flash Sales
      </router-link>

      <div class="ms-auto d-flex align-items-center">
        <template v-if="isAuthenticated">
          <span class="navbar-text me-3">{{ user?.email }}</span>
          <button type="button" class="btn btn-outline-light btn-sm" @click="handleLogout">
            Logout
          </button>
        </template>
        <router-link v-else class="btn btn-outline-light btn-sm" to="/login">
          Login
        </router-link>
      </div>
    </div>
  </nav>

  <router-view />
</template>
