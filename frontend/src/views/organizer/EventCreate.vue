<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/http'
import { combineDateTime } from '../../utils/datetime'
import type { ValidationErrors } from '../../types/user'

const router = useRouter()

const title = ref('')
const totalTickets = ref<number | null>(null)
const saleDate = ref('')
const saleTime = ref('')
const errors = ref<ValidationErrors>({})
const generalError = ref<string | null>(null)
const isSubmitting = ref(false)

async function handleSubmit(): Promise<void> {
  isSubmitting.value = true
  errors.value = {}
  generalError.value = null

  try {
    const response = await apiFetch('/events', {
      method: 'POST',
      body: JSON.stringify({
        title: title.value,
        total_tickets: totalTickets.value,
        sale_starts_at: combineDateTime(saleDate.value, saleTime.value),
      }),
    })

    if (response.ok) {
      await router.push('/events')
      return
    }

    if (response.status === 422) {
      const data = (await response.json()) as { errors: ValidationErrors }
      errors.value = data.errors
    } else {
      generalError.value = 'Could not create the event. Please try again.'
    }
  } catch {
    generalError.value = 'Unable to reach the server. Please try again later.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-6">
        <div class="card">
          <div class="card-body">
            <h1 class="card-title h4 mb-4">Create event</h1>

            <div v-if="generalError" class="alert alert-danger" role="alert">
              {{ generalError }}
            </div>

            <form novalidate @submit.prevent="handleSubmit">
              <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input
                  id="title"
                  v-model="title"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': errors.title }"
                />
                <div v-if="errors.title" class="invalid-feedback">
                  {{ errors.title[0] }}
                </div>
              </div>

              <div class="mb-3">
                <label for="total_tickets" class="form-label">Total tickets</label>
                <input
                  id="total_tickets"
                  v-model.number="totalTickets"
                  type="number"
                  min="1"
                  class="form-control"
                  :class="{ 'is-invalid': errors.total_tickets }"
                />
                <div v-if="errors.total_tickets" class="invalid-feedback">
                  {{ errors.total_tickets[0] }}
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label">
                  Sale starts at <span class="text-muted fst-italic">(date and time)</span>
                </label>
                <div class="row g-2">
                  <div class="col-7">
                    <input
                      id="sale_date"
                      v-model="saleDate"
                      type="date"
                      class="form-control"
                      :class="{ 'is-invalid': errors.sale_starts_at }"
                      aria-label="Sale start date"
                    />
                  </div>
                  <div class="col-5">
                    <input
                      id="sale_time"
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

              <button type="submit" class="btn btn-primary w-100" :disabled="isSubmitting">
                <span
                  v-if="isSubmitting"
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                  aria-hidden="true"
                ></span>
                {{ isSubmitting ? 'Creating…' : 'Create event' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
