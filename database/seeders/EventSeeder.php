<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizers = \App\Models\Organizer::all();
        $eventData = [
            ['title' => 'Summer Music Festival', 'total_tickets' => 500],
            ['title' => 'Tech Conference 2026', 'total_tickets' => 200],
            ['title' => 'Comedy Night Special', 'total_tickets' => 100],
            ['title' => 'Art Gallery Opening', 'total_tickets' => 50],
            ['title' => 'Food & Wine Expo', 'total_tickets' => 300],
        ];

        foreach ($eventData as $data) {
            Event::factory()->create(array_merge($data, [
                'organizer_id' => $organizers->random()->id,
            ]));
        }
    }
}
