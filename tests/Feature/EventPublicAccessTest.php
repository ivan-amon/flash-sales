<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventPublicAccessTest extends TestCase
{
    use RefreshDatabase;

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

    // ==========================================
    // AUTHENTICATED USER TESTS
    // ==========================================

    public function test_authenticated_user_can_list_events()
    {

        $organizer = Organizer::factory()->create();
        Event::factory()->count(2)->create(['organizer_id' => $organizer->id]);
        $user = User::factory()->create();
        // Simulate a regular user
        Sanctum::actingAs($user, ['is_user']);

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_authenticated_organizer_can_list_events()
    {

        $organizer = Organizer::factory()->create();
        Event::factory()->count(2)->create(['organizer_id' => $organizer->id]);
        // Simulate an organizer
        Sanctum::actingAs($organizer, ['is_organizer']);

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }
}