<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/features/auth/composables/useAuth'
import type { ValidationErrors } from '@/features/auth/types/user'

const route = useRoute()
const router = useRouter()
const { resetPassword } = useAuth()

const token = (route.query.token as string) ?? ''
const email = ref((route.query.email as string) ?? '')
const password = ref('')
const passwordConfirmation = ref('')
const errors = ref<ValidationErrors>({})
const isSubmitting = ref(false)

async function handleSubmit(): Promise<void> {
  isSubmitting.value = true
  errors.value = {}

  try {
    const result = await resetPassword({
      token,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    if (result.ok) {
      await router.push('/')
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
            <h1 class="card-title h4 mb-4">Choose a new password</h1>

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

              <div class="mb-3">
                <label for="password" class="form-label">New password</label>
                <input
                  id="password"
                  v-model="password"
                  type="password"
                  class="form-control"
                  :class="{ 'is-invalid': errors.password }"
                  autocomplete="new-password"
                />
                <div v-if="errors.password" class="invalid-feedback">
                  {{ errors.password[0] }}
                </div>
              </div>

              <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirm new password</label>
                <input
                  id="password_confirmation"
                  v-model="passwordConfirmation"
                  type="password"
                  class="form-control"
                  :class="{ 'is-invalid': errors.password_confirmation }"
                  autocomplete="new-password"
                />
                <div v-if="errors.password_confirmation" class="invalid-feedback">
                  {{ errors.password_confirmation[0] }}
                </div>
              </div>

              <button type="submit" class="btn btn-primary w-100" :disabled="isSubmitting">
                <span
                  v-if="isSubmitting"
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                  aria-hidden="true"
                ></span>
                {{ isSubmitting ? 'Resetting…' : 'Reset password' }}
              </button>
            </form>

            <p class="text-center text-muted mt-3 mb-0">
              Back to
              <router-link to="/login">Sign in</router-link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
