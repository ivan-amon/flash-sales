export interface EventItem {
  id: number
  title: string
  total_tickets: number
  organizer_id: number
  sale_starts_at: string | null
  created_at: string
  updated_at: string
  available_tickets: number
}
