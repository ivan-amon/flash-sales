<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\Ticket;
use Carbon\Carbon;

class OrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // ==================
    // Users
    // ==================
    public function test_user_can_create_order_for_available_ticket(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['is_user']);

        $event = Event::factory()->create([
            'sale_starts_at' => now()->subHour(),
        ]);
        Ticket::factory()->count(10)->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);

        $response = $this->postJson('/api/orders', [
            'event_id' => $event->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'ticket_id',
                'status',
                'expires_at',
                'created_at',
                'updated_at',
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'ticket_id' => $response->json('ticket_id'),
            'status' => OrderStatus::Pending->value,
            'expires_at' => Carbon::parse($response->json('expires_at'))->format('Y-m-d H:i:s'),
            'created_at' => Carbon::parse($response->json('created_at'))->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($response->json('updated_at'))->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_user_cannot_create_order_for_event_not_on_sale(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['is_user']);
        $event = Event::factory()->create([
            'sale_starts_at' => now()->addHour(),
        ]);
        Ticket::factory()->count(10)->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);

        $response = $this->postJson('/api/orders', [
            'event_id' => $event->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_create_order_for_event_with_no_available_tickets(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['is_user']);

        $event = Event::factory()->create([
            'sale_starts_at' => now()->subHour(),
        ]);
        Ticket::factory()->count(10)->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Sold,
        ]);

        $response = $this->postJson('/api/orders', [
            'event_id' => $event->id,
        ]);

        $response->assertStatus(409);
    }

    public function test_user_cannot_create_order_with_invalid_data(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['is_user']);

        $response = $this->postJson('/api/orders', [
            // 'event_id' => missing
        ]); 

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['event_id']);
    }

    public function test_user_cannot_create_order_for_non_existent_event(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['is_user']);
        $response = $this->postJson('/api/orders', [
            'event_id' => 9999, // Non-existent event ID
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['event_id']);
    }

    // ==================
    // Organizers
    // ==================
    public function test_organizer_cannot_create_order(): void
    {
        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer, ['is_organizer']);

        $event = Event::factory()->create([
            'sale_starts_at' => now()->subHour(),
        ]);
        Ticket::factory()->count(10)->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);

        $response = $this->postJson('/api/orders', [
            'event_id' => $event->id,
        ]);

        $response->assertStatus(403);
    }
}
