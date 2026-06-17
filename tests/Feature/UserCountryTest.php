<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserCountryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_their_country(): void
    {
        Country::factory()->create(['iso_code' => 'ES']);
        $user = User::factory()->create(['country_code' => null]);

        Sanctum::actingAs($user, ['is_user']);

        $response = $this->patchJson('/api/user/country', ['country_code' => 'ES']);

        $response->assertStatus(200);
        $response->assertJsonPath('country_code', 'ES');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'country_code' => 'ES',
        ]);
    }

    public function test_country_code_must_exist(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['is_user']);

        $response = $this->patchJson('/api/user/country', ['country_code' => 'ZZ']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('country_code');
    }

    public function test_country_code_is_required(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['is_user']);

        $response = $this->patchJson('/api/user/country', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('country_code');
    }

    public function test_guest_cannot_update_country(): void
    {
        Country::factory()->create(['iso_code' => 'ES']);

        $response = $this->patchJson('/api/user/country', ['country_code' => 'ES']);

        $response->assertStatus(401);
    }

    public function test_organizer_cannot_update_country(): void
    {
        Country::factory()->create(['iso_code' => 'ES']);
        $organizer = Organizer::factory()->create();

        Sanctum::actingAs($organizer, ['is_organizer']);

        $response = $this->patchJson('/api/user/country', ['country_code' => 'ES']);

        $response->assertStatus(403);
    }
}
