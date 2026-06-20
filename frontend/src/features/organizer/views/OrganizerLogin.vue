<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/features/auth/composables/useAuth'
import type { ValidationErrors } from '@/features/auth/types/user'

const router = useRouter()
const { organizerLogin } = useAuth()

const email = ref('')
const password = ref('')
const errors = ref<ValidationErrors>({})
const isSubmitting = ref(false)

async function handleSubmit(): Promise<void> {
  isSubmitting.value = true
  errors.value = {}

  try {
    const result = await organizerLogin(email.value, password.value)

    if (result.ok) {
      await router.push('/organizer/events/create')
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
            <h1 class="card-title h4 mb-4">Organizer sign in</h1>

            <form novalidate @submit.prevent="handleSubmit">
              <div class="mb-3">
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

              <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input
                  id="password"
                  v-model="password"
                  type="password"
                  class="form-control"
                  :class="{ 'is-invalid': errors.password }"
                  autocomplete="current-password"
                />
                <div v-if="errors.password" class="invalid-feedback">
                  {{ errors.password[0] }}
                </div>
              </div>

              <button type="submit" class="btn btn-primary w-100" :disabled="isSubmitting">
                <span
                  v-if="isSubmitting"
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                  aria-hidden="true"
                ></span>
                {{ isSubmitting ? 'Signing in…' : 'Sign in' }}
              </button>
            </form>

            <p class="text-center mt-3 mb-0">
              <router-link to="/organizer/password/forgot">Forgot your password?</router-link>
            </p>

            <p class="text-center text-muted mt-2 mb-0">
              Need an organizer account?
              <router-link to="/organizer/register">Register</router-link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
