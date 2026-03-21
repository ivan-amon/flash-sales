<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Exceptions\Tickets\NotAvailableTicketsException;
use App\Exceptions\Tickets\TicketSalesNotStartedException;
use App\Models\Event;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CreateOrderAction
{
    public function __invoke(array $data): Order
    {
        $event = Event::findOrFail($data['event_id']);

        if ($event->sale_starts_at > now()) {
            throw new TicketSalesNotStartedException("Ticket sales for event {$event->id} have not started yet.");
        }

        // Layer 1: Redis backpressure, reject most requests before hitting MySQL
        $key = "available_tickets_{$event->id}";
        Redis::setnx($key, $event->tickets()->where('status', TicketStatus::Available)->count());

        $remaining = Redis::decr($key);

        if ($remaining < 0) {
            Redis::incr($key);
            throw new NotAvailableTicketsException("No available tickets for event {$event->id}.");
        }

        // Layer 2: Pessimistic locking, safe here because Redis already filtered 99.9% of traffic
        $ticket = DB::transaction(function () use ($event, $key) {
            $ticket = $event->tickets()
                ->where('status', TicketStatus::Available)
                ->lockForUpdate()
                ->first();

            if (!$ticket) {
                Redis::incr($key);
                throw new NotAvailableTicketsException("No available tickets for event {$event->id}.");
            }

            $ticket->status = TicketStatus::Reserved;
            $ticket->save();

            return $ticket;
        });

        $expiresAt = now()->addMinutes(5);

        return Order::create([
            'user_id' => $data['user_id'],
            'ticket_id' => $ticket->id,
            'status' => OrderStatus::Pending,
            'expires_at' => $expiresAt,
        ]);
    }
}
