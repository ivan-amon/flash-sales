<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Event;
use App\Enums\TicketStatus;
use App\Models\Order;

class CreateOrderAction
{
    public function __invoke(array $data): Order
    {
        $event = Event::findOrFail($data['event_id']);

        if ($event->sale_starts_at > now()) {
            abort(403, 'Ticket sales have not started for this event.');
        }

        $ticket = $event->tickets()->where('status', TicketStatus::Available)->first();
        if (!$ticket) {
            abort(409, 'No available tickets for this event.');
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