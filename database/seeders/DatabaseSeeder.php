<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TicketStatus;
use App\Models\City;
use App\Models\Country;
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
        $this->call(CountryCitySeeder::class);

        $countryCodes = Country::query()->pluck('iso_code');

        foreach (range(1, 200) as $n) {
            User::factory()->create([
                'name' => "Test User $n",
                'email' => "test{$n}@email.com",
                'password' => bcrypt('test1234'),
                'country_code' => $countryCodes->random(),
            ]);
        }

        $event = Event::factory()->create([
            'title' => 'Test Event',
            'total_tickets' => 5,
            'organizer_id' => Organizer::factory()->create()->id,
            'city_id' => City::where('name', 'Madrid')->value('id'),
            'sale_starts_at' => now()->subDay(),
        ]);

        Ticket::factory()->count(5)->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);

        Redis::set("available_tickets_{$event->id}", 5);

        $this->seedSampleEvents();
    }

    /**
     * Seed between 3 and 10 events for every country across several organizers,
     * each placed in one of the country's cities with a mix of available and
     * sold tickets, so every country has events to list.
     */
    private function seedSampleEvents(): void
    {
        $organizerIds = Organizer::factory()->count(5)->create()->pluck('id');

        Country::query()->each(function (Country $country) use ($organizerIds): void {
            $cityIds = City::query()->where('country_code', $country->iso_code)->pluck('id');

            if ($cityIds->isEmpty()) {
                return;
            }

            foreach (range(1, fake()->numberBetween(3, 10)) as $n) {
                $event = Event::factory()->create([
                    'organizer_id' => $organizerIds->random(),
                    'city_id' => $cityIds->random(),
                ]);

                $availableCount = fake()->numberBetween(0, $event->total_tickets);
                $soldCount = $event->total_tickets - $availableCount;

                if ($availableCount > 0) {
                    Ticket::factory()->count($availableCount)->available()->create(['event_id' => $event->id]);
                }

                if ($soldCount > 0) {
                    Ticket::factory()->count($soldCount)->sold()->create(['event_id' => $event->id]);
                }

                Redis::set("available_tickets_{$event->id}", $availableCount);
            }
        });
    }
}
