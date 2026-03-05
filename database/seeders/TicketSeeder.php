<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::all()->each(function (Event $event) {
            // Create tickets up to 80% of total_tickets capacity
            $count = (int) floor($event->total_tickets * 0.8);

            // 70% available, 20% sold, 10% reserved
            $available = (int) floor($count * 0.7);
            $sold = (int) floor($count * 0.2);
            $reserved = $count - $available - $sold;

            Ticket::factory()
                ->count($available)
                ->for($event)
                ->create();

            Ticket::factory()
                ->count($sold)
                ->sold()
                ->for($event)
                ->create();

            Ticket::factory()
                ->count($reserved)
                ->reserved()
                ->for($event)
                ->create();
        });
    }
}
