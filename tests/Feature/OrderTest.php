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
use App\Models\Order;
use Carbon\Carbon;

class OrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // ==================
    // Users
    // ==================
    public function test_unauthenticated_user_cannot_access_orders_endpoints(): void
    {
        $this->getJson('/api/orders')->assertStatus(401);
        $this->postJson('/api/orders', [])->assertStatus(401);
        $this->getJson('/api/orders/1')->assertStatus(401);
    }
    // Create Order Tests
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

    // Read Order Tests
    public function test_user_can_view_only_their_own_orders_in_index(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'sale_starts_at' => now()->subHour(),
        ]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
        ]);

        Sanctum::actingAs($user, ['is_user']);
        $response = $this->getJson('/api/orders')->assertStatus(200);
        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertEquals($order->id, $data[0]['id']);
    }

    public function test_user_does_not_see_others_orders_in_index(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $event = Event::factory()->create([
            'sale_starts_at' => now()->subHour(),
        ]);
        $ticket1 = Ticket::factory()->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);
        $ticket2 = Ticket::factory()->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);
        $order1 = Order::factory()->create([
            'user_id' => $user1->id,
            'ticket_id' => $ticket1->id,
        ]);
        $order2 = Order::factory()->create([
            'user_id' => $user2->id,
            'ticket_id' => $ticket2->id,
        ]);

        Sanctum::actingAs($user1, ['is_user']);
        $response = $this->getJson('/api/orders')->assertStatus(200);
        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertEquals($order1->id, $data[0]['id']);
        $this->assertNotEquals($order2->id, $data[0]['id']);
    }

    public function test_user_can_view_own_order(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'sale_starts_at' => now()->subHour(),
        ]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
        ]);

        Sanctum::actingAs($user, ['is_user']);
        $response = $this->getJson("/api/orders/{$order->id}")->assertStatus(200);
        $data = $response->json();
        $this->assertEquals($order->id, $data['id']);
    }

    public function test_user_cannot_view_others_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $event = Event::factory()->create([
            'sale_starts_at' => now()->subHour(),
        ]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);
        $order = Order::factory()->create([
            'user_id' => $user2->id,
            'ticket_id' => $ticket->id,
        ]);

        Sanctum::actingAs($user1, ['is_user']);
        $this->getJson("/api/orders/{$order->id}")->assertStatus(404);
    }

    public function test_user_cannot_view_non_existent_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Sanctum::actingAs($user1, ['is_user']);
        $this->getJson('/api/orders/9999')->assertStatus(404);
    }

    public function test_user_with_no_orders_sees_empty_array_in_index(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['is_user']);
        $response = $this->getJson('/api/orders')->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertCount(0, $data);
    }

    // Todo: Add tests to check the JSON format of the responses (already checked with curl to a seeded database)

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

    public function test_organizer_cannot_view_orders(): void
    {

        $event = Event::factory()->create([
            'sale_starts_at' => now()->subHour(),
        ]);
        Ticket::factory()->count(10)->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);
        Order::factory()->count(5)->create([
            'ticket_id' => Ticket::factory()->create([
                'event_id' => $event->id,
                'status' => TicketStatus::Available,
            ])->id,
        ]);

        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer, ['is_organizer']);

        // Todo: Change the code from 403 to 404 in the controller and the policy
        $this->getJson('/api/orders')->assertStatus(403);
        $this->getJson('/api/orders/1')->assertStatus(403);
        $this->getJson('/api/orders/9999')->assertStatus(404);
    }
}
