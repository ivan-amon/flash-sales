<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    // ==================================
    // Countries
    // ==================================
    public function test_guest_can_list_countries_ordered_by_name(): void
    {
        Country::factory()->create(['name' => 'Spain']);
        Country::factory()->create(['name' => 'Andorra']);

        $response = $this->getJson('/api/countries');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonPath('0.name', 'Andorra');
        $response->assertJsonPath('1.name', 'Spain');
        $response->assertJsonStructure([['id', 'name', 'created_at', 'updated_at']]);
    }

    // ==================================
    // Cities
    // ==================================
    public function test_guest_can_list_all_cities(): void
    {
        City::factory()->count(3)->create();

        $response = $this->getJson('/api/cities');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([['id', 'name', 'country_id', 'created_at', 'updated_at']]);
    }

    public function test_cities_can_be_filtered_by_country(): void
    {
        $spain = Country::factory()->create();
        $france = Country::factory()->create();

        City::factory()->create(['name' => 'Madrid', 'country_id' => $spain->id]);
        City::factory()->create(['name' => 'Barcelona', 'country_id' => $spain->id]);
        City::factory()->create(['name' => 'Paris', 'country_id' => $france->id]);

        $response = $this->getJson("/api/cities?country_id={$spain->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        foreach ($response->json() as $city) {
            $this->assertEquals($spain->id, $city['country_id']);
        }
    }

    public function test_cities_are_ordered_by_name(): void
    {
        $country = Country::factory()->create();
        City::factory()->create(['name' => 'Zaragoza', 'country_id' => $country->id]);
        City::factory()->create(['name' => 'Alicante', 'country_id' => $country->id]);

        $response = $this->getJson('/api/cities');

        $response->assertStatus(200);
        $response->assertJsonPath('0.name', 'Alicante');
        $response->assertJsonPath('1.name', 'Zaragoza');
    }
}
