<?php

namespace Database\Seeders;

use App\Enums\TicketStatus;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $soldTickets = Ticket::where('status', TicketStatus::Sold)->get();

        $soldTickets->each(function (Ticket $ticket) use ($users) {
            Order::factory()->create([
                'user_id' => $users->random()->id,
                'ticket_id' => $ticket->id,
            ]);
        });
    }
}
