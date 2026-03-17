<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Event;
use App\Enums\TicketStatus;

use App\Exceptions\Tickets\NotAvailableTicketsException;
use App\Exceptions\Tickets\TicketSalesNotStartedException;
use App\Models\Order;

class CreateOrderAction
{
    public function __invoke(array $data): Order
    {
        $event = Event::findOrFail($data['event_id']);

        if ($event->sale_starts_at > now()) {
            throw new  TicketSalesNotStartedException("Ticket sales for event {$event->id} have not started yet.");
        }

        $ticket = $event->tickets()->where('status', TicketStatus::Available)->first();
        if (!$ticket) {
            throw new NotAvailableTicketsException("No available tickets for event {$event->id}.");
        }

        $ticket->status = TicketStatus::Reserved;
        $ticket->save();

        $expiresAt = now()->addMinutes(5);
        return Order::create([
            'user_id' => $data['user_id'],
            'ticket_id' => $ticket->id,
            'status' => OrderStatus::Pending,
            'expires_at' => $expiresAt,
        ]);
    }
}
