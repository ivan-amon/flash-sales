<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useAuth } from '@/features/auth/composables/useAuth'

const { isAuthenticated, isOrganizer, refreshUser, refreshOrganizer } = useAuth()

const destination = computed(() => {
  if (!isAuthenticated.value) {
    return '/login'
  }

  return isOrganizer.value ? '/organizer/events/create' : '/events'
})

const destinationLabel = computed(() => {
  if (!isAuthenticated.value) {
    return 'Sign in'
  }

  return isOrganizer.value ? 'Create an event' : 'Browse events'
})

const message = computed(() =>
  isOrganizer.value
    ? 'Your email address has been verified. You can now create events.'
    : 'Your email address has been verified. You can now reserve tickets.',
)

onMounted(async () => {
  if (isOrganizer.value) {
    await refreshOrganizer()
  } else if (isAuthenticated.value) {
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
            <p class="text-muted mb-4">{{ message }}</p>

            <router-link :to="destination" class="btn btn-primary w-100">
              {{ destinationLabel }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
