<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    // ==================================
    // Organizer Permissions
    // ==================================
    public function test_organizer_can_create_event(): void
    {

        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer, ['is_organizer']);

        $eventData = [
            'title' => 'Sample Event',
            'total_tickets' => 100,
            'organizer_id' => 1,
            'sale_starts_at' => now()->addDays(7),
        ];

        $response = $this->post('/api/events', $eventData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('events', [
            'title' => 'Sample Event',
            'total_tickets' => 100,
            'organizer_id' => 1,
            'sale_starts_at' => now()->addDays(7),
        ]);
        $this->assertDatabaseCount('tickets', 100);
    }

    public function test_organizer_can_update_own_event()
    {
        $organizer = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);

        Sanctum::actingAs($organizer, ['is_organizer']);

        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'Updated Event',
        ]);

        $response->assertStatus(200)
            ->assertJson(['title' => 'Updated Event']);
    }

    public function test_organizer_can_delete_own_event()
    {
        $organizer = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);

        Sanctum::actingAs($organizer, ['is_organizer']);

        $response = $this->deleteJson("/api/events/{$event->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_organizer_cannot_update_other_organizer_event()
    {
        $organizer1 = Organizer::factory()->create();
        $organizer2 = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer2->id]);

        Sanctum::actingAs($organizer1, ['is_organizer']);

        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_organizer_cannot_delete_other_organizer_event()
    {
        $organizer1 = Organizer::factory()->create();
        $organizer2 = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer2->id]);

        Sanctum::actingAs($organizer1, ['is_organizer']);

        $response = $this->deleteJson("/api/events/{$event->id}");
        $response->assertStatus(403);
    }

    // ==================================
    // User Permissions
    // ==================================
    public function test_user_cannot_create_event()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['is_user']);

        $response = $this->postJson('/api/events', [
            'title' => 'User Event',
            'total_tickets' => 50,
            'organizer_id' => 1,
            'sale_starts_at' => now()->addDays(7),
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('events', ['title' => 'User Event']);
    }

    public function test_user_cannot_update_event()
    {
        $user = User::factory()->create();
        $organizer = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);

        Sanctum::actingAs($user, ['is_user']);

        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'User Update',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_event()
    {
        $user = User::factory()->create();
        $organizer = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);

        Sanctum::actingAs($user, ['is_user']);

        $response = $this->deleteJson("/api/events/{$event->id}");
        $response->assertStatus(403);
    }

    // ==========================================
    // GUEST TESTS (No token)
    // ==========================================

    public function test_guest_can_list_all_events()
    {
        // Create an organizer and 3 events linked to it
        $organizer = Organizer::factory()->create();
        Event::factory()->count(3)->create(['organizer_id' => $organizer->id]);

        // Make an unauthenticated GET request
        $response = $this->getJson('/api/events');

        // Verify it returns a 200 OK and contains 3 items
        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_guest_can_view_specific_event_by_id()
    {

        $organizer = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $event->id,
                'title' => $event->title,
                'total_tickets' => $event->total_tickets,
            ]);
    }

    public function test_returns_404_when_searching_for_nonexistent_event()
    {
        // Request an ID that we know does not exist
        $response = $this->getJson('/api/events/999999');

        $response->assertStatus(404);
    }
}
