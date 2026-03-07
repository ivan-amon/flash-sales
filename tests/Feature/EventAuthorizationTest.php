<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_can_create_event()
    {
        $organizer = Organizer::factory()->create();
        
        Sanctum::actingAs($organizer, ['is_organizer']);

        $response = $this->postJson('/api/events', [
            'title' => 'Organizer Event',
            'total_tickets' => 100,
        ]);

        $response->assertStatus(201)
            ->assertJson(['title' => 'Organizer Event', 'total_tickets' => 100]);
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

    public function test_user_cannot_create_event()
    {
        $user = User::factory()->create();
        
        Sanctum::actingAs($user, ['is_user']);

        $response = $this->postJson('/api/events', [
            'title' => 'User Event',
            'total_tickets' => 50,
        ]);

        $response->assertStatus(403);
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
}