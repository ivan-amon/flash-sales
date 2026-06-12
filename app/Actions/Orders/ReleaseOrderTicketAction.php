<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ReleaseOrderTicketAction
{
    /**
     * Atomically release the ticket held by a pending order back to the
     * available pool, transitioning the order to the given final status.
     *
     * The status transition doubles as a claim: only the first caller to move
     * the order out of the pending state performs the release. This makes the
     * operation safe to run concurrently from both the payment flow and the
     * scheduled expiry sweep, and guarantees the Redis counter is incremented
     * at most once per order. The counter is only touched when it already
     * exists, since a missing key is lazily re-seeded from the live database
     * count, which already reflects the freed ticket.
     *
     * @return bool True if this call freed the ticket, false if the order was
     *              already finalised by another process or held no reserved ticket.
     */
    public function __invoke(Order $order, OrderStatus $finalStatus): bool
    {
        $eventId = DB::transaction(function () use ($order, $finalStatus): ?int {
            $claimed = Order::query()
                ->whereKey($order->getKey())
                ->where('status', OrderStatus::Pending)
                ->update(['status' => $finalStatus]) === 1;

            if (! $claimed) {
                return null;
            }

            $ticket = $order->ticket()->lockForUpdate()->first();

            if ($ticket === null || $ticket->status !== TicketStatus::Reserved) {
                return null;
            }

            $ticket->status = TicketStatus::Available;
            $ticket->save();

            return $ticket->event_id;
        });

        $order->status = $finalStatus;

        if ($eventId === null) {
            return false;
        }

        $key = "available_tickets_{$eventId}";

        if (Redis::exists($key)) {
            Redis::incr($key);
        }

        return true;
    }
}
