<?php 

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\Ticket;

class AvailableTicketsAction
{
    /**
     * Retrieves the number of available tickets for a given event.
     *
     * @param Event $event The event for which to count available tickets.
     * @return int The count of available tickets.
     */
    public function __invoke(Event $event): int
    {
        return Ticket::where('event_id', $event->id)
                     ->where('status', 'available')
                     ->count();
    }
}