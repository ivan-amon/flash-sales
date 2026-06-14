<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiFetch } from '@/shared/api/http'
import type { OrderWithTicket } from '@/features/orders/types/order'

type PaymentMethod = 'credit_card' | 'paypal'

const route = useRoute()
const router = useRouter()

const id = route.params.id as string

const order = ref<OrderWithTicket | null>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)
const payError = ref<string | null>(null)
const isPaying = ref(false)
const paymentMethod = ref<PaymentMethod>('credit_card')
const cardNumber = ref('')

const now = ref(Date.now())
let timer: ReturnType<typeof setInterval> | undefined

const remainingMs = computed(() =>
  order.value ? new Date(order.value.expires_at).getTime() - now.value : 0,
)

const isExpired = computed(() => remainingMs.value <= 0)

const countdown = computed(() => {
  const totalSeconds = Math.max(0, Math.floor(remainingMs.value / 1000))
  const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0')
  const seconds = String(totalSeconds % 60).padStart(2, '0')
  return `${minutes}:${seconds}`
})

const isPending = computed(() => order.value?.status === 'pending')
const canPay = computed(() => isPending.value && !isExpired.value && !isPaying.value)

onMounted(async () => {
  try {
    const response = await apiFetch(`/orders/${id}`)

    if (response.status === 404 || response.status === 403) {
      error.value = 'Order not found.'
      return
    }

    if (!response.ok) {
      error.value = `Failed to load the order (${response.status}).`
      return
    }

    order.value = await response.json()

    if (order.value?.status === 'pending') {
      timer = setInterval(() => {
        now.value = Date.now()
      }, 1000)
    }
  } catch {
    error.value = 'Unable to reach the server. Please try again later.'
  } finally {
    isLoading.value = false
  }
})

onUnmounted(() => {
  clearInterval(timer)
})

async function pay(): Promise<void> {
  isPaying.value = true
  payError.value = null

  try {
    const response = await apiFetch(`/orders/${id}/pay`, {
      method: 'POST',
      body: JSON.stringify({ payment_method: paymentMethod.value }),
    })

    if (response.ok) {
      await router.push({ name: 'my-orders' })
      return
    }

    if (response.status === 409 || response.status === 410) {
      const data = (await response.json()) as { error: string }
      payError.value = data.error

      if (response.status === 410 && order.value) {
        order.value.status = 'expired'
      }
    } else {
      payError.value = 'Payment could not be processed. Please try again.'
    }
  } catch {
    payError.value = 'Unable to reach the server. Please try again later.'
  } finally {
    isPaying.value = false
  }
}
</script>

<template>
  <div class="container py-4">
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading…</span>
      </div>
    </div>

    <div v-else-if="error" class="alert alert-danger" role="alert">
      {{ error }}
    </div>

    <div v-else-if="order" class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card">
          <div class="card-body">
            <h1 class="card-title h4 mb-1">Checkout</h1>
            <p class="text-muted">{{ order.ticket.event.title }}</p>

            <template v-if="isPending && !isExpired">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="text-muted">Time remaining</span>
                <span
                  class="fs-4 fw-bold font-monospace"
                  :class="remainingMs <= 60000 ? 'text-danger' : ''"
                >
                  {{ countdown }}
                </span>
              </div>

              <div v-if="payError" class="alert alert-warning" role="alert">
                {{ payError }}
              </div>

              <form novalidate @submit.prevent="pay">
                <div class="mb-3">
                  <label class="form-label d-block">Payment method</label>
                  <div class="form-check form-check-inline">
                    <input
                      id="method-card"
                      v-model="paymentMethod"
                      class="form-check-input"
                      type="radio"
                      value="credit_card"
                    />
                    <label class="form-check-label" for="method-card">Credit Card</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input
                      id="method-paypal"
                      v-model="paymentMethod"
                      class="form-check-input"
                      type="radio"
                      value="paypal"
                    />
                    <label class="form-check-label" for="method-paypal">PayPal</label>
                  </div>
                </div>

                <div v-if="paymentMethod === 'credit_card'" class="mb-4">
                  <label for="card_number" class="form-label">Card number</label>
                  <input
                    id="card_number"
                    v-model="cardNumber"
                    type="text"
                    class="form-control"
                    placeholder="4242 4242 4242 4242"
                    autocomplete="off"
                  />
                </div>

                <button type="submit" class="btn btn-primary w-100" :disabled="!canPay">
                  <span
                    v-if="isPaying"
                    class="spinner-border spinner-border-sm me-2"
                    role="status"
                    aria-hidden="true"
                  ></span>
                  {{ isPaying ? 'Processing…' : 'Pay' }}
                </button>
              </form>
            </template>

            <div v-else-if="isExpired" class="alert alert-danger mb-0" role="alert">
              Order Expired — this reservation is no longer available.
            </div>

            <div v-else class="alert alert-info mb-0" role="alert">
              This order is already {{ order.status }}.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
