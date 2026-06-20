import { computed, readonly, ref } from 'vue'
import { apiFetch, TOKEN_STORAGE_KEY } from '@/shared/api/http'
import type { Organizer, Role, User, ValidationErrors } from '@/features/auth/types/user'

const USER_STORAGE_KEY = 'auth_user'
const ROLE_STORAGE_KEY = 'auth_role'

function loadStored<T>(key: string): T | null {
  const raw = localStorage.getItem(key)
  if (!raw) {
    return null
  }

  try {
    return JSON.parse(raw) as T
  } catch {
    return null
  }
}

const token = ref<string | null>(localStorage.getItem(TOKEN_STORAGE_KEY))
const role = ref<Role | null>(localStorage.getItem(ROLE_STORAGE_KEY) as Role | null)
const user = ref<User | null>(role.value === 'user' ? loadStored<User>(USER_STORAGE_KEY) : null)
const organizer = ref<Organizer | null>(
  role.value === 'organizer' ? loadStored<Organizer>(USER_STORAGE_KEY) : null,
)

const isAuthenticated = computed<boolean>(() => token.value !== null)
const isOrganizer = computed<boolean>(() => role.value === 'organizer')
const isUser = computed<boolean>(() => role.value === 'user')
const isEmailVerified = computed<boolean>(() => user.value?.email_verified_at != null)
const isOrganizerEmailVerified = computed<boolean>(
  () => organizer.value?.email_verified_at != null,
)

function persistSession(principal: User | Organizer, nextToken: string, nextRole: Role): void {
  token.value = nextToken
  role.value = nextRole
  localStorage.setItem(TOKEN_STORAGE_KEY, nextToken)
  localStorage.setItem(ROLE_STORAGE_KEY, nextRole)
  localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(principal))
}

function setUserSession(nextUser: User, nextToken: string): void {
  user.value = nextUser
  organizer.value = null
  persistSession(nextUser, nextToken, 'user')
}

function setOrganizerSession(nextOrganizer: Organizer, nextToken: string): void {
  organizer.value = nextOrganizer
  user.value = null
  persistSession(nextOrganizer, nextToken, 'organizer')
}

function clearSession(): void {
  token.value = null
  role.value = null
  user.value = null
  organizer.value = null
  localStorage.removeItem(TOKEN_STORAGE_KEY)
  localStorage.removeItem(ROLE_STORAGE_KEY)
  localStorage.removeItem(USER_STORAGE_KEY)
}

type AuthResult = { ok: true } | { ok: false; errors: ValidationErrors }

interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
}

interface OrganizerRegisterPayload {
  official_name: string
  phone: string | null
  email: string
  password: string
  password_confirmation: string
}

async function postAuth<T>(
  path: string,
  body: object,
  onSuccess: (data: T) => void,
  fallbackMessage: string,
): Promise<AuthResult> {
  const response = await apiFetch(path, {
    method: 'POST',
    body: JSON.stringify(body),
  })

  if (response.ok) {
    onSuccess((await response.json()) as T)
    return { ok: true }
  }

  if (response.status === 422) {
    const data = (await response.json()) as { errors: ValidationErrors }
    return { ok: false, errors: data.errors }
  }

  return { ok: false, errors: { email: [fallbackMessage] } }
}

async function login(email: string, password: string): Promise<AuthResult> {
  return postAuth(
    '/login',
    { email, password },
    (data: { user: User; token: string }) => setUserSession(data.user, data.token),
    'Login failed. Please try again.',
  )
}

async function register(payload: RegisterPayload): Promise<AuthResult> {
  return postAuth(
    '/register',
    payload,
    (data: { user: User; token: string }) => setUserSession(data.user, data.token),
    'Registration failed. Please try again.',
  )
}

async function organizerLogin(email: string, password: string): Promise<AuthResult> {
  return postAuth(
    '/organizer/login',
    { email, password },
    (data: { organizer: Organizer; token: string }) =>
      setOrganizerSession(data.organizer, data.token),
    'Login failed. Please try again.',
  )
}

async function organizerRegister(payload: OrganizerRegisterPayload): Promise<AuthResult> {
  return postAuth(
    '/organizer/register',
    payload,
    (data: { organizer: Organizer; token: string }) =>
      setOrganizerSession(data.organizer, data.token),
    'Registration failed. Please try again.',
  )
}

interface ResetPasswordPayload {
  token: string
  email: string
  password: string
  password_confirmation: string
}

async function forgotPassword(email: string): Promise<AuthResult> {
  return postAuth(
    '/password/forgot',
    { email },
    () => {},
    'Could not send the reset link. Please try again.',
  )
}

async function resetPassword(payload: ResetPasswordPayload): Promise<AuthResult> {
  return postAuth(
    '/password/reset',
    payload,
    (data: { user: User; token: string }) => setUserSession(data.user, data.token),
    'Could not reset the password. Please try again.',
  )
}

async function organizerForgotPassword(email: string): Promise<AuthResult> {
  return postAuth(
    '/organizer/password/forgot',
    { email },
    () => {},
    'Could not send the reset link. Please try again.',
  )
}

async function organizerResetPassword(payload: ResetPasswordPayload): Promise<AuthResult> {
  return postAuth(
    '/organizer/password/reset',
    payload,
    (data: { organizer: Organizer; token: string }) =>
      setOrganizerSession(data.organizer, data.token),
    'Could not reset the password. Please try again.',
  )
}

async function updateCountry(code: string): Promise<boolean> {
  const response = await apiFetch('/user/country', {
    method: 'PATCH',
    body: JSON.stringify({ country_code: code }),
  })

  if (!response.ok) {
    return false
  }

  const updated = (await response.json()) as User
  user.value = updated
  localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(updated))

  return true
}

async function refreshUser(): Promise<User | null> {
  if (role.value !== 'user') {
    return null
  }

  const response = await apiFetch('/user')

  if (!response.ok) {
    return null
  }

  const updated = (await response.json()) as User
  user.value = updated
  localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(updated))

  return updated
}

async function resendVerificationEmail(): Promise<boolean> {
  const response = await apiFetch('/email/verification-notification', {
    method: 'POST',
  })

  return response.ok
}

async function refreshOrganizer(): Promise<Organizer | null> {
  if (role.value !== 'organizer') {
    return null
  }

  const response = await apiFetch('/organizer')

  if (!response.ok) {
    return null
  }

  const updated = (await response.json()) as Organizer
  organizer.value = updated
  localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(updated))

  return updated
}

async function resendOrganizerVerificationEmail(): Promise<boolean> {
  const response = await apiFetch('/organizer/email/verification-notification', {
    method: 'POST',
  })

  return response.ok
}

async function logout(): Promise<void> {
  const path = role.value === 'organizer' ? '/organizer/logout' : '/logout'

  try {
    await apiFetch(path, { method: 'POST' })
  } catch {
    // Best-effort: the token may already be invalid; clear the session regardless.
  } finally {
    clearSession()
  }
}

export function useAuth() {
  return {
    user: readonly(user),
    organizer: readonly(organizer),
    role: readonly(role),
    isAuthenticated,
    isOrganizer,
    isUser,
    isEmailVerified,
    isOrganizerEmailVerified,
    login,
    register,
    organizerLogin,
    organizerRegister,
    forgotPassword,
    resetPassword,
    organizerForgotPassword,
    organizerResetPassword,
    updateCountry,
    refreshUser,
    refreshOrganizer,
    resendVerificationEmail,
    resendOrganizerVerificationEmail,
    logout,
  }
}
