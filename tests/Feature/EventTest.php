<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\City;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        Sanctum::actingAs($organizer);

        $city = City::factory()->create();

        $eventData = [
            'title' => 'Sample Event',
            'total_tickets' => 100,
            'organizer_id' => 1,
            'city_id' => $city->id,
            'sale_starts_at' => now()->addDays(7),
            'event_starts_at' => now()->addDays(30),
        ];

        $response = $this->post('/api/events', $eventData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('events', [
            'title' => 'Sample Event',
            'total_tickets' => 100,
            'organizer_id' => 1,
            'city_id' => $city->id,
            'sale_starts_at' => now()->addDays(7),
            'event_starts_at' => now()->addDays(30),
        ]);
        $this->assertDatabaseCount('tickets', 100);
    }

    // ==================================
    // Event Location (City)
    // ==================================
    public function test_organizer_cannot_create_event_without_city(): void
    {
        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer);

        $response = $this->postJson('/api/events', [
            'title' => 'Cityless Event',
            'total_tickets' => 100,
            'sale_starts_at' => now()->addDays(7),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('city_id');
        $this->assertDatabaseMissing('events', ['title' => 'Cityless Event']);
    }

    public function test_organizer_cannot_create_event_with_nonexistent_city(): void
    {
        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer);

        $response = $this->postJson('/api/events', [
            'title' => 'Bad City Event',
            'total_tickets' => 100,
            'city_id' => 999999,
            'sale_starts_at' => now()->addDays(7),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('city_id');
        $this->assertDatabaseMissing('events', ['title' => 'Bad City Event']);
    }

    public function test_organizer_cannot_create_event_without_sale_starts_at(): void
    {
        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer);

        $city = City::factory()->create();

        $response = $this->postJson('/api/events', [
            'title' => 'No Sale Date Event',
            'total_tickets' => 100,
            'city_id' => $city->id,
            'event_starts_at' => now()->addDays(30),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('sale_starts_at');
        $this->assertDatabaseMissing('events', ['title' => 'No Sale Date Event']);
    }

    // ==================================
    // Event Start Date
    // ==================================
    public function test_organizer_cannot_create_event_without_event_starts_at(): void
    {
        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer);

        $city = City::factory()->create();

        $response = $this->postJson('/api/events', [
            'title' => 'Dateless Event',
            'total_tickets' => 100,
            'city_id' => $city->id,
            'sale_starts_at' => now()->addDays(7),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('event_starts_at');
        $this->assertDatabaseMissing('events', ['title' => 'Dateless Event']);
    }

    public function test_event_show_nests_city_and_country(): void
    {
        $organizer = Organizer::factory()->create();
        $city = City::factory()->create();
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'city_id' => $city->id,
        ]);

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('city.id', $city->id);
        $response->assertJsonPath('city.country.id', $city->country_id);
        $response->assertJsonStructure([
            'city' => [
                'id',
                'name',
                'country' => [
                    'id',
                    'name',
                ],
            ],
        ]);
    }

    public function test_organizer_can_update_own_event()
    {
        $organizer = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);

        Sanctum::actingAs($organizer);

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

        Sanctum::actingAs($organizer);

        $response = $this->deleteJson("/api/events/{$event->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_organizer_cannot_update_other_organizer_event()
    {
        $organizer1 = Organizer::factory()->create();
        $organizer2 = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer2->id]);

        Sanctum::actingAs($organizer1);

        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(404);
    }

    public function test_organizer_cannot_delete_other_organizer_event()
    {
        $organizer1 = Organizer::factory()->create();
        $organizer2 = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer2->id]);

        Sanctum::actingAs($organizer1);

        $response = $this->deleteJson("/api/events/{$event->id}");
        $response->assertStatus(404);
    }

    // ==================================
    // User Permissions
    // ==================================
    public function test_user_cannot_create_event()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

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

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'User Update',
        ]);

        $response->assertStatus(404);
    }

    public function test_user_cannot_delete_event()
    {
        $user = User::factory()->create();
        $organizer = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/events/{$event->id}");
        $response->assertStatus(404);
    }

    // ==========================================
    // GUEST TESTS (No token)
    // ==========================================

    public function test_guest_can_list_all_events()
    {
        // Create an organizer and 3 events linked to it
        $organizer = Organizer::factory()->create();
        $events = Event::factory()->count(3)->create(['organizer_id' => $organizer->id]);

        // For each event, create tickets: some available, some sold
        foreach ($events as $event) {
            // 8 available, 2 sold
            Ticket::factory()->count(8)->create([
                'event_id' => $event->id,
                'status' => TicketStatus::Available,
            ]);
            Ticket::factory()->count(2)->sold()->create([
                'event_id' => $event->id,
                'status' => TicketStatus::Sold,
            ]);
        }

        $response = $this->getJson('/api/events');

        $response->assertStatus(200);
        $response->assertJsonCount(3); // 3 events
        foreach ($response->json() as $event) {
            $this->assertEquals($event['available_tickets'], 8);
        }
    }

    public function test_guest_can_view_specific_event_by_id()
    {

        $organizer = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);
        // Create tickets for the event
        Ticket::factory()->count(8)->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);
        // Create some sold tickets as well
        Ticket::factory()->count(2)->sold()->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Sold,
        ]);

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $event->id,
                'title' => $event->title,
                'total_tickets' => $event->total_tickets,
            ]);
        $response->assertJsonStructure([
            'id',
            'title',
            'total_tickets',
            'available_tickets',
        ]);
        $this->assertEquals($response->json()['available_tickets'], 8);
    }

    public function test_returns_404_when_searching_for_nonexistent_event()
    {
        // Request an ID that we know does not exist
        $response = $this->getJson('/api/events/999999');

        $response->assertStatus(404);
    }

    // ==================================
    // Cover Image
    // ==================================
    public function test_organizer_can_create_event_with_cover_image(): void
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::fake('public');

        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer);
        $city = City::factory()->create();

        $response = $this->postJson('/api/events', [
            'title' => 'Event With Cover',
            'total_tickets' => 50,
            'city_id' => $city->id,
            'sale_starts_at' => now()->addDays(7),
            'event_starts_at' => now()->addDays(30),
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        ]);

        $response->assertStatus(201);
        $path = $response->json('cover_image_path');
        $this->assertNotNull($path);
        $disk->assertExists($path);
        $response->assertJsonPath('cover_image_url', asset('storage/'.$path));
    }

    public function test_event_cover_image_must_be_a_valid_image(): void
    {
        Storage::fake('public');

        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer);
        $city = City::factory()->create();

        $response = $this->postJson('/api/events', [
            'title' => 'Bad Cover Event',
            'total_tickets' => 50,
            'city_id' => $city->id,
            'sale_starts_at' => now()->addDays(7),
            'event_starts_at' => now()->addDays(30),
            'cover_image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('cover_image');
    }

    public function test_event_cover_image_cannot_exceed_max_size(): void
    {
        Storage::fake('public');

        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer);
        $city = City::factory()->create();

        $response = $this->postJson('/api/events', [
            'title' => 'Oversized Cover Event',
            'total_tickets' => 50,
            'city_id' => $city->id,
            'sale_starts_at' => now()->addDays(7),
            'event_starts_at' => now()->addDays(30),
            'cover_image' => UploadedFile::fake()->image('huge.jpg')->size(3000),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('cover_image');
    }

    public function test_updating_cover_image_deletes_the_old_file(): void
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::fake('public');

        $organizer = Organizer::factory()->create();
        $oldPath = UploadedFile::fake()->image('old.jpg')->store('events', 'public');
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'cover_image_path' => $oldPath,
        ]);

        Sanctum::actingAs($organizer);

        $response = $this->putJson("/api/events/{$event->id}", [
            'cover_image' => UploadedFile::fake()->image('new.jpg'),
        ]);

        $response->assertStatus(200);
        $disk->assertMissing($oldPath);
        $newPath = $response->json('cover_image_path');
        $this->assertNotEquals($oldPath, $newPath);
        $disk->assertExists($newPath);
    }

    public function test_deleting_event_removes_its_cover_image(): void
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::fake('public');

        $organizer = Organizer::factory()->create();
        $path = UploadedFile::fake()->image('cover.jpg')->store('events', 'public');
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'cover_image_path' => $path,
        ]);

        Sanctum::actingAs($organizer);

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(204);
        $disk->assertMissing($path);
    }
}
