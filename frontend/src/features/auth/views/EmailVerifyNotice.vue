<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '@/features/auth/composables/useAuth'

const { user, resendVerificationEmail } = useAuth()

const isSending = ref(false)
const feedback = ref<{ type: 'success' | 'danger'; message: string } | null>(null)

async function handleResend(): Promise<void> {
  isSending.value = true
  feedback.value = null

  try {
    const ok = await resendVerificationEmail()

    feedback.value = ok
      ? { type: 'success', message: 'Verification link sent. Check your inbox.' }
      : { type: 'danger', message: 'Could not send the email. Please try again shortly.' }
  } finally {
    isSending.value = false
  }
}
</script>

<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-6">
        <div class="card">
          <div class="card-body text-center p-4 p-md-5">
            <i class="bi bi-envelope-check display-4 text-info mb-3 d-block"></i>
            <h1 class="card-title h4 mb-3">Verify your email</h1>
            <p class="text-muted mb-4">
              We've sent a verification link to
              <span class="fw-semibold">{{ user?.email }}</span>. Click it to activate your
              account before reserving tickets.
            </p>

            <div v-if="feedback" :class="`alert alert-${feedback.type}`" role="alert">
              {{ feedback.message }}
            </div>

            <button
              type="button"
              class="btn btn-primary w-100"
              :disabled="isSending"
              @click="handleResend"
            >
              <span
                v-if="isSending"
                class="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
              ></span>
              {{ isSending ? 'Sending…' : 'Resend verification email' }}
            </button>

            <p class="text-muted mt-3 mb-0">
              Already verified?
              <router-link to="/events">Browse events</router-link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
