<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventTests extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    // ==================
    // AUTH
    // ==================

    public function test_guest_cannot_access_events(): void
    {
        // Override the setUp authentication
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/events')->assertStatus(401);
        $this->postJson('/api/events')->assertStatus(401);
        $this->getJson('/api/events/1')->assertStatus(401);
        $this->putJson('/api/events/1')->assertStatus(401);
        $this->deleteJson('/api/events/1')->assertStatus(401);
    }

    // ==================
    // INDEX
    // ==================

    public function test_can_list_events(): void
    {
        Event::factory()->count(3)->create();

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_list_events_returns_empty_when_none_exist(): void
    {
        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    // ==================
    // STORE
    // ==================

    public function test_can_create_event(): void
    {
        $response = $this->postJson('/api/events', [
            'title' => 'Laravel Conference',
            'total_tickets' => 100,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'title', 'total_tickets']);

        $this->assertDatabaseHas('events', [
            'title' => 'Laravel Conference',
            'total_tickets' => 100,
        ]);
    }

    public function test_create_event_requires_title(): void
    {
        $response = $this->postJson('/api/events', [
            'total_tickets' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_create_event_requires_total_tickets(): void
    {
        $response = $this->postJson('/api/events', [
            'title' => 'Laravel Conference',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total_tickets']);
    }

    public function test_create_event_title_must_be_unique(): void
    {
        Event::factory()->create(['title' => 'Laravel Conference']);

        $response = $this->postJson('/api/events', [
            'title' => 'Laravel Conference',
            'total_tickets' => 50,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_create_event_total_tickets_must_be_at_least_one(): void
    {
        $response = $this->postJson('/api/events', [
            'title' => 'Laravel Conference',
            'total_tickets' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total_tickets']);
    }

    public function test_create_event_total_tickets_must_be_integer(): void
    {
        $response = $this->postJson('/api/events', [
            'title' => 'Laravel Conference',
            'total_tickets' => 'not-a-number',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total_tickets']);
    }

    // ==================
    // SHOW
    // ==================

    public function test_can_show_event(): void
    {
        $event = Event::factory()->create();

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $event->id,
                'title' => $event->title,
                'total_tickets' => $event->total_tickets,
            ]);
    }

    public function test_show_returns_404_for_nonexistent_event(): void
    {
        $response = $this->getJson('/api/events/999');

        $response->assertStatus(404);
    }

    // ==================
    // UPDATE
    // ==================

    public function test_can_update_event_title(): void
    {
        $event = Event::factory()->create();

        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJson(['title' => 'Updated Title']);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_can_update_event_total_tickets(): void
    {
        $event = Event::factory()->create();

        $response = $this->putJson("/api/events/{$event->id}", [
            'total_tickets' => 200,
        ]);

        $response->assertStatus(200)
            ->assertJson(['total_tickets' => 200]);
    }

    public function test_update_event_title_must_be_unique(): void
    {
        Event::factory()->create(['title' => 'Existing Event']);
        $event = Event::factory()->create();

        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'Existing Event',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_update_event_allows_keeping_same_title(): void
    {
        $event = Event::factory()->create(['title' => 'My Event']);

        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'My Event',
        ]);

        $response->assertStatus(200);
    }

    public function test_update_returns_404_for_nonexistent_event(): void
    {
        $response = $this->putJson('/api/events/999', [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(404);
    }

    // ==================
    // DESTROY
    // ==================

    public function test_can_delete_event(): void
    {
        $event = Event::factory()->create();

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);
    }

    public function test_delete_returns_404_for_nonexistent_event(): void
    {
        $response = $this->deleteJson('/api/events/999');

        $response->assertStatus(404);
    }
}
