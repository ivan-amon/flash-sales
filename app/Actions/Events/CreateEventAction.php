<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Exception;

class CreateEventAction
{
    /**
     * Creates a new event and generates the associated tickets.
     *
     * @param array $data Must contain 'title', 'total_tickets', 'organizer_id', and 'sale_starts_at'.
     * @return Event
     */
    public function __invoke($data): Event
    {
        DB::beginTransaction();

        try {
            $event = Event::create([
                'title' => $data['title'],
                'total_tickets' => $data['total_tickets'],
                'organizer_id' => $data['organizer_id'],
                'sale_starts_at' => $data['sale_starts_at'],
            ]);

            $tickets = [];
            for ($i = 0; $i < $data['total_tickets']; $i++) {
                $tickets[] = [
                    'event_id' => $event->id,
                    'status' => 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Ticket::insert($tickets);
            DB::commit();
            return $event;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
