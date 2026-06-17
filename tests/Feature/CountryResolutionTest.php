<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;
use Tests\TestCase;

class CountryResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_without_context_falls_back_to_spain(): void
    {
        Location::shouldReceive('get')->andReturnFalse();

        $this->createEventsIn('ES', 2);
        $this->createEventsIn('FR', 3);

        $response = $this->getJson('/api/events');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $this->assertEventsBelongTo($response->json(), 'ES');
    }

    public function test_ip_geolocation_is_used_when_no_header_is_present(): void
    {
        Location::shouldReceive('get')->andReturn($this->position('US'));

        $this->createEventsIn('US', 3);
        $this->createEventsIn('ES', 2);

        $response = $this->getJson('/api/events');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $this->assertEventsBelongTo($response->json(), 'US');
    }

    public function test_explicit_header_takes_precedence_over_ip(): void
    {
        Location::shouldReceive('get')->andReturn($this->position('FR'));

        $this->createEventsIn('US', 2);
        $this->createEventsIn('FR', 4);

        $response = $this->withHeader('X-Country-Code', 'US')->getJson('/api/events');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $this->assertEventsBelongTo($response->json(), 'US');
    }

    public function test_authenticated_user_profile_wins_over_header_and_ip(): void
    {
        Location::shouldReceive('get')->andReturn($this->position('US'));

        $country = Country::factory()->create(['iso_code' => 'ES']);
        $user = User::factory()->create(['country_code' => $country->iso_code]);

        $this->createEventsIn('ES', 3);
        $this->createEventsIn('US', 5);

        Sanctum::actingAs($user);

        $response = $this->withHeader('X-Country-Code', 'US')->getJson('/api/events');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $this->assertEventsBelongTo($response->json(), 'ES');
    }

    public function test_invalid_header_falls_back_to_spain(): void
    {
        $this->createEventsIn('ES', 2);
        $this->createEventsIn('US', 4);

        $response = $this->withHeader('X-Country-Code', 'ZZ')->getJson('/api/events');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $this->assertEventsBelongTo($response->json(), 'ES');
    }

    /**
     * Create a country (if missing) with a city and the given number of events.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Event>
     */
    private function createEventsIn(string $code, int $count): Collection
    {
        $country = Country::firstOrCreate(['iso_code' => $code], ['name' => $code]);
        $city = City::factory()->create(['country_code' => $country->iso_code]);

        return Event::factory()->count($count)->create([
            'organizer_id' => Organizer::factory()->create()->id,
            'city_id' => $city->id,
        ]);
    }

    private function position(string $code): Position
    {
        $position = new Position;
        $position->countryCode = $code;

        return $position;
    }

    /**
     * @param  array<int, array<string, mixed>>  $events
     */
    private function assertEventsBelongTo(array $events, string $code): void
    {
        foreach ($events as $event) {
            $this->assertSame($code, $event['city']['country_code']);
        }
    }
}
