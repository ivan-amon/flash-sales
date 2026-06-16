export interface Country {
  id: number
  name: string
  iso_code: string
  created_at: string
  updated_at: string
}

export interface City {
  id: number
  country_id: number
  name: string
  created_at: string
  updated_at: string
  country?: Country
}

export interface Paginated<T> {
  current_page: number
  data: T[]
  last_page: number
  next_page_url: string | null
  prev_page_url: string | null
  per_page: number
  total: number
  from: number | null
  to: number | null
}

export interface EventItem {
  id: number
  title: string
  total_tickets: number
  organizer_id: number
  city_id: number
  sale_starts_at: string
  event_starts_at: string
  cover_image_path: string | null
  cover_image_url: string | null
  city?: City
  created_at: string
  updated_at: string
  available_tickets: number
}
