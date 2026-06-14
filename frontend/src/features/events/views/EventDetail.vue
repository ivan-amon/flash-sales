<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiFetch } from '@/shared/api/http'
import { useAuth } from '@/features/auth/composables/useAuth'
import type { EventItem } from '@/features/events/types/event'
import type { Order } from '@/features/orders/types/order'

const route = useRoute()
const router = useRouter()
const { isAuthenticated, isUser, isOrganizer } = useAuth()

const id = route.params.id as string

const event = ref<EventItem | null>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)
const reserveError = ref<string | null>(null)
const isReserving = ref(false)

const dateFormatter = new Intl.DateTimeFormat(undefined, {
  dateStyle: 'medium',
  timeStyle: 'short',
})

function formatSaleStart(value: string | null): string {
  if (!value) {
    return 'TBA'
  }

  return dateFormatter.format(new Date(value))
}

onMounted(async () => {
  try {
    const response = await apiFetch(`/events/${id}`)

    if (response.status === 404) {
      error.value = 'Event not found.'
      return
    }

    if (!response.ok) {
      error.value = `Failed to load the event (${response.status}).`
      return
    }

    event.value = await response.json()
  } catch {
    error.value = 'Unable to reach the server. Please try again later.'
  } finally {
    isLoading.value = false
  }
})

async function reserveTicket(): Promise<void> {
  if (!event.value) {
    return
  }

  isReserving.value = true
  reserveError.value = null

  try {
    const response = await apiFetch('/orders', {
      method: 'POST',
      body: JSON.stringify({ event_id: event.value.id }),
    })

    if (response.ok) {
      const order = (await response.json()) as Order
      await router.push({ name: 'order-checkout', params: { id: order.id } })
      return
    }

    if (response.status === 403 || response.status === 409) {
      const data = (await response.json()) as { error: string }
      reserveError.value = data.error
    } else {
      reserveError.value = 'Could not reserve a ticket. Please try again.'
    }
  } catch {
    reserveError.value = 'Unable to reach the server. Please try again later.'
  } finally {
    isReserving.value = false
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

    <div v-else-if="event" class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card bg-secondary">
          <div class="card-body">
            <h1 class="card-title h3 mb-3">{{ event.title }}</h1>

            <p class="card-text text-light mb-3">
              Sale starts: {{ formatSaleStart(event.sale_starts_at) }}
            </p>

            <span
              class="badge mb-4"
              :class="event.available_tickets > 0 ? 'bg-success' : 'bg-danger'"
            >
              <template v-if="event.available_tickets > 0">
                {{ event.available_tickets }} / {{ event.total_tickets }} tickets available
              </template>
              <template v-else>Sold Out!</template>
            </span>

            <div v-if="reserveError" class="alert alert-warning" role="alert">
              {{ reserveError }}
            </div>

            <div v-if="!isOrganizer" class="d-grid">
              <button
                v-if="!isAuthenticated"
                type="button"
                class="btn btn-primary"
                @click="router.push('/login')"
              >
                Log in to reserve
              </button>

              <button
                v-else-if="isUser"
                type="button"
                class="btn btn-primary"
                :disabled="isReserving || event.available_tickets === 0"
                @click="reserveTicket"
              >
                <span
                  v-if="isReserving"
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                  aria-hidden="true"
                ></span>
                {{ event.available_tickets === 0 ? 'Sold out' : 'Reserve Ticket' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
