export interface Country {
  id: number
  name: string
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
