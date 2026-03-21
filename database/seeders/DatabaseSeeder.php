<?php

namespace Database\Seeders;

use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Redis;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (range(1, 200) as $n) {
            User::factory()->create([
                'name' => "Test User $n",
                'email' => "test{$n}@email.com",
                'password' => bcrypt('test1234'),
            ]);
        }

        $event = Event::factory()->create([
            'title' => 'Evento de prueba',
            'total_tickets' => 5,
            'organizer_id' => Organizer::factory()->create()->id,
            'sale_starts_at' => now()->subDay(),
        ]);

        Ticket::factory()->count(5)->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);

        Redis::set("available_tickets_{$event->id}", 5);
    }
}
