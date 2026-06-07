<script setup lang="ts">
import { computed, ref } from 'vue'
import { apiFetch } from '../../utils/http'
import type { EventItem } from '../../types/event'

const props = defineProps<{ event: EventItem }>()
const emit = defineEmits<{
  deleted: [id: number]
  close: []
}>()

const confirmText = ref('')
const generalError = ref<string | null>(null)
const isDeleting = ref(false)

const canDelete = computed(() => confirmText.value === props.event.title && !isDeleting.value)

async function remove(): Promise<void> {
  isDeleting.value = true
  generalError.value = null

  try {
    const response = await apiFetch(`/events/${props.event.id}`, {
      method: 'DELETE',
    })

    if (response.ok) {
      emit('deleted', props.event.id)
      return
    }

    generalError.value = 'Could not delete the event. Please try again.'
  } catch {
    generalError.value = 'Unable to reach the server. Please try again later.'
  } finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <div
    class="modal fade show"
    style="display: block"
    tabindex="-1"
    role="dialog"
    @click.self="emit('close')"
  >
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete event</h5>
          <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="emit('close')"></button>
        </div>
        <div class="modal-body">
          <div v-if="generalError" class="alert alert-danger" role="alert">
            {{ generalError }}
          </div>

          <div class="alert alert-danger" role="alert">
            This permanently deletes the event and its tickets. This cannot be undone.
          </div>

          <p class="mb-2">
            To confirm, type the event title
            <strong>{{ event.title }}</strong> below.
          </p>

          <form novalidate @submit.prevent="remove">
            <input
              v-model="confirmText"
              type="text"
              class="form-control mb-3"
              placeholder="Event title"
              aria-label="Type the event title to confirm"
            />

            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-secondary" @click="emit('close')">Cancel</button>
              <button type="submit" class="btn btn-danger" :disabled="!canDelete">
                <span
                  v-if="isDeleting"
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                  aria-hidden="true"
                ></span>
                {{ isDeleting ? 'Deleting…' : 'Delete' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="modal-backdrop fade show"></div>
</template>
