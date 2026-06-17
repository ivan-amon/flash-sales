<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryCitySeeder extends Seeder
{
    /**
     * Seed the world's countries and a selection of their most important cities.
     *
     * Idempotent: uses firstOrCreate so re-running never duplicates rows. This is
     * always called from DatabaseSeeder so a fresh database is populated with the
     * full location catalogue on its first seed.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->locations() as $countryName => $data) {
                $country = Country::firstOrCreate(
                    ['iso_code' => $data['code']],
                    ['name' => $countryName],
                );

                foreach ($data['cities'] as $cityName) {
                    City::firstOrCreate([
                        'country_code' => $country->iso_code,
                        'name' => $cityName,
                    ]);
                }
            }
        });
    }

    /**
     * The country => [ISO 3166-1 alpha-2 code, cities] catalogue.
     *
     * @return array<string, array{code: string, cities: list<string>}>
     */
    private function locations(): array
    {
        return [
            'Spain' => ['code' => 'ES', 'cities' => ['Madrid', 'Barcelona', 'Valencia', 'Seville', 'Zaragoza', 'Málaga', 'Bilbao', 'Granada', 'Alicante', 'A Coruña', 'Murcia', 'Palma', 'Las Palmas', 'Valladolid', 'Vigo']],
            'Portugal' => ['code' => 'PT', 'cities' => ['Lisbon', 'Porto', 'Braga', 'Coimbra', 'Faro', 'Funchal', 'Aveiro', 'Guimarães', 'Évora', 'Setúbal', 'Sintra', 'Cascais', 'Viseu']],
            'France' => ['code' => 'FR', 'cities' => ['Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes', 'Bordeaux', 'Lille', 'Strasbourg', 'Montpellier', 'Rennes', 'Grenoble', 'Cannes']],
            'Italy' => ['code' => 'IT', 'cities' => ['Rome', 'Milan', 'Naples', 'Turin', 'Palermo', 'Genoa', 'Bologna', 'Florence', 'Venice', 'Verona', 'Bari', 'Catania', 'Pisa']],
            'Germany' => ['code' => 'DE', 'cities' => ['Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt', 'Stuttgart', 'Düsseldorf', 'Leipzig', 'Dortmund', 'Dresden', 'Hanover', 'Nuremberg', 'Bremen']],
            'United Kingdom' => ['code' => 'GB', 'cities' => ['London', 'Birmingham', 'Manchester', 'Glasgow', 'Liverpool', 'Edinburgh', 'Leeds', 'Bristol', 'Sheffield', 'Cardiff', 'Newcastle', 'Nottingham', 'Brighton']],
            'Netherlands' => ['code' => 'NL', 'cities' => ['Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven', 'Groningen', 'Tilburg', 'Breda', 'Nijmegen', 'Haarlem', 'Maastricht']],
            'United States' => ['code' => 'US', 'cities' => ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'San Francisco', 'Miami', 'Seattle', 'Boston', 'Las Vegas', 'Austin', 'Atlanta', 'Denver', 'Washington', 'New Orleans']],
            'Mexico' => ['code' => 'MX', 'cities' => ['Mexico City', 'Guadalajara', 'Monterrey', 'Puebla', 'Tijuana', 'Cancún', 'Mérida', 'Querétaro', 'León', 'Oaxaca', 'Guanajuato', 'Puerto Vallarta', 'Playa del Carmen']],
            'Argentina' => ['code' => 'AR', 'cities' => ['Buenos Aires', 'Córdoba', 'Rosario', 'Mendoza', 'La Plata', 'Mar del Plata', 'San Miguel de Tucumán', 'Salta', 'Santa Fe', 'San Carlos de Bariloche', 'Neuquén', 'Corrientes']],
        ];
    }
}
