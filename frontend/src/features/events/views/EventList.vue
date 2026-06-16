<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { apiFetch } from '@/shared/api/http'
import EventCard from '@/features/events/components/EventCard.vue'
import type { EventItem, Paginated } from '@/features/events/types/event'

const PER_PAGE = 15

const events = ref<EventItem[]>([])
const isLoading = ref(true)
const isLoadingMore = ref(false)
const nextPage = ref<number | null>(1)
const error = ref<string | null>(null)
const sentinel = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

const hasMore = computed(() => nextPage.value !== null)

const loadEvents = async (): Promise<void> => {
  if (nextPage.value === null || isLoadingMore.value) {
    return
  }

  const page = nextPage.value
  if (page > 1) {
    isLoadingMore.value = true
  }

  try {
    const response = await apiFetch(`/events?page=${page}&per_page=${PER_PAGE}`)

    if (!response.ok) {
      error.value = `Failed to load events (${response.status}).`
      nextPage.value = null
      return
    }

    const payload: Paginated<EventItem> = await response.json()
    events.value.push(...payload.data)
    nextPage.value = payload.next_page_url ? payload.current_page + 1 : null
  } catch {
    error.value = 'Unable to reach the server. Please try again later.'
    nextPage.value = null
  } finally {
    isLoading.value = false
    isLoadingMore.value = false
  }
}
const searchQuery = ref('')
const onlyAvailable = ref(false)
const selectedCity = ref('')
const isFilterPanelOpen = ref(false)

type DatePreset = '' | 'today' | 'weekend' | 'week' | 'month'

const datePresets: { value: Exclude<DatePreset, ''>; label: string }[] = [
  { value: 'today', label: 'Today' },
  { value: 'weekend', label: 'This weekend' },
  { value: 'week', label: 'This week' },
  { value: 'month', label: 'This month' },
]

const selectedDatePreset = ref<DatePreset>('')

const cities = computed(() =>
  [...new Set(events.value.map((event) => event.city?.name).filter((name): name is string => !!name))].sort(
    (a, b) => a.localeCompare(b),
  ),
)

const startOfDay = (date: Date): Date => {
  const result = new Date(date)
  result.setHours(0, 0, 0, 0)
  return result
}

const endOfDay = (date: Date): Date => {
  const result = new Date(date)
  result.setHours(23, 59, 59, 999)
  return result
}

const addDays = (date: Date, days: number): Date => {
  const result = new Date(date)
  result.setDate(result.getDate() + days)
  return result
}

const dateRange = computed<{ start: Date | null; end: Date | null }>(() => {
  if (selectedDatePreset.value === '') {
    return { start: null, end: null }
  }

  const now = new Date()
  const dayOfWeek = now.getDay()

  switch (selectedDatePreset.value) {
    case 'today':
      return { start: startOfDay(now), end: endOfDay(now) }
    case 'weekend': {
      if (dayOfWeek === 0) {
        return { start: startOfDay(now), end: endOfDay(now) }
      }
      const saturday = addDays(now, (6 - dayOfWeek + 7) % 7)
      return { start: startOfDay(saturday), end: endOfDay(addDays(saturday, 1)) }
    }
    case 'week':
      return { start: startOfDay(now), end: endOfDay(addDays(now, (7 - dayOfWeek) % 7)) }
    case 'month':
      return {
        start: startOfDay(now),
        end: endOfDay(new Date(now.getFullYear(), now.getMonth() + 1, 0)),
      }
  }

  return { start: null, end: null }
})

const activeFilterCount = computed(
  () =>
    (onlyAvailable.value ? 1 : 0) +
    (selectedCity.value !== '' ? 1 : 0) +
    (selectedDatePreset.value !== '' ? 1 : 0),
)

const isFiltering = computed(
  () => searchQuery.value.trim() !== '' || activeFilterCount.value > 0,
)

const clearFilters = (): void => {
  onlyAvailable.value = false
  selectedCity.value = ''
  selectedDatePreset.value = ''
}

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

    const { start, end } = dateRange.value
    if (start || end) {
      const eventDate = new Date(event.event_starts_at)
      if (start && eventDate < start) {
        return false
      }
      if (end && eventDate > end) {
        return false
      }
    }

    return !onlyAvailable.value || event.available_tickets > 0
  }),
)

onMounted(async () => {
  await loadEvents()

  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0]?.isIntersecting) {
        void loadEvents()
      }
    },
    { rootMargin: '200px' },
  )

  watch(
    sentinel,
    (element) => {
      observer?.disconnect()
      if (element) {
        observer?.observe(element)
      }
    },
    { immediate: true },
  )
})

onBeforeUnmount(() => {
  observer?.disconnect()
})
</script>

<template>
  <div class="container py-4">
    <div
      class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-3 mb-3"
    >
      <h1 class="mb-0">Events</h1>
      <div class="d-flex align-items-center gap-2 filter-bar">
        <input
          v-model="searchQuery"
          type="search"
          class="form-control"
          placeholder="Search events…"
          aria-label="Search events"
        />
        <button
          type="button"
          class="btn btn-primary d-flex align-items-center position-relative"
          :class="{ active: isFilterPanelOpen }"
          aria-label="Toggle filters"
          :aria-expanded="isFilterPanelOpen"
          @click="isFilterPanelOpen = !isFilterPanelOpen"
        >
          <i class="bi bi-sliders"></i>
          <span
            v-if="activeFilterCount > 0"
            class="position-absolute top-0 start-100 translate-middle p-1 bg-light border border-primary rounded-circle"
          >
            <span class="visually-hidden">filters active</span>
          </span>
        </button>
      </div>
    </div>

    <div v-if="isFilterPanelOpen" class="card bg-secondary mb-4">
      <div class="card-body d-flex flex-column flex-md-row align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-2 grow">
          <label class="form-label mb-0" for="filter-city">City</label>
          <select
            id="filter-city"
            v-model="selectedCity"
            class="form-select"
            aria-label="Filter by city"
          >
            <option value="">All cities</option>
            <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2 grow">
          <label class="form-label mb-0 text-nowrap" for="filter-date">Event date</label>
          <select
            id="filter-date"
            v-model="selectedDatePreset"
            class="form-select"
            aria-label="Filter by event date"
          >
            <option value="">Any date</option>
            <option v-for="preset in datePresets" :key="preset.value" :value="preset.value">
              {{ preset.label }}
            </option>
          </select>
        </div>
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
        <button
          type="button"
          class="btn btn-outline-light ms-md-auto"
          :disabled="activeFilterCount === 0"
          @click="clearFilters"
        >
          Clear filters
        </button>
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
        <EventCard :event="event" />
      </div>
    </div>

    <div ref="sentinel" aria-hidden="true"></div>

    <div v-if="isLoadingMore" class="text-center py-4">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading more…</span>
      </div>
    </div>

    <p v-else-if="!isLoading && !error && !hasMore && events.length > 0" class="text-muted text-center py-4 mb-0">
      You've reached the end.
    </p>
  </div>
</template>

<style scoped>
.filter-bar {
  width: 100%;
}

.filter-bar .form-control {
  flex: 1 1 auto;
  min-width: 0;
}

@media (min-width: 576px) {
  .filter-bar {
    width: auto;
  }

  .filter-bar .form-control {
    width: 18rem;
    flex: 0 0 auto;
  }
}
</style>
