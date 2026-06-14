<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '@/shared/api/http'
import { MIN_EVENT_LEAD_MINUTES, combineDateTime, splitDateTime } from '@/shared/utils/datetime'
import { flagEmoji } from '@/shared/utils/format'
import type { City, Country } from '@/features/events/types/event'
import type { ValidationErrors } from '@/features/auth/types/user'

const router = useRouter()

const title = ref('')
const totalTickets = ref<number | null>(null)
const saleMode = ref<'now' | 'schedule'>('now')
const saleDate = ref('')
const saleTime = ref('')
const eventDate = ref('')
const eventTime = ref('')

const countries = ref<Country[]>([])
const cities = ref<City[]>([])
const countryId = ref<number | null>(null)
const cityId = ref<number | null>(null)

const errors = ref<ValidationErrors>({})
const generalError = ref<string | null>(null)
const isSubmitting = ref(false)

onMounted(async () => {
  try {
    const response = await apiFetch('/countries')
    if (response.ok) {
      countries.value = (await response.json()) as Country[]
    }
  } catch {
    generalError.value = 'Unable to load countries. Please try again later.'
  }
})

watch(countryId, async (id) => {
  cityId.value = null
  cities.value = []

  if (id === null) {
    return
  }

  try {
    const response = await apiFetch(`/cities?country_id=${id}`)
    if (response.ok) {
      cities.value = (await response.json()) as City[]
    }
  } catch {
    generalError.value = 'Unable to load cities. Please try again later.'
  }
})

function resolveSaleStartsAt(): string | null {
  if (saleMode.value === 'now') {
    const now = splitDateTime(new Date().toISOString())
    return combineDateTime(now.date, now.time)
  }

  return combineDateTime(saleDate.value, saleTime.value)
}

function validateDates(): boolean {
  const validationErrors: ValidationErrors = {}

  const eventIso = combineDateTime(eventDate.value, eventTime.value)
  const minEventStart = Date.now() + MIN_EVENT_LEAD_MINUTES * 60 * 1000

  if (!eventIso || new Date(eventIso).getTime() < minEventStart) {
    validationErrors.event_starts_at = [
      `The event must start at least ${MIN_EVENT_LEAD_MINUTES} minutes from now.`,
    ]
  }

  if (saleMode.value === 'schedule') {
    const saleIso = combineDateTime(saleDate.value, saleTime.value)

    if (!saleIso || new Date(saleIso).getTime() < Date.now()) {
      validationErrors.sale_starts_at = ['The sale cannot start in the past.']
    } else if (eventIso && new Date(saleIso).getTime() > new Date(eventIso).getTime()) {
      validationErrors.sale_starts_at = ['The sale must start before the event begins.']
    }
  }

  if (Object.keys(validationErrors).length > 0) {
    errors.value = validationErrors
    return false
  }

  return true
}

async function handleSubmit(): Promise<void> {
  errors.value = {}
  generalError.value = null

  if (!validateDates()) {
    return
  }

  isSubmitting.value = true

  try {
    const formData = new FormData()
    formData.append('title', title.value)
    formData.append('total_tickets', totalTickets.value === null ? '' : String(totalTickets.value))
    formData.append('city_id', cityId.value === null ? '' : String(cityId.value))
    formData.append('sale_starts_at', resolveSaleStartsAt() ?? '')
    formData.append('event_starts_at', combineDateTime(eventDate.value, eventTime.value) ?? '')

    const response = await apiFetch('/events', {
      method: 'POST',
      body: formData,
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

              <div class="row g-2 mb-3">
                <div class="col-sm-6">
                  <label for="country" class="form-label">Country</label>
                  <select
                    id="country"
                    v-model.number="countryId"
                    class="form-select"
                  >
                    <option :value="null" disabled>Select a country…</option>
                    <option v-for="country in countries" :key="country.id" :value="country.id">
                      {{ flagEmoji(country.iso_code) }} {{ country.name }}
                    </option>
                  </select>
                </div>
                <div class="col-sm-6">
                  <label for="city" class="form-label">City</label>
                  <select
                    id="city"
                    v-model.number="cityId"
                    class="form-select"
                    :class="{ 'is-invalid': errors.city_id }"
                    :disabled="countryId === null"
                  >
                    <option :value="null" disabled>
                      {{ countryId === null ? 'Pick a country first' : 'Select a city…' }}
                    </option>
                    <option v-for="city in cities" :key="city.id" :value="city.id">
                      {{ city.name }}
                    </option>
                  </select>
                  <div v-if="errors.city_id" class="invalid-feedback">
                    {{ errors.city_id[0] }}
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Event starts at</label>
                <div class="row g-2">
                  <div class="col-7">
                    <input
                      id="event_date"
                      v-model="eventDate"
                      type="date"
                      class="form-control"
                      :class="{ 'is-invalid': errors.event_starts_at }"
                      aria-label="Event start date"
                    />
                  </div>
                  <div class="col-5">
                    <input
                      id="event_time"
                      v-model="eventTime"
                      type="time"
                      class="form-control"
                      :class="{ 'is-invalid': errors.event_starts_at }"
                      aria-label="Event start time"
                    />
                  </div>
                </div>
                <div v-if="errors.event_starts_at" class="invalid-feedback d-block">
                  {{ errors.event_starts_at[0] }}
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label d-block">When should the sale start?</label>
                <div class="btn-group w-100 mb-2" role="group" aria-label="Sale start mode">
                  <input
                    id="sale_now"
                    v-model="saleMode"
                    type="radio"
                    class="btn-check"
                    value="now"
                  />
                  <label class="btn btn-outline-info" for="sale_now">
                    <i class="bi bi-lightning-charge-fill me-1"></i>Start immediately
                  </label>
                  <input
                    id="sale_schedule"
                    v-model="saleMode"
                    type="radio"
                    class="btn-check"
                    value="schedule"
                  />
                  <label class="btn btn-outline-info" for="sale_schedule">
                    <i class="bi bi-calendar-event me-1"></i>Schedule for later
                  </label>
                </div>

                <div v-if="saleMode === 'schedule'" class="row g-2">
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
                <p v-else class="text-muted small mb-0">
                  Tickets go on sale as soon as the event is created.
                </p>
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
