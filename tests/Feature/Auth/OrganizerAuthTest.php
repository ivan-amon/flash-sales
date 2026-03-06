<?php

namespace Tests\Feature\Auth;

use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrganizerAuthTest extends TestCase
{
    use RefreshDatabase;

    // Todo: Add tests for phone validation

    // ==================
    // REGISTER
    // ==================

    public function test_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/organizer/register', [
            'official_name' => 'OrgName',
            'email' => 'org@example.com',
            'phone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'organizer' => ['id', 'official_name', 'email'],
                'token',
            ]);

        $this->assertDatabaseHas('organizers', [
            'email' => 'org@example.com',
        ]);
    }

    public function test_register_requires_official_name(): void
    {
        $response = $this->postJson('/api/organizer/register', [
            'email' => 'org@example.com',
            'phone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['official_name']);
    }

    public function test_register_requires_valid_email(): void
    {
        $response = $this->postJson('/api/organizer/register', [
            'official_name' => 'OrgName',
            'email' => 'not-an-email',
            'phone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_requires_unique_email(): void
    {
        Organizer::factory()->create(['email' => 'org@example.com']);

        $response = $this->postJson('/api/organizer/register', [
            'official_name' => 'OrgName',
            'email' => 'org@example.com',
            'phone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/organizer/register', [
            'official_name' => 'OrgName',
            'email' => 'org@example.com',
            'phone' => '+1234567890',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_requires_minimum_password_length(): void
    {
        $response = $this->postJson('/api/organizer/register', [
            'official_name' => 'OrgName',
            'email' => 'org@example.com',
            'phone' => '+1234567890',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // ==================
    // LOGIN
    // ==================

    public function test_can_login_with_valid_credentials(): void
    {
        Organizer::factory()->create([
            'email' => 'org@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/organizer/login', [
            'email' => 'org@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'organizer' => ['id', 'official_name', 'email'],
                'token',
            ]);
    }

    public function test_cannot_login_with_wrong_password(): void
    {
        Organizer::factory()->create([
            'email' => 'org@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/organizer/login', [
            'email' => 'org@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/organizer/login', [
            'email' => 'nobody@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_email(): void
    {
        $response = $this->postJson('/api/organizer/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/api/organizer/login', [
            'email' => 'org@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // ==================
    // LOGOUT
    // ==================

    public function test_authenticated_organizer_can_logout(): void
    {
        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer, [], 'organizer');

        $response = $this->postJson('/api/organizer/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully.']);
    }

    public function test_guest_cannot_logout(): void
    {
        $response = $this->postJson('/api/organizer/logout');

        $response->assertStatus(401);
    }
}
