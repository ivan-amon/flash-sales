export interface User {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  country_code: string | null
  created_at: string
  updated_at: string
}

export interface Organizer {
  id: number
  official_name: string
  email: string
  phone: string | null
  created_at: string
  updated_at: string
}

export type Role = 'user' | 'organizer'

export type ValidationErrors = Record<string, string[]>
