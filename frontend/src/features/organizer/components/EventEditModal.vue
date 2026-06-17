<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { apiFetch } from '@/shared/api/http'
import { MIN_EVENT_LEAD_MINUTES, combineDateTime, splitDateTime } from '@/shared/utils/datetime'
import { flagEmoji } from '@/shared/utils/format'
import type { City, Country, EventItem } from '@/features/events/types/event'
import type { ValidationErrors } from '@/features/auth/types/user'

const props = defineProps<{ event: EventItem }>()
const emit = defineEmits<{
  updated: [event: EventItem]
  close: []
}>()

const saleInitial = splitDateTime(props.event.sale_starts_at)
const eventInitial = splitDateTime(props.event.event_starts_at)

const title = ref(props.event.title)
const totalTickets = ref<number | null>(props.event.total_tickets)
const saleDate = ref(saleInitial.date)
const saleTime = ref(saleInitial.time)
const eventDate = ref(eventInitial.date)
const eventTime = ref(eventInitial.time)

const countries = ref<Country[]>([])
const cities = ref<City[]>([])
const countryCode = ref<string | null>(props.event.city?.country_code ?? null)
const cityId = ref<number | null>(props.event.city_id)

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

  if (countryCode.value !== null) {
    await loadCities(countryCode.value)
  }
})

async function loadCities(code: string): Promise<void> {
  try {
    const response = await apiFetch(`/cities?country_code=${code}`)
    if (response.ok) {
      cities.value = (await response.json()) as City[]
    }
  } catch {
    generalError.value = 'Unable to load cities. Please try again later.'
  }
}

watch(countryCode, async (code) => {
  cityId.value = null
  cities.value = []

  if (code === null) {
    return
  }

  await loadCities(code)
})

function validateDates(): boolean {
  const validationErrors: ValidationErrors = {}

  const eventIso = combineDateTime(eventDate.value, eventTime.value)
  const minEventStart = Date.now() + MIN_EVENT_LEAD_MINUTES * 60 * 1000

  if (!eventIso || new Date(eventIso).getTime() < minEventStart) {
    validationErrors.event_starts_at = [
      `The event must start at least ${MIN_EVENT_LEAD_MINUTES} minutes from now.`,
    ]
  }

  const saleIso = combineDateTime(saleDate.value, saleTime.value)

  if (!saleIso) {
    validationErrors.sale_starts_at = ['The sale start date and time are required.']
  } else if (eventIso && new Date(saleIso).getTime() > new Date(eventIso).getTime()) {
    validationErrors.sale_starts_at = ['The sale must start before the event begins.']
  }

  if (Object.keys(validationErrors).length > 0) {
    errors.value = validationErrors
    return false
  }

  return true
}

async function submit(): Promise<void> {
  errors.value = {}
  generalError.value = null

  if (!validateDates()) {
    return
  }

  isSubmitting.value = true

  try {
    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('title', title.value)
    formData.append('total_tickets', totalTickets.value === null ? '' : String(totalTickets.value))
    formData.append('city_id', cityId.value === null ? '' : String(cityId.value))
    formData.append('sale_starts_at', combineDateTime(saleDate.value, saleTime.value) ?? '')
    formData.append('event_starts_at', combineDateTime(eventDate.value, eventTime.value) ?? '')

    const response = await apiFetch(`/events/${props.event.id}`, {
      method: 'POST',
      body: formData,
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

            <div class="row g-2 mb-3">
              <div class="col-sm-6">
                <label for="edit_country" class="form-label">Country</label>
                <select id="edit_country" v-model="countryCode" class="form-select">
                  <option :value="null" disabled>Select a country…</option>
                  <option v-for="country in countries" :key="country.iso_code" :value="country.iso_code">
                    {{ flagEmoji(country.iso_code) }} {{ country.name }}
                  </option>
                </select>
              </div>
              <div class="col-sm-6">
                <label for="edit_city" class="form-label">City</label>
                <select
                  id="edit_city"
                  v-model.number="cityId"
                  class="form-select"
                  :class="{ 'is-invalid': errors.city_id }"
                  :disabled="countryCode === null"
                >
                  <option :value="null" disabled>
                    {{ countryCode === null ? 'Pick a country first' : 'Select a city…' }}
                  </option>
                  <option v-for="city in cities" :key="city.id" :value="city.id">
                    {{ city.name }}
                  </option>
                </select>
                <div v-if="errors.city_id" class="invalid-feedback">{{ errors.city_id[0] }}</div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Event starts at</label>
              <div class="row g-2">
                <div class="col-7">
                  <input
                    id="edit_event_date"
                    v-model="eventDate"
                    type="date"
                    class="form-control"
                    :class="{ 'is-invalid': errors.event_starts_at }"
                    aria-label="Event start date"
                  />
                </div>
                <div class="col-5">
                  <input
                    id="edit_event_time"
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

            <div class="mb-3">
              <label class="form-label">Sale starts at</label>
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
