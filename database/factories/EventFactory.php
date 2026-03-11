<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
            'total_tickets' => fake()->numberBetween(10, 500),
            'organizer_id' => Organizer::factory(),
            'sale_starts_at' => fake()->optional()->dateTimeBetween('-1 month', '+2 months'),
        ];
    }
}
