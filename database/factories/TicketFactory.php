<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'status' => TicketStatus::Available,
        ];
    }

    public function available(): static
    {
        return $this->state(['status' => TicketStatus::Available]);
    }

    public function sold(): static
    {
        return $this->state(['status' => TicketStatus::Sold]);
    }

    public function reserved(): static
    {
        return $this->state(['status' => TicketStatus::Reserved]);
    }
}