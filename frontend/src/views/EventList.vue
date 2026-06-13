<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { apiFetch } from '../utils/http'
import type { EventItem } from '../types/event'

const events = ref<EventItem[]>([])
const isLoading = ref(true)
const error = ref<string | null>(null)
const searchQuery = ref('')
const onlyAvailable = ref(false)
const selectedCity = ref('')

const cities = computed(() =>
  [...new Set(events.value.map((event) => event.city?.name).filter((name): name is string => !!name))].sort(
    (a, b) => a.localeCompare(b),
  ),
)

const isFiltering = computed(
  () => searchQuery.value.trim() !== '' || onlyAvailable.value || selectedCity.value !== '',
)

const filteredEvents = computed(() =>
  events.value.filter((event) => {
    const matchesTitle = event.title
      .toLowerCase()
      .includes(searchQuery.value.trim().toLowerCase())

    if (!matchesTitle) {
      return false
    }

    if (selectedCity.value !== '' && event.city?.name !== selectedCity.value) {
      return false
    }

    return !onlyAvailable.value || event.available_tickets > 0
  }),
)

const dateFormatter = new Intl.DateTimeFormat(undefined, {
  dateStyle: 'medium',
  timeStyle: 'short',
})

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
      <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2 gap-sm-3">
        <input
          v-model="searchQuery"
          type="search"
          class="form-control search-input"
          placeholder="Search events…"
          aria-label="Search events"
        />
        <select
          v-model="selectedCity"
          class="form-select city-select"
          aria-label="Filter by city"
        >
          <option value="">All cities</option>
          <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
        </select>
        <div class="form-check form-switch mb-0">
          <input
            id="only-available"
            v-model="onlyAvailable"
            class="form-check-input"
            type="checkbox"
            role="switch"
          />
          <label class="form-check-label text-nowrap" for="only-available">
            Available only
          </label>
        </div>
      </div>
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
      {{ isFiltering ? 'No events match your filters.' : 'There are no events available right now.' }}
    </p>

    <div v-else class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
      <div v-for="event in filteredEvents" :key="event.id" class="col">
        <RouterLink
          :to="{ name: 'event-detail', params: { id: event.id } }"
          class="card event-card h-100 bg-secondary text-reset text-decoration-none"
        >
          <div class="card-body d-flex flex-column position-relative">
            <span
              class="badge position-absolute top-0 end-0 m-3"
              :class="event.available_tickets > 0 ? 'bg-success' : 'bg-danger'"
            >
              <template v-if="event.available_tickets > 0">
                {{ event.available_tickets }} / {{ event.total_tickets }} tickets available
              </template>
              <template v-else>Sold Out!</template>
            </span>
            <h5 class="card-title pe-5">{{ event.title }}</h5>
            <p v-if="event.city" class="card-text text-light mb-1">
              <i class="bi bi-geo-alt me-1"></i>{{ event.city.name
              }}<template v-if="event.city.country">, {{ event.city.country.name }}</template>
            </p>
            <p class="card-text text-light mb-2">
              <i class="bi bi-clock me-1"></i>{{ dateFormatter.format(new Date(event.event_starts_at)) }}
            </p>
            <span
              v-if="event.available_tickets > 0"
              class="btn btn-primary w-100 mt-2"
            >
              Reserve Ticket
            </span>
            <span v-else class="btn btn-primary w-100 mt-2">
              More Information
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

  .city-select {
    max-width: 12rem;
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
