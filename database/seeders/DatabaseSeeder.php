<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a known test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create additional random users
        User::factory(9)->create();

        // Seed in dependency order: Events → Tickets → Orders
        $this->call([
            EventSeeder::class,
            TicketSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
