<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
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
  weekday: 'long',
  day: 'numeric',
  month: 'long',
  year: 'numeric',
})

const timeFormatter = new Intl.DateTimeFormat(undefined, {
  hour: '2-digit',
  minute: '2-digit',
})

const saleFormatter = new Intl.DateTimeFormat(undefined, {
  dateStyle: 'medium',
  timeStyle: 'short',
})

const eventDate = computed(() =>
  event.value ? dateFormatter.format(new Date(event.value.event_starts_at)) : '',
)

const eventTime = computed(() =>
  event.value ? timeFormatter.format(new Date(event.value.event_starts_at)) : '',
)

const saleStart = computed(() =>
  event.value ? saleFormatter.format(new Date(event.value.sale_starts_at)) : '',
)

const isSaleLive = computed(
  () => !!event.value && new Date(event.value.sale_starts_at) <= new Date(),
)

const location = computed(() => {
  const city = event.value?.city
  if (!city) {
    return null
  }

  return city.country ? `${city.name}, ${city.country.name}` : city.name
})

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

    <div v-else-if="event">
      <RouterLink
        :to="{ name: 'events' }"
        class="btn btn-sm btn-outline-light mb-3"
      >
        <i class="bi bi-arrow-left me-1"></i>Back to events
      </RouterLink>

      <div class="card bg-secondary border-0 shadow overflow-hidden">
        <div class="row g-0">
          <div class="col-lg-7 order-lg-2">
            <div class="event-hero h-100">
              <img
                v-if="event.cover_image_url"
                :src="event.cover_image_url"
                :alt="event.title"
                class="event-hero__img"
              />
              <div v-else class="event-hero__placeholder">
                <i class="bi bi-calendar-event"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-5 order-lg-1">
            <div class="card-body p-4 p-lg-5 d-flex flex-column h-100">
              <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                <h1 class="h2 mb-0">{{ event.title }}</h1>
                <span
                  class="badge shrink-0"
                  :class="event.available_tickets > 0 ? 'bg-success' : 'bg-danger'"
                >
                  <template v-if="event.available_tickets > 0">
                    {{ event.available_tickets }} / {{ event.total_tickets }} tickets available
                  </template>
                  <template v-else>Sold Out!</template>
                </span>
              </div>

              <ul class="list-unstyled mb-4">
                <li class="d-flex align-items-start mb-3">
                  <i class="bi bi-calendar-event fs-5 me-3 text-info"></i>
                  <div>
                    <div class="fw-semibold">{{ eventDate }}</div>
                    <div class="text-light">{{ eventTime }}</div>
                  </div>
                </li>
                <li v-if="location" class="d-flex align-items-start mb-3">
                  <i class="bi bi-geo-alt fs-5 me-3 text-info"></i>
                  <div>
                    <div class="fw-semibold">{{ location }}</div>
                  </div>
                </li>
                <li class="d-flex align-items-start">
                  <i class="bi bi-tag fs-5 me-3 text-info"></i>
                  <div>
                    <div class="fw-semibold">Sale starts</div>
                    <div class="text-light">{{ saleStart }}</div>
                  </div>
                </li>
              </ul>

              <div v-if="reserveError" class="alert alert-warning" role="alert">
                {{ reserveError }}
              </div>

              <div v-if="!isOrganizer" class="d-grid mt-auto">
                <button
                  v-if="!isAuthenticated"
                  type="button"
                  class="btn btn-primary btn-lg"
                  @click="router.push('/login')"
                >
                  Log in to reserve
                </button>

                <button
                  v-else-if="isUser"
                  type="button"
                  class="btn btn-primary btn-lg"
                  :disabled="isReserving || event.available_tickets === 0 || !isSaleLive"
                  @click="reserveTicket"
                >
                  <span
                    v-if="isReserving"
                    class="spinner-border spinner-border-sm me-2"
                    role="status"
                    aria-hidden="true"
                  ></span>
                  <template v-if="event.available_tickets === 0">Sold out</template>
                  <template v-else-if="!isSaleLive">Sale not started yet</template>
                  <template v-else>Reserve Ticket</template>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.event-hero {
  min-height: 100%;
  background-color: var(--bs-dark);
}

.event-hero__img {
  width: 100%;
  height: 100%;
  min-height: 18rem;
  object-fit: cover;
}

.event-hero__placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  min-height: 18rem;
  color: rgba(255, 255, 255, 0.25);
  font-size: 5rem;
}
</style>
