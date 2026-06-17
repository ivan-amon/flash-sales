<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { Stripe, StripeCardElement } from '@stripe/stripe-js'
import { apiFetch } from '@/shared/api/http'
import { getStripe } from '@/shared/payments/stripe'
import { formatPriceFromCents } from '@/shared/utils/format'
import type { OrderWithTicket } from '@/features/orders/types/order'

const route = useRoute()
const router = useRouter()

const id = route.params.id as string

const order = ref<OrderWithTicket | null>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)
const payError = ref<string | null>(null)
const isPaying = ref(false)

const stripe = ref<Stripe | null>(null)
const cardEl = ref<HTMLElement | null>(null)
let cardElement: StripeCardElement | null = null

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
  } catch {
    error.value = 'Unable to reach the server. Please try again later.'
    return
  } finally {
    isLoading.value = false
  }

  if (order.value?.status === 'pending' && !isExpired.value) {
    timer = setInterval(() => {
      now.value = Date.now()
    }, 1000)

    await setupCardElement()
  }
})

onUnmounted(() => {
  clearInterval(timer)
  cardElement?.destroy()
})

async function setupCardElement(): Promise<void> {
  stripe.value = await getStripe()

  if (!stripe.value) {
    return
  }

  await nextTick()

  if (!cardEl.value) {
    return
  }

  const elements = stripe.value.elements()
  cardElement = elements.create('card', {
    style: {
      base: {
        color: '#212529',
        fontSize: '16px',
        '::placeholder': { color: '#adb5bd' },
      },
      invalid: { color: '#e74c3c', iconColor: '#e74c3c' },
    },
  })
  cardElement.mount(cardEl.value)
}

async function pay(): Promise<void> {
  isPaying.value = true
  payError.value = null

  try {
    const clientSecret = await createPaymentIntent()
    if (!clientSecret) {
      return
    }

    if (stripe.value && cardElement) {
      const { error: stripeError, paymentIntent } = await stripe.value.confirmCardPayment(
        clientSecret,
        { payment_method: { card: cardElement } },
      )

      if (stripeError) {
        payError.value = stripeError.message ?? 'Your card was declined.'
        return
      }

      if (paymentIntent?.status !== 'succeeded') {
        payError.value = 'The payment could not be completed.'
        return
      }
    }

    await confirmPayment()
  } catch {
    payError.value = 'Unable to reach the server. Please try again later.'
  } finally {
    isPaying.value = false
  }
}

async function createPaymentIntent(): Promise<string | null> {
  const response = await apiFetch(`/orders/${id}/payment-intent`, { method: 'POST' })

  if (response.ok) {
    const data = (await response.json()) as { client_secret: string }
    return data.client_secret
  }

  await handlePaymentError(response)
  return null
}

async function confirmPayment(): Promise<void> {
  const response = await apiFetch(`/orders/${id}/pay`, { method: 'POST' })

  if (response.ok) {
    await router.push({ name: 'my-orders' })
    return
  }

  await handlePaymentError(response)
}

async function handlePaymentError(response: Response): Promise<void> {
  if (response.status === 409 || response.status === 410) {
    const data = (await response.json()) as { error: string }
    payError.value = data.error

    if (response.status === 410 && order.value) {
      order.value.status = 'expired'
    }
  } else {
    payError.value = 'Payment could not be processed. Please try again.'
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

            <div class="d-flex justify-content-between align-items-center border-top border-bottom py-2 my-3">
              <span class="text-muted">Total</span>
              <span class="fs-5 fw-bold">{{ formatPriceFromCents(order.amount) }}</span>
            </div>

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
                <div v-if="stripe" class="mb-4">
                  <label class="form-label">Card details</label>
                  <div ref="cardEl" class="form-control card-element"></div>
                </div>

                <div v-else class="alert alert-info" role="alert">
                  <i class="bi bi-info-circle me-2"></i>
                  Test mode — payment is simulated, no card required.
                </div>

                <button type="submit" class="btn btn-primary w-100" :disabled="!canPay">
                  <span
                    v-if="isPaying"
                    class="spinner-border spinner-border-sm me-2"
                    role="status"
                    aria-hidden="true"
                  ></span>
                  {{ isPaying ? 'Processing…' : `Pay ${formatPriceFromCents(order.amount)}` }}
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

<style scoped>
.card-element {
  padding: 0.75rem;
}
</style>
