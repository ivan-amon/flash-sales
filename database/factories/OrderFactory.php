<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ticket_id' => Ticket::factory()->reserved(),
            'amount' => fn (array $attributes): int => Ticket::find($attributes['ticket_id'])?->price ?? fake()->numberBetween(1000, 50000),
            'status' => 'pending',
            'expires_at' => now()->addMinutes(5),
        ];
    }
}
