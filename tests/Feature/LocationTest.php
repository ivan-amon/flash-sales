<?php

declare(strict_types=1);

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
        Country::factory()->create(['name' => 'Spain', 'iso_code' => 'ES']);
        Country::factory()->create(['name' => 'Andorra', 'iso_code' => 'AD']);

        $response = $this->getJson('/api/countries');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonPath('0.name', 'Andorra');
        $response->assertJsonPath('0.iso_code', 'AD');
        $response->assertJsonPath('1.name', 'Spain');
        $response->assertJsonPath('1.iso_code', 'ES');
        $response->assertJsonStructure([['name', 'iso_code', 'created_at', 'updated_at']]);
    }

    public function test_country_iso_code_must_be_unique(): void
    {
        Country::factory()->create(['iso_code' => 'ES']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Country::factory()->create(['iso_code' => 'ES']);
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
        $response->assertJsonStructure([['id', 'name', 'country_code', 'created_at', 'updated_at']]);
    }

    public function test_cities_can_be_filtered_by_country(): void
    {
        $spain = Country::factory()->create(['iso_code' => 'ES']);
        $france = Country::factory()->create(['iso_code' => 'FR']);

        City::factory()->create(['name' => 'Madrid', 'country_code' => $spain->iso_code]);
        City::factory()->create(['name' => 'Barcelona', 'country_code' => $spain->iso_code]);
        City::factory()->create(['name' => 'Paris', 'country_code' => $france->iso_code]);

        $response = $this->getJson("/api/cities?country_code={$spain->iso_code}");

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        foreach ($response->json() as $city) {
            $this->assertEquals($spain->iso_code, $city['country_code']);
        }
    }

    public function test_cities_are_ordered_by_name(): void
    {
        $country = Country::factory()->create();
        City::factory()->create(['name' => 'Zaragoza', 'country_code' => $country->iso_code]);
        City::factory()->create(['name' => 'Alicante', 'country_code' => $country->iso_code]);

        $response = $this->getJson('/api/cities');

        $response->assertStatus(200);
        $response->assertJsonPath('0.name', 'Alicante');
        $response->assertJsonPath('1.name', 'Zaragoza');
    }
}
