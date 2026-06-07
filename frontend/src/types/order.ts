export type OrderStatus = 'pending' | 'confirmed' | 'cancelled' | 'expired'

export interface Order {
  id: number
  user_id: number
  ticket_id: number
  status: OrderStatus
  expires_at: string
  created_at: string
  updated_at: string
}

export type TicketStatus = 'available' | 'reserved' | 'sold'

export interface OrderEvent {
  id: number
  title: string
  total_tickets: number
  organizer_id: number
  sale_starts_at: string | null
  created_at: string
  updated_at: string
}

export interface OrderTicket {
  id: number
  event_id: number
  status: TicketStatus
  created_at: string
  updated_at: string
  event: OrderEvent
}

export interface OrderWithTicket extends Order {
  ticket: OrderTicket
}
