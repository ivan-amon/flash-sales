<script setup lang="ts">
import { ref } from 'vue'
import { apiFetch } from '../../utils/http'
import { combineDateTime, splitDateTime } from '../../utils/datetime'
import type { EventItem } from '../../types/event'
import type { ValidationErrors } from '../../types/user'

const props = defineProps<{ event: EventItem }>()
const emit = defineEmits<{
  updated: [event: EventItem]
  close: []
}>()

const initial = splitDateTime(props.event.sale_starts_at)

const title = ref(props.event.title)
const totalTickets = ref<number | null>(props.event.total_tickets)
const saleDate = ref(initial.date)
const saleTime = ref(initial.time)
const errors = ref<ValidationErrors>({})
const generalError = ref<string | null>(null)
const isSubmitting = ref(false)

async function submit(): Promise<void> {
  isSubmitting.value = true
  errors.value = {}
  generalError.value = null

  try {
    const response = await apiFetch(`/events/${props.event.id}`, {
      method: 'PUT',
      body: JSON.stringify({
        title: title.value,
        total_tickets: totalTickets.value,
        sale_starts_at: combineDateTime(saleDate.value, saleTime.value),
      }),
    })

    if (response.ok) {
      emit('updated', (await response.json()) as EventItem)
      return
    }

    if (response.status === 422) {
      const data = (await response.json()) as { errors: ValidationErrors }
      errors.value = data.errors
    } else {
      generalError.value = 'Could not update the event. Please try again.'
    }
  } catch {
    generalError.value = 'Unable to reach the server. Please try again later.'
  } finally {
    isSubmitting.value = false
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
          <h5 class="modal-title">Edit event</h5>
          <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="emit('close')"></button>
        </div>
        <div class="modal-body">
          <div v-if="generalError" class="alert alert-danger" role="alert">
            {{ generalError }}
          </div>

          <form novalidate @submit.prevent="submit">
            <div class="mb-3">
              <label for="edit_title" class="form-label">Title</label>
              <input
                id="edit_title"
                v-model="title"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.title }"
              />
              <div v-if="errors.title" class="invalid-feedback">{{ errors.title[0] }}</div>
            </div>

            <div class="mb-3">
              <label for="edit_total_tickets" class="form-label">Total tickets</label>
              <input
                id="edit_total_tickets"
                v-model.number="totalTickets"
                type="number"
                min="1"
                class="form-control"
                :class="{ 'is-invalid': errors.total_tickets }"
              />
              <div v-if="errors.total_tickets" class="invalid-feedback">{{ errors.total_tickets[0] }}</div>
            </div>

            <div class="mb-3">
              <label class="form-label">
                Sale starts at <span class="text-muted fst-italic">(date and time)</span>
              </label>
              <div class="row g-2">
                <div class="col-7">
                  <input
                    id="edit_sale_date"
                    v-model="saleDate"
                    type="date"
                    class="form-control"
                    :class="{ 'is-invalid': errors.sale_starts_at }"
                    aria-label="Sale start date"
                  />
                </div>
                <div class="col-5">
                  <input
                    id="edit_sale_time"
                    v-model="saleTime"
                    type="time"
                    class="form-control"
                    :class="{ 'is-invalid': errors.sale_starts_at }"
                    aria-label="Sale start time"
                  />
                </div>
              </div>
              <div v-if="errors.sale_starts_at" class="invalid-feedback d-block">
                {{ errors.sale_starts_at[0] }}
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-secondary" @click="emit('close')">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                <span
                  v-if="isSubmitting"
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                  aria-hidden="true"
                ></span>
                {{ isSubmitting ? 'Saving…' : 'Save changes' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="modal-backdrop fade show"></div>
</template>
