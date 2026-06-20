<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '@/features/auth/composables/useAuth'
import type { ValidationErrors } from '@/features/auth/types/user'

const { organizerForgotPassword } = useAuth()

const email = ref('')
const errors = ref<ValidationErrors>({})
const isSubmitting = ref(false)
const isSent = ref(false)

async function handleSubmit(): Promise<void> {
  isSubmitting.value = true
  errors.value = {}

  try {
    const result = await organizerForgotPassword(email.value)

    if (result.ok) {
      isSent.value = true
    } else {
      errors.value = result.errors
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card">
          <div class="card-body">
            <h1 class="card-title h4 mb-4">Reset your password</h1>

            <div v-if="isSent" class="alert alert-success" role="alert">
              If an organizer account exists for that email, a password reset link is on its way.
              Check your inbox.
            </div>

            <form v-else novalidate @submit.prevent="handleSubmit">
              <p class="text-muted">
                Enter your email and we'll send you a link to reset your password.
              </p>

              <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <input
                  id="email"
                  v-model="email"
                  type="email"
                  class="form-control"
                  :class="{ 'is-invalid': errors.email }"
                  autocomplete="email"
                />
                <div v-if="errors.email" class="invalid-feedback">
                  {{ errors.email[0] }}
                </div>
              </div>

              <button type="submit" class="btn btn-primary w-100" :disabled="isSubmitting">
                <span
                  v-if="isSubmitting"
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                  aria-hidden="true"
                ></span>
                {{ isSubmitting ? 'Sending…' : 'Send reset link' }}
              </button>
            </form>

            <p class="text-center text-muted mt-3 mb-0">
              Remember your password?
              <router-link to="/organizer/login">Sign in</router-link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
