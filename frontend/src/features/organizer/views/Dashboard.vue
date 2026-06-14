<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { apiFetch } from '@/shared/api/http'
import { useAuth } from '@/features/auth/composables/useAuth'
import type { EventItem } from '@/features/events/types/event'
import EventEditModal from '@/features/organizer/components/EventEditModal.vue'
import EventDeleteModal from '@/features/organizer/components/EventDeleteModal.vue'

const { organizer } = useAuth()

const events = ref<EventItem[]>([])
const isLoading = ref(true)
const error = ref<string | null>(null)
const editingEvent = ref<EventItem | null>(null)
const deletingEvent = ref<EventItem | null>(null)

const dateFormatter = new Intl.DateTimeFormat(undefined, {
  dateStyle: 'medium',
  timeStyle: 'short',
})

function formatSaleStart(value: string | null): string {
  return value ? dateFormatter.format(new Date(value)) : 'TBA'
}

onMounted(async () => {
  try {
    const response = await apiFetch('/events')

    if (!response.ok) {
      error.value = `Failed to load your events (${response.status}).`
      return
    }

    const all = (await response.json()) as EventItem[]
    events.value = all.filter((event) => event.organizer_id === organizer.value?.id)
  } catch {
    error.value = 'Unable to reach the server. Please try again later.'
  } finally {
    isLoading.value = false
  }
})

function onUpdated(updated: EventItem): void {
  const index = events.value.findIndex((event) => event.id === updated.id)
  if (index !== -1) {
    events.value[index] = { ...events.value[index], ...updated }
  }
  editingEvent.value = null
}

function onDeleted(id: number): void {
  events.value = events.value.filter((event) => event.id !== id)
  deletingEvent.value = null
}
</script>

<template>
  <div class="container py-4">
    <h1 class="mb-4">My Events</h1>

    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading…</span>
      </div>
    </div>

    <div v-else-if="error" class="alert alert-danger" role="alert">
      {{ error }}
    </div>

    <div v-else-if="events.length === 0" class="alert alert-info" role="alert">
      You haven't created any events yet.
      <RouterLink :to="{ name: 'organizer-event-create' }" class="alert-link">
        Create one
      </RouterLink>.
    </div>

    <div v-else class="table-responsive">
      <table class="table table-dark table-hover align-middle">
        <thead>
          <tr>
            <th>Title</th>
            <th>Total tickets</th>
            <th>Available</th>
            <th>Sale starts</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="event in events" :key="event.id">
            <td>{{ event.title }}</td>
            <td>{{ event.total_tickets }}</td>
            <td>{{ event.available_tickets }}</td>
            <td>{{ formatSaleStart(event.sale_starts_at) }}</td>
            <td class="text-end">
              <button
                type="button"
                class="btn btn-sm btn-outline-primary rounded-1 me-2"
                aria-label="Edit event"
                title="Edit"
                @click="editingEvent = event"
              >
                <i class="bi bi-pencil"></i>
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-danger rounded-1"
                aria-label="Delete event"
                title="Delete"
                @click="deletingEvent = event"
              >
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <EventEditModal
      v-if="editingEvent"
      :event="editingEvent"
      @updated="onUpdated"
      @close="editingEvent = null"
    />

    <EventDeleteModal
      v-if="deletingEvent"
      :event="deletingEvent"
      @deleted="onDeleted"
      @close="deletingEvent = null"
    />
  </div>
</template>
