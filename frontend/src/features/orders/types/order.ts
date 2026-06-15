export type OrderStatus = 'pending' | 'confirmed' | 'cancelled' | 'expired'

export interface Order {
  id: number
  user_id: number
  ticket_id: number
  amount: number
  status: OrderStatus
  expires_at: string
  created_at: string
  updated_at: string
}

export type TicketStatus = 'available' | 'reserved' | 'sold'

export interface OrderEvent {
  id: number
  title: string
  description: string | null
  total_tickets: number
  organizer_id: number
  city_id: number
  sale_starts_at: string | null
  event_starts_at: string
  cover_image_path: string | null
  cover_image_url: string | null
  created_at: string
  updated_at: string
}

export interface OrderTicket {
  id: number
  event_id: number
  status: TicketStatus
  price: number
  created_at: string
  updated_at: string
  event: OrderEvent
}

export interface OrderWithTicket extends Order {
  ticket: OrderTicket
}
