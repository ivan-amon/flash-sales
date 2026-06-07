<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { apiFetch } from '../utils/http'
import type { EventItem } from '../types/event'

const events = ref<EventItem[]>([])
const isLoading = ref(true)
const error = ref<string | null>(null)
const searchQuery = ref('')

const filteredEvents = computed(() =>
  events.value.filter((event) =>
    event.title.toLowerCase().includes(searchQuery.value.trim().toLowerCase()),
  ),
)

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
    <div
      class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-3 mb-4"
    >
      <h1 class="mb-0">Events</h1>
      <input
        v-model="searchQuery"
        type="search"
        class="form-control search-input"
        placeholder="Search events…"
        aria-label="Search events"
      />
    </div>

    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading…</span>
      </div>
    </div>

    <div v-else-if="error" class="alert alert-danger" role="alert">
      {{ error }}
    </div>

    <p v-else-if="filteredEvents.length === 0" class="text-muted">
      {{ searchQuery ? 'No events match your search.' : 'There are no events available right now.' }}
    </p>

    <div v-else class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
      <div v-for="event in filteredEvents" :key="event.id" class="col">
        <RouterLink
          :to="{ name: 'event-detail', params: { id: event.id } }"
          class="card event-card h-100 bg-secondary text-reset text-decoration-none"
        >
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ event.title }}</h5>
            <p class="card-text text-light mb-2">
              Sale starts: {{ formatSaleStart(event.sale_starts_at) }}
            </p>
            <span
              class="badge mt-auto align-self-start"
              :class="event.available_tickets > 0 ? 'bg-success' : 'bg-danger'"
            >
              <template v-if="event.available_tickets > 0">
                {{ event.available_tickets }} / {{ event.total_tickets }} tickets available
              </template>
              <template v-else>Sold Out!</template>
            </span>
          </div>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
@media (min-width: 576px) {
  .search-input {
    max-width: 18rem;
  }
}

.event-card {
  transition:
    transform 0.15s ease,
    box-shadow 0.15s ease;
}

.event-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.4);
}
</style>
