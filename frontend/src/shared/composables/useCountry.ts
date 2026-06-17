import { readonly, ref } from 'vue'
import { apiFetch, COUNTRY_STORAGE_KEY } from '@/shared/api/http'
import type { Country } from '@/features/events/types/event'

const selectedCountry = ref<string | null>(localStorage.getItem(COUNTRY_STORAGE_KEY))
const resolvedCountry = ref<string | null>(null)
const countries = ref<Country[]>([])

async function loadCountries(): Promise<void> {
  if (countries.value.length > 0) {
    return
  }

  try {
    const response = await apiFetch('/countries')
    if (response.ok) {
      countries.value = (await response.json()) as Country[]
    }
  } catch {
    // Silent: the selector simply stays empty if countries can't be loaded.
  }
}

function setCountry(code: string): void {
  selectedCountry.value = code
  localStorage.setItem(COUNTRY_STORAGE_KEY, code)
}

/**
 * Records the country the backend actually resolved (via IP or fallback) so the
 * selector can reflect it when the visitor has not made an explicit choice. This
 * is display-only and intentionally not persisted.
 */
function setResolvedCountry(code: string | null): void {
  resolvedCountry.value = code
}

export function useCountry() {
  return {
    selectedCountry: readonly(selectedCountry),
    resolvedCountry: readonly(resolvedCountry),
    countries: readonly(countries),
    loadCountries,
    setCountry,
    setResolvedCountry,
  }
}
