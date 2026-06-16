<script setup lang="ts">
import { RouterLink } from 'vue-router'
import type { EventItem } from '@/features/events/types/event'

defineProps<{ event: EventItem }>()

const dateFormatter = new Intl.DateTimeFormat(undefined, {
  dateStyle: 'medium',
  timeStyle: 'short',
})
</script>

<template>
  <RouterLink
    :to="{ name: 'event-detail', params: { id: event.id } }"
    class="card event-card h-100 bg-secondary text-reset text-decoration-none"
  >
    <div class="card-body d-flex flex-column">
      <h5 class="card-title event-card__title">{{ event.title }}</h5>

      <div class="mb-2">
        <span
          class="badge"
          :class="event.available_tickets > 0 ? 'bg-success' : 'bg-danger'"
        >
          <template v-if="event.available_tickets > 0">
            {{ event.available_tickets }} / {{ event.total_tickets }} tickets available
          </template>
          <template v-else>Sold Out!</template>
        </span>
      </div>

      <p v-if="event.city" class="card-text text-light mb-1 text-truncate">
        <i class="bi bi-geo-alt me-1"></i>{{ event.city.name
        }}<template v-if="event.city.country">, {{ event.city.country.name }}</template>
      </p>
      <p class="card-text text-light mb-3">
        <i class="bi bi-clock me-1"></i>{{ dateFormatter.format(new Date(event.event_starts_at)) }}
      </p>

      <span class="btn btn-primary w-100 mt-auto">
        {{ event.available_tickets > 0 ? 'Reserve Ticket' : 'More Information' }}
      </span>
    </div>
  </RouterLink>
</template>

<style scoped>
.event-card {
  transition:
    transform 0.15s ease,
    box-shadow 0.15s ease;
}

.event-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.4);
}

.event-card__title {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
