<?php

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
            foreach ($this->locations() as $countryName => $cities) {
                $country = Country::firstOrCreate(['name' => $countryName]);

                foreach ($cities as $cityName) {
                    City::firstOrCreate([
                        'country_id' => $country->id,
                        'name' => $cityName,
                    ]);
                }
            }
        });
    }

    /**
     * The country => cities catalogue.
     *
     * @return array<string, list<string>>
     */
    private function locations(): array
    {
        return [
            // ===== Europe =====
            'Albania' => ['Tirana', 'Durrës', 'Vlorë', 'Shkodër'],
            'Andorra' => ['Andorra la Vella', 'Escaldes-Engordany'],
            'Austria' => ['Vienna', 'Graz', 'Linz', 'Salzburg', 'Innsbruck'],
            'Belarus' => ['Minsk', 'Gomel', 'Mogilev', 'Vitebsk', 'Grodno'],
            'Belgium' => ['Brussels', 'Antwerp', 'Ghent', 'Charleroi', 'Liège', 'Bruges'],
            'Bosnia and Herzegovina' => ['Sarajevo', 'Banja Luka', 'Tuzla', 'Mostar'],
            'Bulgaria' => ['Sofia', 'Plovdiv', 'Varna', 'Burgas', 'Ruse'],
            'Croatia' => ['Zagreb', 'Split', 'Rijeka', 'Osijek', 'Dubrovnik'],
            'Cyprus' => ['Nicosia', 'Limassol', 'Larnaca', 'Paphos'],
            'Czech Republic' => ['Prague', 'Brno', 'Ostrava', 'Plzeň', 'Liberec'],
            'Denmark' => ['Copenhagen', 'Aarhus', 'Odense', 'Aalborg', 'Esbjerg'],
            'Estonia' => ['Tallinn', 'Tartu', 'Narva', 'Pärnu'],
            'Finland' => ['Helsinki', 'Espoo', 'Tampere', 'Vantaa', 'Turku', 'Oulu'],
            'France' => ['Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes', 'Bordeaux', 'Lille'],
            'Germany' => ['Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt', 'Stuttgart', 'Düsseldorf', 'Leipzig'],
            'Greece' => ['Athens', 'Thessaloniki', 'Patras', 'Heraklion', 'Larissa'],
            'Hungary' => ['Budapest', 'Debrecen', 'Szeged', 'Miskolc', 'Pécs'],
            'Iceland' => ['Reykjavík', 'Kópavogur', 'Hafnarfjörður', 'Akureyri'],
            'Ireland' => ['Dublin', 'Cork', 'Limerick', 'Galway', 'Waterford'],
            'Italy' => ['Rome', 'Milan', 'Naples', 'Turin', 'Palermo', 'Genoa', 'Bologna', 'Florence', 'Venice'],
            'Kosovo' => ['Pristina', 'Prizren', 'Peja', 'Gjakova'],
            'Latvia' => ['Riga', 'Daugavpils', 'Liepāja', 'Jelgava'],
            'Liechtenstein' => ['Vaduz', 'Schaan'],
            'Lithuania' => ['Vilnius', 'Kaunas', 'Klaipėda', 'Šiauliai'],
            'Luxembourg' => ['Luxembourg City', 'Esch-sur-Alzette', 'Differdange'],
            'Malta' => ['Valletta', 'Birkirkara', 'Sliema', 'Mosta'],
            'Moldova' => ['Chișinău', 'Tiraspol', 'Bălți', 'Bender'],
            'Monaco' => ['Monaco', 'Monte Carlo'],
            'Montenegro' => ['Podgorica', 'Nikšić', 'Herceg Novi', 'Budva'],
            'Netherlands' => ['Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven', 'Groningen'],
            'North Macedonia' => ['Skopje', 'Bitola', 'Kumanovo', 'Ohrid'],
            'Norway' => ['Oslo', 'Bergen', 'Trondheim', 'Stavanger', 'Drammen'],
            'Poland' => ['Warsaw', 'Kraków', 'Łódź', 'Wrocław', 'Poznań', 'Gdańsk'],
            'Portugal' => ['Lisbon', 'Porto', 'Braga', 'Coimbra', 'Faro', 'Funchal'],
            'Romania' => ['Bucharest', 'Cluj-Napoca', 'Timișoara', 'Iași', 'Constanța', 'Brașov'],
            'Russia' => ['Moscow', 'Saint Petersburg', 'Novosibirsk', 'Yekaterinburg', 'Kazan', 'Sochi'],
            'San Marino' => ['San Marino', 'Serravalle'],
            'Serbia' => ['Belgrade', 'Novi Sad', 'Niš', 'Kragujevac'],
            'Slovakia' => ['Bratislava', 'Košice', 'Prešov', 'Žilina'],
            'Slovenia' => ['Ljubljana', 'Maribor', 'Celje', 'Kranj'],
            'Spain' => ['Madrid', 'Barcelona', 'Valencia', 'Seville', 'Zaragoza', 'Málaga', 'Bilbao', 'A Coruña', 'Granada'],
            'Sweden' => ['Stockholm', 'Gothenburg', 'Malmö', 'Uppsala', 'Västerås'],
            'Switzerland' => ['Zürich', 'Geneva', 'Basel', 'Bern', 'Lausanne', 'Lucerne'],
            'Ukraine' => ['Kyiv', 'Kharkiv', 'Odesa', 'Dnipro', 'Lviv', 'Zaporizhzhia'],
            'United Kingdom' => ['London', 'Birmingham', 'Manchester', 'Glasgow', 'Liverpool', 'Edinburgh', 'Leeds', 'Bristol'],
            'Vatican City' => ['Vatican City'],

            // ===== Asia =====
            'Afghanistan' => ['Kabul', 'Kandahar', 'Herat', 'Mazar-i-Sharif'],
            'Armenia' => ['Yerevan', 'Gyumri', 'Vanadzor'],
            'Azerbaijan' => ['Baku', 'Ganja', 'Sumqayit', 'Mingachevir'],
            'Bahrain' => ['Manama', 'Riffa', 'Muharraq'],
            'Bangladesh' => ['Dhaka', 'Chittagong', 'Khulna', 'Rajshahi', 'Sylhet'],
            'Bhutan' => ['Thimphu', 'Phuntsholing', 'Paro'],
            'Brunei' => ['Bandar Seri Begawan', 'Kuala Belait', 'Seria'],
            'Cambodia' => ['Phnom Penh', 'Siem Reap', 'Battambang', 'Sihanoukville'],
            'China' => ['Beijing', 'Shanghai', 'Guangzhou', 'Shenzhen', 'Chengdu', 'Chongqing', 'Wuhan', 'Xi\'an', 'Hangzhou'],
            'Georgia' => ['Tbilisi', 'Batumi', 'Kutaisi', 'Rustavi'],
            'India' => ['New Delhi', 'Mumbai', 'Bangalore', 'Kolkata', 'Chennai', 'Hyderabad', 'Pune', 'Ahmedabad', 'Jaipur'],
            'Indonesia' => ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar'],
            'Iran' => ['Tehran', 'Mashhad', 'Isfahan', 'Shiraz', 'Tabriz', 'Karaj'],
            'Iraq' => ['Baghdad', 'Basra', 'Mosul', 'Erbil', 'Najaf'],
            'Israel' => ['Jerusalem', 'Tel Aviv', 'Haifa', 'Rishon LeZion', 'Beersheba'],
            'Japan' => ['Tokyo', 'Yokohama', 'Osaka', 'Nagoya', 'Sapporo', 'Fukuoka', 'Kyoto', 'Kobe'],
            'Jordan' => ['Amman', 'Zarqa', 'Irbid', 'Aqaba'],
            'Kazakhstan' => ['Astana', 'Almaty', 'Shymkent', 'Karaganda'],
            'Kuwait' => ['Kuwait City', 'Al Ahmadi', 'Hawalli'],
            'Kyrgyzstan' => ['Bishkek', 'Osh', 'Jalal-Abad'],
            'Laos' => ['Vientiane', 'Pakse', 'Savannakhet', 'Luang Prabang'],
            'Lebanon' => ['Beirut', 'Tripoli', 'Sidon', 'Tyre'],
            'Malaysia' => ['Kuala Lumpur', 'George Town', 'Ipoh', 'Johor Bahru', 'Malacca City'],
            'Maldives' => ['Malé', 'Addu City', 'Fuvahmulah'],
            'Mongolia' => ['Ulaanbaatar', 'Erdenet', 'Darkhan'],
            'Myanmar' => ['Naypyidaw', 'Yangon', 'Mandalay', 'Bago'],
            'Nepal' => ['Kathmandu', 'Pokhara', 'Lalitpur', 'Biratnagar'],
            'North Korea' => ['Pyongyang', 'Hamhung', 'Chongjin', 'Nampo'],
            'Oman' => ['Muscat', 'Salalah', 'Sohar', 'Nizwa'],
            'Pakistan' => ['Islamabad', 'Karachi', 'Lahore', 'Faisalabad', 'Rawalpindi', 'Multan'],
            'Palestine' => ['Ramallah', 'Gaza', 'Hebron', 'Nablus', 'Bethlehem'],
            'Philippines' => ['Manila', 'Quezon City', 'Davao', 'Cebu City', 'Makati'],
            'Qatar' => ['Doha', 'Al Rayyan', 'Al Wakrah'],
            'Saudi Arabia' => ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam'],
            'Singapore' => ['Singapore'],
            'South Korea' => ['Seoul', 'Busan', 'Incheon', 'Daegu', 'Daejeon', 'Gwangju'],
            'Sri Lanka' => ['Colombo', 'Kandy', 'Galle', 'Jaffna', 'Sri Jayawardenepura Kotte'],
            'Syria' => ['Damascus', 'Aleppo', 'Homs', 'Latakia'],
            'Taiwan' => ['Taipei', 'Kaohsiung', 'Taichung', 'Tainan'],
            'Tajikistan' => ['Dushanbe', 'Khujand', 'Bokhtar'],
            'Thailand' => ['Bangkok', 'Chiang Mai', 'Pattaya', 'Phuket', 'Nonthaburi'],
            'Timor-Leste' => ['Dili', 'Baucau', 'Maliana'],
            'Turkey' => ['Ankara', 'Istanbul', 'Izmir', 'Bursa', 'Antalya', 'Adana'],
            'Turkmenistan' => ['Ashgabat', 'Türkmenabat', 'Daşoguz'],
            'United Arab Emirates' => ['Abu Dhabi', 'Dubai', 'Sharjah', 'Al Ain', 'Ajman'],
            'Uzbekistan' => ['Tashkent', 'Samarkand', 'Bukhara', 'Andijan', 'Namangan'],
            'Vietnam' => ['Hanoi', 'Ho Chi Minh City', 'Da Nang', 'Haiphong', 'Can Tho'],
            'Yemen' => ['Sana\'a', 'Aden', 'Taiz', 'Hodeidah'],

            // ===== Africa =====
            'Algeria' => ['Algiers', 'Oran', 'Constantine', 'Annaba'],
            'Angola' => ['Luanda', 'Huambo', 'Lobito', 'Benguela'],
            'Benin' => ['Porto-Novo', 'Cotonou', 'Parakou'],
            'Botswana' => ['Gaborone', 'Francistown', 'Maun'],
            'Burkina Faso' => ['Ouagadougou', 'Bobo-Dioulasso', 'Koudougou'],
            'Burundi' => ['Gitega', 'Bujumbura', 'Ngozi'],
            'Cabo Verde' => ['Praia', 'Mindelo', 'Santa Maria'],
            'Cameroon' => ['Yaoundé', 'Douala', 'Bamenda', 'Garoua'],
            'Central African Republic' => ['Bangui', 'Bimbo', 'Berbérati'],
            'Chad' => ['N\'Djamena', 'Moundou', 'Sarh'],
            'Comoros' => ['Moroni', 'Mutsamudu'],
            'Democratic Republic of the Congo' => ['Kinshasa', 'Lubumbashi', 'Mbuji-Mayi', 'Kisangani', 'Goma'],
            'Republic of the Congo' => ['Brazzaville', 'Pointe-Noire', 'Dolisie'],
            'Djibouti' => ['Djibouti', 'Ali Sabieh'],
            'Egypt' => ['Cairo', 'Alexandria', 'Giza', 'Luxor', 'Aswan', 'Port Said'],
            'Equatorial Guinea' => ['Malabo', 'Bata'],
            'Eritrea' => ['Asmara', 'Keren', 'Massawa'],
            'Eswatini' => ['Mbabane', 'Manzini', 'Lobamba'],
            'Ethiopia' => ['Addis Ababa', 'Dire Dawa', 'Mekelle', 'Gondar'],
            'Gabon' => ['Libreville', 'Port-Gentil', 'Franceville'],
            'Gambia' => ['Banjul', 'Serekunda', 'Brikama'],
            'Ghana' => ['Accra', 'Kumasi', 'Tamale', 'Takoradi'],
            'Guinea' => ['Conakry', 'Nzérékoré', 'Kankan'],
            'Guinea-Bissau' => ['Bissau', 'Bafatá', 'Gabú'],
            'Ivory Coast' => ['Yamoussoukro', 'Abidjan', 'Bouaké', 'Daloa'],
            'Kenya' => ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret'],
            'Lesotho' => ['Maseru', 'Teyateyaneng', 'Mafeteng'],
            'Liberia' => ['Monrovia', 'Gbarnga', 'Buchanan'],
            'Libya' => ['Tripoli', 'Benghazi', 'Misrata', 'Sabha'],
            'Madagascar' => ['Antananarivo', 'Toamasina', 'Antsirabe', 'Mahajanga'],
            'Malawi' => ['Lilongwe', 'Blantyre', 'Mzuzu', 'Zomba'],
            'Mali' => ['Bamako', 'Sikasso', 'Mopti', 'Gao'],
            'Mauritania' => ['Nouakchott', 'Nouadhibou', 'Kaédi'],
            'Mauritius' => ['Port Louis', 'Curepipe', 'Vacoas'],
            'Morocco' => ['Rabat', 'Casablanca', 'Marrakesh', 'Fez', 'Tangier', 'Agadir'],
            'Mozambique' => ['Maputo', 'Matola', 'Beira', 'Nampula'],
            'Namibia' => ['Windhoek', 'Walvis Bay', 'Swakopmund'],
            'Niger' => ['Niamey', 'Zinder', 'Maradi'],
            'Nigeria' => ['Abuja', 'Lagos', 'Kano', 'Ibadan', 'Port Harcourt', 'Benin City'],
            'Rwanda' => ['Kigali', 'Butare', 'Gisenyi'],
            'São Tomé and Príncipe' => ['São Tomé', 'Santo António'],
            'Senegal' => ['Dakar', 'Touba', 'Thiès', 'Saint-Louis'],
            'Seychelles' => ['Victoria', 'Anse Boileau'],
            'Sierra Leone' => ['Freetown', 'Bo', 'Kenema', 'Makeni'],
            'Somalia' => ['Mogadishu', 'Hargeisa', 'Kismayo', 'Bosaso'],
            'South Africa' => ['Pretoria', 'Cape Town', 'Johannesburg', 'Durban', 'Port Elizabeth', 'Bloemfontein'],
            'South Sudan' => ['Juba', 'Malakal', 'Wau'],
            'Sudan' => ['Khartoum', 'Omdurman', 'Port Sudan', 'Kassala'],
            'Tanzania' => ['Dodoma', 'Dar es Salaam', 'Mwanza', 'Arusha', 'Zanzibar City'],
            'Togo' => ['Lomé', 'Sokodé', 'Kara'],
            'Tunisia' => ['Tunis', 'Sfax', 'Sousse', 'Kairouan'],
            'Uganda' => ['Kampala', 'Gulu', 'Mbarara', 'Jinja'],
            'Zambia' => ['Lusaka', 'Kitwe', 'Ndola', 'Kabwe'],
            'Zimbabwe' => ['Harare', 'Bulawayo', 'Chitungwiza', 'Mutare'],

            // ===== North America =====
            'Antigua and Barbuda' => ['St. John\'s', 'All Saints'],
            'Bahamas' => ['Nassau', 'Freeport'],
            'Barbados' => ['Bridgetown', 'Speightstown'],
            'Belize' => ['Belmopan', 'Belize City', 'San Ignacio'],
            'Canada' => ['Ottawa', 'Toronto', 'Montreal', 'Vancouver', 'Calgary', 'Edmonton', 'Quebec City'],
            'Costa Rica' => ['San José', 'Alajuela', 'Cartago', 'Heredia'],
            'Cuba' => ['Havana', 'Santiago de Cuba', 'Camagüey', 'Holguín'],
            'Dominica' => ['Roseau', 'Portsmouth'],
            'Dominican Republic' => ['Santo Domingo', 'Santiago', 'La Romana', 'Punta Cana'],
            'El Salvador' => ['San Salvador', 'Santa Ana', 'San Miguel'],
            'Grenada' => ['St. George\'s', 'Gouyave'],
            'Guatemala' => ['Guatemala City', 'Quetzaltenango', 'Antigua Guatemala'],
            'Haiti' => ['Port-au-Prince', 'Cap-Haïtien', 'Les Cayes'],
            'Honduras' => ['Tegucigalpa', 'San Pedro Sula', 'La Ceiba'],
            'Jamaica' => ['Kingston', 'Montego Bay', 'Spanish Town'],
            'Mexico' => ['Mexico City', 'Guadalajara', 'Monterrey', 'Puebla', 'Tijuana', 'Cancún', 'Mérida'],
            'Nicaragua' => ['Managua', 'León', 'Granada', 'Masaya'],
            'Panama' => ['Panama City', 'Colón', 'David'],
            'Saint Kitts and Nevis' => ['Basseterre', 'Charlestown'],
            'Saint Lucia' => ['Castries', 'Vieux Fort'],
            'Saint Vincent and the Grenadines' => ['Kingstown', 'Georgetown'],
            'Trinidad and Tobago' => ['Port of Spain', 'San Fernando', 'Chaguanas'],
            'United States' => ['Washington', 'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'San Francisco', 'Miami', 'Seattle'],

            // ===== South America =====
            'Argentina' => ['Buenos Aires', 'Córdoba', 'Rosario', 'Mendoza', 'La Plata', 'Mar del Plata'],
            'Bolivia' => ['La Paz', 'Santa Cruz de la Sierra', 'Cochabamba', 'Sucre'],
            'Brazil' => ['Brasília', 'São Paulo', 'Rio de Janeiro', 'Salvador', 'Fortaleza', 'Belo Horizonte', 'Curitiba', 'Recife'],
            'Chile' => ['Santiago', 'Valparaíso', 'Concepción', 'Antofagasta', 'Viña del Mar'],
            'Colombia' => ['Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Cartagena'],
            'Ecuador' => ['Quito', 'Guayaquil', 'Cuenca', 'Santo Domingo'],
            'Guyana' => ['Georgetown', 'Linden', 'New Amsterdam'],
            'Paraguay' => ['Asunción', 'Ciudad del Este', 'Encarnación'],
            'Peru' => ['Lima', 'Arequipa', 'Trujillo', 'Cusco', 'Chiclayo'],
            'Suriname' => ['Paramaribo', 'Lelydorp', 'Nieuw Nickerie'],
            'Uruguay' => ['Montevideo', 'Salto', 'Punta del Este', 'Paysandú'],
            'Venezuela' => ['Caracas', 'Maracaibo', 'Valencia', 'Barquisimeto', 'Maracay'],

            // ===== Oceania =====
            'Australia' => ['Canberra', 'Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide', 'Gold Coast'],
            'Fiji' => ['Suva', 'Nadi', 'Lautoka'],
            'Kiribati' => ['Tarawa', 'Betio'],
            'Marshall Islands' => ['Majuro', 'Ebeye'],
            'Micronesia' => ['Palikir', 'Weno', 'Kolonia'],
            'Nauru' => ['Yaren'],
            'New Zealand' => ['Wellington', 'Auckland', 'Christchurch', 'Hamilton', 'Dunedin', 'Queenstown'],
            'Palau' => ['Ngerulmud', 'Koror'],
            'Papua New Guinea' => ['Port Moresby', 'Lae', 'Mount Hagen'],
            'Samoa' => ['Apia', 'Asau'],
            'Solomon Islands' => ['Honiara', 'Auki', 'Gizo'],
            'Tonga' => ['Nukuʻalofa', 'Neiafu'],
            'Tuvalu' => ['Funafuti'],
            'Vanuatu' => ['Port Vila', 'Luganville'],
        ];
    }
}
