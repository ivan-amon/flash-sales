<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\Ticket;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CreateEventAction
{
    /**
     * Creates a new event and generates the associated tickets.
     *
     * @param  array  $data  Must contain 'title', 'total_tickets', 'price', 'organizer_id', 'city_id', 'sale_starts_at', and 'event_starts_at'. May contain 'description' and 'cover_image_path'.
     */
    public function __invoke($data): Event
    {
        DB::beginTransaction();

        try {
            $event = Event::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'total_tickets' => $data['total_tickets'],
                'organizer_id' => $data['organizer_id'],
                'city_id' => $data['city_id'],
                'sale_starts_at' => $data['sale_starts_at'],
                'event_starts_at' => $data['event_starts_at'],
                'cover_image_path' => $data['cover_image_path'] ?? null,
            ]);

            $tickets = [];
            for ($i = 0; $i < $data['total_tickets']; $i++) {
                $tickets[] = [
                    'event_id' => $event->id,
                    'status' => 'available',
                    'price' => $data['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Ticket::insert($tickets);
            DB::commit();

            // Store available tickets in Redis for quick access during sales
            $key = "available_tickets_{$event->id}";
            Redis::set($key, $data['total_tickets']);

            return $event;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
