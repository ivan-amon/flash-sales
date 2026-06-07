<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { apiFetch } from '../utils/http'
import type { EventItem } from '../types/event'

const events = ref<EventItem[]>([])
const isLoading = ref(true)
const error = ref<string | null>(null)

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
    const response = await apiFetch('/events')

    if (!response.ok) {
      error.value = `Failed to load events (${response.status}).`
      return
    }

    events.value = await response.json()
  } catch {
    error.value = 'Unable to reach the server. Please try again later.'
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="container py-4">
    <h1 class="mb-4">Events</h1>

    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading…</span>
      </div>
    </div>

    <div v-else-if="error" class="alert alert-danger" role="alert">
      {{ error }}
    </div>

    <p v-else-if="events.length === 0" class="text-muted">
      There are no events available right now.
    </p>

    <div v-else class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
      <div v-for="event in events" :key="event.id" class="col">
        <div class="card h-100 bg-secondary">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ event.title }}</h5>
            <p class="card-text text-light mb-2">
              Sale starts: {{ formatSaleStart(event.sale_starts_at) }}
            </p>
            <span
              class="badge mt-auto align-self-start"
              :class="event.available_tickets > 0 ? 'bg-success' : 'bg-danger'"
            >
              {{ event.available_tickets }} / {{ event.total_tickets }} tickets available
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
