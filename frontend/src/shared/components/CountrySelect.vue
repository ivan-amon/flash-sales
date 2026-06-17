<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useAuth } from '@/features/auth/composables/useAuth'
import { useCountry } from '@/shared/composables/useCountry'
import { flagEmoji } from '@/shared/utils/format'

const { countries, selectedCountry, resolvedCountry, loadCountries, setCountry } = useCountry()
const { isUser, user, updateCountry } = useAuth()

const isOpen = ref(false)
const root = ref<HTMLElement | null>(null)

onMounted(() => {
  loadCountries()
  document.addEventListener('click', onClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onClickOutside)
})

const current = computed<string>(() => {
  if (isUser.value) {
    return user.value?.country_code ?? ''
  }

  return selectedCountry.value ?? resolvedCountry.value ?? ''
})

function onClickOutside(event: MouseEvent): void {
  if (root.value && !root.value.contains(event.target as Node)) {
    isOpen.value = false
  }
}

async function select(code: string): Promise<void> {
  // Authenticated users keep their country on their profile, so the backend
  // can honour it regardless of the request headers.
  if (isUser.value) {
    const ok = await updateCountry(code)
    if (!ok) {
      return
    }
  }

  setCountry(code)
  isOpen.value = false
}
</script>

<template>
  <div ref="root" class="dropdown">
    <button
      type="button"
      class="btn btn-dark btn-sm dropdown-toggle"
      aria-label="Select country"
      :aria-expanded="isOpen"
      @click="isOpen = !isOpen"
    >
      <span class="fs-6">{{ current ? flagEmoji(current) : '🌐' }}</span>
    </button>

    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" :class="{ show: isOpen }">
      <li v-for="country in countries" :key="country.iso_code">
        <button
          type="button"
          class="dropdown-item d-flex align-items-center gap-2"
          :class="{ active: country.iso_code === current }"
          @click="select(country.iso_code)"
        >
          <span class="fs-6">{{ flagEmoji(country.iso_code) }}</span>
          <span>{{ country.iso_code }}</span>
        </button>
      </li>
    </ul>
  </div>
</template>
