export const API_BASE_URL = 'http://localhost:8000/api'

export async function apiFetch(path: string, options: RequestInit = {}): Promise<Response> {
  const headers = new Headers(options.headers)
  headers.set('Accept', 'application/json')

  return fetch(`${API_BASE_URL}${path}`, {
    ...options,
    headers,
  })
}
