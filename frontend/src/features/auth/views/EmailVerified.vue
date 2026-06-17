<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuth } from '@/features/auth/composables/useAuth'

const { isAuthenticated, refreshUser } = useAuth()

onMounted(async () => {
  if (isAuthenticated.value) {
    await refreshUser()
  }
})
</script>

<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-6">
        <div class="card">
          <div class="card-body text-center p-4 p-md-5">
            <i class="bi bi-check-circle display-4 text-success mb-3 d-block"></i>
            <h1 class="card-title h4 mb-3">Email verified</h1>
            <p class="text-muted mb-4">
              Your email address has been verified. You can now reserve tickets.
            </p>

            <router-link
              :to="isAuthenticated ? '/events' : '/login'"
              class="btn btn-primary w-100"
            >
              {{ isAuthenticated ? 'Browse events' : 'Sign in' }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
