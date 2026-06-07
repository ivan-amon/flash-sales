import { computed, readonly, ref } from 'vue'
import { apiFetch, TOKEN_STORAGE_KEY } from '../utils/http'
import type { User, ValidationErrors } from '../types/user'

const USER_STORAGE_KEY = 'auth_user'

function loadStoredUser(): User | null {
  const raw = localStorage.getItem(USER_STORAGE_KEY)
  if (!raw) {
    return null
  }

  try {
    return JSON.parse(raw) as User
  } catch {
    return null
  }
}

const token = ref<string | null>(localStorage.getItem(TOKEN_STORAGE_KEY))
const user = ref<User | null>(loadStoredUser())
const isAuthenticated = computed<boolean>(() => token.value !== null)

function setSession(nextUser: User, nextToken: string): void {
  token.value = nextToken
  user.value = nextUser
  localStorage.setItem(TOKEN_STORAGE_KEY, nextToken)
  localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(nextUser))
}

function clearSession(): void {
  token.value = null
  user.value = null
  localStorage.removeItem(TOKEN_STORAGE_KEY)
  localStorage.removeItem(USER_STORAGE_KEY)
}

type AuthResult = { ok: true } | { ok: false; errors: ValidationErrors }

interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
}

async function login(email: string, password: string): Promise<AuthResult> {
  const response = await apiFetch('/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
  })

  if (response.ok) {
    const data = (await response.json()) as { user: User; token: string }
    setSession(data.user, data.token)
    return { ok: true }
  }

  if (response.status === 422) {
    const data = (await response.json()) as { errors: ValidationErrors }
    return { ok: false, errors: data.errors }
  }

  return { ok: false, errors: { email: ['Login failed. Please try again.'] } }
}

async function register(payload: RegisterPayload): Promise<AuthResult> {
  const response = await apiFetch('/register', {
    method: 'POST',
    body: JSON.stringify(payload),
  })

  if (response.ok) {
    const data = (await response.json()) as { user: User; token: string }
    setSession(data.user, data.token)
    return { ok: true }
  }

  if (response.status === 422) {
    const data = (await response.json()) as { errors: ValidationErrors }
    return { ok: false, errors: data.errors }
  }

  return { ok: false, errors: { email: ['Registration failed. Please try again.'] } }
}

async function logout(): Promise<void> {
  try {
    await apiFetch('/logout', { method: 'POST' })
  } catch {
    // Best-effort: the token may already be invalid; clear the session regardless.
  } finally {
    clearSession()
  }
}

export function useAuth() {
  return {
    user: readonly(user),
    isAuthenticated,
    login,
    register,
    logout,
  }
}
