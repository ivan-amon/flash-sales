<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useAuth } from '@/features/auth/composables/useAuth'
import { useCountry } from '@/shared/composables/useCountry'
import { flagEmoji } from '@/shared/utils/format'

const { countries, selectedCountry, resolvedCountry, loadCountries, setCountry } = useCountry()
const { isUser, user, updateCountry } = useAuth()

onMounted(loadCountries)

const current = computed<string>(() => {
  if (isUser.value) {
    return user.value?.country_code ?? ''
  }

  return selectedCountry.value ?? resolvedCountry.value ?? ''
})

async function onChange(event: Event): Promise<void> {
  const code = (event.target as HTMLSelectElement).value
  if (!code) {
    return
  }

  // Authenticated users keep their country on their profile, so the backend
  // can honour it regardless of the request headers.
  if (isUser.value) {
    const ok = await updateCountry(code)
    if (!ok) {
      return
    }
  }

  setCountry(code)
}
</script>

<template>
  <select
    class="form-select form-select-sm w-auto"
    aria-label="Select country"
    :value="current"
    @change="onChange"
  >
    <option value="" disabled>🌐</option>
    <option v-for="country in countries" :key="country.iso_code" :value="country.iso_code">
      {{ flagEmoji(country.iso_code) }} {{ country.iso_code }}
    </option>
  </select>
</template>
