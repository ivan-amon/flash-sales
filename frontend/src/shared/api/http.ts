export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api'
export const TOKEN_STORAGE_KEY = 'auth_token'
export const COUNTRY_STORAGE_KEY = 'country_code'

export async function apiFetch(path: string, options: RequestInit = {}): Promise<Response> {
  const headers = new Headers(options.headers)
  headers.set('Accept', 'application/json')

  if (options.body && !(options.body instanceof FormData) && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json')
  }

  const token = localStorage.getItem(TOKEN_STORAGE_KEY)
  if (token) {
    headers.set('Authorization', `Bearer ${token}`)
  }

  const countryCode = localStorage.getItem(COUNTRY_STORAGE_KEY)
  if (countryCode) {
    headers.set('X-Country-Code', countryCode)
  }

  return fetch(`${API_BASE_URL}${path}`, {
    ...options,
    headers,
  })
}
