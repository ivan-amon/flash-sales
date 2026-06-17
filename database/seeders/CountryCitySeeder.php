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
            // ===== Europe =====
            'Albania' => ['code' => 'AL', 'cities' => ['Tirana', 'Durrës', 'Vlorë', 'Shkodër']],
            'Andorra' => ['code' => 'AD', 'cities' => ['Andorra la Vella', 'Escaldes-Engordany']],
            'Austria' => ['code' => 'AT', 'cities' => ['Vienna', 'Graz', 'Linz', 'Salzburg', 'Innsbruck']],
            'Belarus' => ['code' => 'BY', 'cities' => ['Minsk', 'Gomel', 'Mogilev', 'Vitebsk', 'Grodno']],
            'Belgium' => ['code' => 'BE', 'cities' => ['Brussels', 'Antwerp', 'Ghent', 'Charleroi', 'Liège', 'Bruges']],
            'Bosnia and Herzegovina' => ['code' => 'BA', 'cities' => ['Sarajevo', 'Banja Luka', 'Tuzla', 'Mostar']],
            'Bulgaria' => ['code' => 'BG', 'cities' => ['Sofia', 'Plovdiv', 'Varna', 'Burgas', 'Ruse']],
            'Croatia' => ['code' => 'HR', 'cities' => ['Zagreb', 'Split', 'Rijeka', 'Osijek', 'Dubrovnik']],
            'Cyprus' => ['code' => 'CY', 'cities' => ['Nicosia', 'Limassol', 'Larnaca', 'Paphos']],
            'Czech Republic' => ['code' => 'CZ', 'cities' => ['Prague', 'Brno', 'Ostrava', 'Plzeň', 'Liberec']],
            'Denmark' => ['code' => 'DK', 'cities' => ['Copenhagen', 'Aarhus', 'Odense', 'Aalborg', 'Esbjerg']],
            'Estonia' => ['code' => 'EE', 'cities' => ['Tallinn', 'Tartu', 'Narva', 'Pärnu']],
            'Finland' => ['code' => 'FI', 'cities' => ['Helsinki', 'Espoo', 'Tampere', 'Vantaa', 'Turku', 'Oulu']],
            'France' => ['code' => 'FR', 'cities' => ['Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes', 'Bordeaux', 'Lille']],
            'Germany' => ['code' => 'DE', 'cities' => ['Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt', 'Stuttgart', 'Düsseldorf', 'Leipzig']],
            'Greece' => ['code' => 'GR', 'cities' => ['Athens', 'Thessaloniki', 'Patras', 'Heraklion', 'Larissa']],
            'Hungary' => ['code' => 'HU', 'cities' => ['Budapest', 'Debrecen', 'Szeged', 'Miskolc', 'Pécs']],
            'Iceland' => ['code' => 'IS', 'cities' => ['Reykjavík', 'Kópavogur', 'Hafnarfjörður', 'Akureyri']],
            'Ireland' => ['code' => 'IE', 'cities' => ['Dublin', 'Cork', 'Limerick', 'Galway', 'Waterford']],
            'Italy' => ['code' => 'IT', 'cities' => ['Rome', 'Milan', 'Naples', 'Turin', 'Palermo', 'Genoa', 'Bologna', 'Florence', 'Venice']],
            'Kosovo' => ['code' => 'XK', 'cities' => ['Pristina', 'Prizren', 'Peja', 'Gjakova']],
            'Latvia' => ['code' => 'LV', 'cities' => ['Riga', 'Daugavpils', 'Liepāja', 'Jelgava']],
            'Liechtenstein' => ['code' => 'LI', 'cities' => ['Vaduz', 'Schaan']],
            'Lithuania' => ['code' => 'LT', 'cities' => ['Vilnius', 'Kaunas', 'Klaipėda', 'Šiauliai']],
            'Luxembourg' => ['code' => 'LU', 'cities' => ['Luxembourg City', 'Esch-sur-Alzette', 'Differdange']],
            'Malta' => ['code' => 'MT', 'cities' => ['Valletta', 'Birkirkara', 'Sliema', 'Mosta']],
            'Moldova' => ['code' => 'MD', 'cities' => ['Chișinău', 'Tiraspol', 'Bălți', 'Bender']],
            'Monaco' => ['code' => 'MC', 'cities' => ['Monaco', 'Monte Carlo']],
            'Montenegro' => ['code' => 'ME', 'cities' => ['Podgorica', 'Nikšić', 'Herceg Novi', 'Budva']],
            'Netherlands' => ['code' => 'NL', 'cities' => ['Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven', 'Groningen']],
            'North Macedonia' => ['code' => 'MK', 'cities' => ['Skopje', 'Bitola', 'Kumanovo', 'Ohrid']],
            'Norway' => ['code' => 'NO', 'cities' => ['Oslo', 'Bergen', 'Trondheim', 'Stavanger', 'Drammen']],
            'Poland' => ['code' => 'PL', 'cities' => ['Warsaw', 'Kraków', 'Łódź', 'Wrocław', 'Poznań', 'Gdańsk']],
            'Portugal' => ['code' => 'PT', 'cities' => ['Lisbon', 'Porto', 'Braga', 'Coimbra', 'Faro', 'Funchal']],
            'Romania' => ['code' => 'RO', 'cities' => ['Bucharest', 'Cluj-Napoca', 'Timișoara', 'Iași', 'Constanța', 'Brașov']],
            'Russia' => ['code' => 'RU', 'cities' => ['Moscow', 'Saint Petersburg', 'Novosibirsk', 'Yekaterinburg', 'Kazan', 'Sochi']],
            'San Marino' => ['code' => 'SM', 'cities' => ['San Marino', 'Serravalle']],
            'Serbia' => ['code' => 'RS', 'cities' => ['Belgrade', 'Novi Sad', 'Niš', 'Kragujevac']],
            'Slovakia' => ['code' => 'SK', 'cities' => ['Bratislava', 'Košice', 'Prešov', 'Žilina']],
            'Slovenia' => ['code' => 'SI', 'cities' => ['Ljubljana', 'Maribor', 'Celje', 'Kranj']],
            'Spain' => ['code' => 'ES', 'cities' => ['Madrid', 'Barcelona', 'Valencia', 'Seville', 'Zaragoza', 'Málaga', 'Bilbao', 'A Coruña', 'Granada']],
            'Sweden' => ['code' => 'SE', 'cities' => ['Stockholm', 'Gothenburg', 'Malmö', 'Uppsala', 'Västerås']],
            'Switzerland' => ['code' => 'CH', 'cities' => ['Zürich', 'Geneva', 'Basel', 'Bern', 'Lausanne', 'Lucerne']],
            'Ukraine' => ['code' => 'UA', 'cities' => ['Kyiv', 'Kharkiv', 'Odesa', 'Dnipro', 'Lviv', 'Zaporizhzhia']],
            'United Kingdom' => ['code' => 'GB', 'cities' => ['London', 'Birmingham', 'Manchester', 'Glasgow', 'Liverpool', 'Edinburgh', 'Leeds', 'Bristol']],
            'Vatican City' => ['code' => 'VA', 'cities' => ['Vatican City']],

            // ===== Asia =====
            'Afghanistan' => ['code' => 'AF', 'cities' => ['Kabul', 'Kandahar', 'Herat', 'Mazar-i-Sharif']],
            'Armenia' => ['code' => 'AM', 'cities' => ['Yerevan', 'Gyumri', 'Vanadzor']],
            'Azerbaijan' => ['code' => 'AZ', 'cities' => ['Baku', 'Ganja', 'Sumqayit', 'Mingachevir']],
            'Bahrain' => ['code' => 'BH', 'cities' => ['Manama', 'Riffa', 'Muharraq']],
            'Bangladesh' => ['code' => 'BD', 'cities' => ['Dhaka', 'Chittagong', 'Khulna', 'Rajshahi', 'Sylhet']],
            'Bhutan' => ['code' => 'BT', 'cities' => ['Thimphu', 'Phuntsholing', 'Paro']],
            'Brunei' => ['code' => 'BN', 'cities' => ['Bandar Seri Begawan', 'Kuala Belait', 'Seria']],
            'Cambodia' => ['code' => 'KH', 'cities' => ['Phnom Penh', 'Siem Reap', 'Battambang', 'Sihanoukville']],
            'China' => ['code' => 'CN', 'cities' => ['Beijing', 'Shanghai', 'Guangzhou', 'Shenzhen', 'Chengdu', 'Chongqing', 'Wuhan', 'Xi\'an', 'Hangzhou']],
            'Georgia' => ['code' => 'GE', 'cities' => ['Tbilisi', 'Batumi', 'Kutaisi', 'Rustavi']],
            'India' => ['code' => 'IN', 'cities' => ['New Delhi', 'Mumbai', 'Bangalore', 'Kolkata', 'Chennai', 'Hyderabad', 'Pune', 'Ahmedabad', 'Jaipur']],
            'Indonesia' => ['code' => 'ID', 'cities' => ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar']],
            'Iran' => ['code' => 'IR', 'cities' => ['Tehran', 'Mashhad', 'Isfahan', 'Shiraz', 'Tabriz', 'Karaj']],
            'Iraq' => ['code' => 'IQ', 'cities' => ['Baghdad', 'Basra', 'Mosul', 'Erbil', 'Najaf']],
            'Israel' => ['code' => 'IL', 'cities' => ['Jerusalem', 'Tel Aviv', 'Haifa', 'Rishon LeZion', 'Beersheba']],
            'Japan' => ['code' => 'JP', 'cities' => ['Tokyo', 'Yokohama', 'Osaka', 'Nagoya', 'Sapporo', 'Fukuoka', 'Kyoto', 'Kobe']],
            'Jordan' => ['code' => 'JO', 'cities' => ['Amman', 'Zarqa', 'Irbid', 'Aqaba']],
            'Kazakhstan' => ['code' => 'KZ', 'cities' => ['Astana', 'Almaty', 'Shymkent', 'Karaganda']],
            'Kuwait' => ['code' => 'KW', 'cities' => ['Kuwait City', 'Al Ahmadi', 'Hawalli']],
            'Kyrgyzstan' => ['code' => 'KG', 'cities' => ['Bishkek', 'Osh', 'Jalal-Abad']],
            'Laos' => ['code' => 'LA', 'cities' => ['Vientiane', 'Pakse', 'Savannakhet', 'Luang Prabang']],
            'Lebanon' => ['code' => 'LB', 'cities' => ['Beirut', 'Tripoli', 'Sidon', 'Tyre']],
            'Malaysia' => ['code' => 'MY', 'cities' => ['Kuala Lumpur', 'George Town', 'Ipoh', 'Johor Bahru', 'Malacca City']],
            'Maldives' => ['code' => 'MV', 'cities' => ['Malé', 'Addu City', 'Fuvahmulah']],
            'Mongolia' => ['code' => 'MN', 'cities' => ['Ulaanbaatar', 'Erdenet', 'Darkhan']],
            'Myanmar' => ['code' => 'MM', 'cities' => ['Naypyidaw', 'Yangon', 'Mandalay', 'Bago']],
            'Nepal' => ['code' => 'NP', 'cities' => ['Kathmandu', 'Pokhara', 'Lalitpur', 'Biratnagar']],
            'North Korea' => ['code' => 'KP', 'cities' => ['Pyongyang', 'Hamhung', 'Chongjin', 'Nampo']],
            'Oman' => ['code' => 'OM', 'cities' => ['Muscat', 'Salalah', 'Sohar', 'Nizwa']],
            'Pakistan' => ['code' => 'PK', 'cities' => ['Islamabad', 'Karachi', 'Lahore', 'Faisalabad', 'Rawalpindi', 'Multan']],
            'Palestine' => ['code' => 'PS', 'cities' => ['Ramallah', 'Gaza', 'Hebron', 'Nablus', 'Bethlehem']],
            'Philippines' => ['code' => 'PH', 'cities' => ['Manila', 'Quezon City', 'Davao', 'Cebu City', 'Makati']],
            'Qatar' => ['code' => 'QA', 'cities' => ['Doha', 'Al Rayyan', 'Al Wakrah']],
            'Saudi Arabia' => ['code' => 'SA', 'cities' => ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam']],
            'Singapore' => ['code' => 'SG', 'cities' => ['Singapore']],
            'South Korea' => ['code' => 'KR', 'cities' => ['Seoul', 'Busan', 'Incheon', 'Daegu', 'Daejeon', 'Gwangju']],
            'Sri Lanka' => ['code' => 'LK', 'cities' => ['Colombo', 'Kandy', 'Galle', 'Jaffna', 'Sri Jayawardenepura Kotte']],
            'Syria' => ['code' => 'SY', 'cities' => ['Damascus', 'Aleppo', 'Homs', 'Latakia']],
            'Taiwan' => ['code' => 'TW', 'cities' => ['Taipei', 'Kaohsiung', 'Taichung', 'Tainan']],
            'Tajikistan' => ['code' => 'TJ', 'cities' => ['Dushanbe', 'Khujand', 'Bokhtar']],
            'Thailand' => ['code' => 'TH', 'cities' => ['Bangkok', 'Chiang Mai', 'Pattaya', 'Phuket', 'Nonthaburi']],
            'Timor-Leste' => ['code' => 'TL', 'cities' => ['Dili', 'Baucau', 'Maliana']],
            'Turkey' => ['code' => 'TR', 'cities' => ['Ankara', 'Istanbul', 'Izmir', 'Bursa', 'Antalya', 'Adana']],
            'Turkmenistan' => ['code' => 'TM', 'cities' => ['Ashgabat', 'Türkmenabat', 'Daşoguz']],
            'United Arab Emirates' => ['code' => 'AE', 'cities' => ['Abu Dhabi', 'Dubai', 'Sharjah', 'Al Ain', 'Ajman']],
            'Uzbekistan' => ['code' => 'UZ', 'cities' => ['Tashkent', 'Samarkand', 'Bukhara', 'Andijan', 'Namangan']],
            'Vietnam' => ['code' => 'VN', 'cities' => ['Hanoi', 'Ho Chi Minh City', 'Da Nang', 'Haiphong', 'Can Tho']],
            'Yemen' => ['code' => 'YE', 'cities' => ['Sana\'a', 'Aden', 'Taiz', 'Hodeidah']],

            // ===== Africa =====
            'Algeria' => ['code' => 'DZ', 'cities' => ['Algiers', 'Oran', 'Constantine', 'Annaba']],
            'Angola' => ['code' => 'AO', 'cities' => ['Luanda', 'Huambo', 'Lobito', 'Benguela']],
            'Benin' => ['code' => 'BJ', 'cities' => ['Porto-Novo', 'Cotonou', 'Parakou']],
            'Botswana' => ['code' => 'BW', 'cities' => ['Gaborone', 'Francistown', 'Maun']],
            'Burkina Faso' => ['code' => 'BF', 'cities' => ['Ouagadougou', 'Bobo-Dioulasso', 'Koudougou']],
            'Burundi' => ['code' => 'BI', 'cities' => ['Gitega', 'Bujumbura', 'Ngozi']],
            'Cabo Verde' => ['code' => 'CV', 'cities' => ['Praia', 'Mindelo', 'Santa Maria']],
            'Cameroon' => ['code' => 'CM', 'cities' => ['Yaoundé', 'Douala', 'Bamenda', 'Garoua']],
            'Central African Republic' => ['code' => 'CF', 'cities' => ['Bangui', 'Bimbo', 'Berbérati']],
            'Chad' => ['code' => 'TD', 'cities' => ['N\'Djamena', 'Moundou', 'Sarh']],
            'Comoros' => ['code' => 'KM', 'cities' => ['Moroni', 'Mutsamudu']],
            'Democratic Republic of the Congo' => ['code' => 'CD', 'cities' => ['Kinshasa', 'Lubumbashi', 'Mbuji-Mayi', 'Kisangani', 'Goma']],
            'Republic of the Congo' => ['code' => 'CG', 'cities' => ['Brazzaville', 'Pointe-Noire', 'Dolisie']],
            'Djibouti' => ['code' => 'DJ', 'cities' => ['Djibouti', 'Ali Sabieh']],
            'Egypt' => ['code' => 'EG', 'cities' => ['Cairo', 'Alexandria', 'Giza', 'Luxor', 'Aswan', 'Port Said']],
            'Equatorial Guinea' => ['code' => 'GQ', 'cities' => ['Malabo', 'Bata']],
            'Eritrea' => ['code' => 'ER', 'cities' => ['Asmara', 'Keren', 'Massawa']],
            'Eswatini' => ['code' => 'SZ', 'cities' => ['Mbabane', 'Manzini', 'Lobamba']],
            'Ethiopia' => ['code' => 'ET', 'cities' => ['Addis Ababa', 'Dire Dawa', 'Mekelle', 'Gondar']],
            'Gabon' => ['code' => 'GA', 'cities' => ['Libreville', 'Port-Gentil', 'Franceville']],
            'Gambia' => ['code' => 'GM', 'cities' => ['Banjul', 'Serekunda', 'Brikama']],
            'Ghana' => ['code' => 'GH', 'cities' => ['Accra', 'Kumasi', 'Tamale', 'Takoradi']],
            'Guinea' => ['code' => 'GN', 'cities' => ['Conakry', 'Nzérékoré', 'Kankan']],
            'Guinea-Bissau' => ['code' => 'GW', 'cities' => ['Bissau', 'Bafatá', 'Gabú']],
            'Ivory Coast' => ['code' => 'CI', 'cities' => ['Yamoussoukro', 'Abidjan', 'Bouaké', 'Daloa']],
            'Kenya' => ['code' => 'KE', 'cities' => ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret']],
            'Lesotho' => ['code' => 'LS', 'cities' => ['Maseru', 'Teyateyaneng', 'Mafeteng']],
            'Liberia' => ['code' => 'LR', 'cities' => ['Monrovia', 'Gbarnga', 'Buchanan']],
            'Libya' => ['code' => 'LY', 'cities' => ['Tripoli', 'Benghazi', 'Misrata', 'Sabha']],
            'Madagascar' => ['code' => 'MG', 'cities' => ['Antananarivo', 'Toamasina', 'Antsirabe', 'Mahajanga']],
            'Malawi' => ['code' => 'MW', 'cities' => ['Lilongwe', 'Blantyre', 'Mzuzu', 'Zomba']],
            'Mali' => ['code' => 'ML', 'cities' => ['Bamako', 'Sikasso', 'Mopti', 'Gao']],
            'Mauritania' => ['code' => 'MR', 'cities' => ['Nouakchott', 'Nouadhibou', 'Kaédi']],
            'Mauritius' => ['code' => 'MU', 'cities' => ['Port Louis', 'Curepipe', 'Vacoas']],
            'Morocco' => ['code' => 'MA', 'cities' => ['Rabat', 'Casablanca', 'Marrakesh', 'Fez', 'Tangier', 'Agadir']],
            'Mozambique' => ['code' => 'MZ', 'cities' => ['Maputo', 'Matola', 'Beira', 'Nampula']],
            'Namibia' => ['code' => 'NA', 'cities' => ['Windhoek', 'Walvis Bay', 'Swakopmund']],
            'Niger' => ['code' => 'NE', 'cities' => ['Niamey', 'Zinder', 'Maradi']],
            'Nigeria' => ['code' => 'NG', 'cities' => ['Abuja', 'Lagos', 'Kano', 'Ibadan', 'Port Harcourt', 'Benin City']],
            'Rwanda' => ['code' => 'RW', 'cities' => ['Kigali', 'Butare', 'Gisenyi']],
            'São Tomé and Príncipe' => ['code' => 'ST', 'cities' => ['São Tomé', 'Santo António']],
            'Senegal' => ['code' => 'SN', 'cities' => ['Dakar', 'Touba', 'Thiès', 'Saint-Louis']],
            'Seychelles' => ['code' => 'SC', 'cities' => ['Victoria', 'Anse Boileau']],
            'Sierra Leone' => ['code' => 'SL', 'cities' => ['Freetown', 'Bo', 'Kenema', 'Makeni']],
            'Somalia' => ['code' => 'SO', 'cities' => ['Mogadishu', 'Hargeisa', 'Kismayo', 'Bosaso']],
            'South Africa' => ['code' => 'ZA', 'cities' => ['Pretoria', 'Cape Town', 'Johannesburg', 'Durban', 'Port Elizabeth', 'Bloemfontein']],
            'South Sudan' => ['code' => 'SS', 'cities' => ['Juba', 'Malakal', 'Wau']],
            'Sudan' => ['code' => 'SD', 'cities' => ['Khartoum', 'Omdurman', 'Port Sudan', 'Kassala']],
            'Tanzania' => ['code' => 'TZ', 'cities' => ['Dodoma', 'Dar es Salaam', 'Mwanza', 'Arusha', 'Zanzibar City']],
            'Togo' => ['code' => 'TG', 'cities' => ['Lomé', 'Sokodé', 'Kara']],
            'Tunisia' => ['code' => 'TN', 'cities' => ['Tunis', 'Sfax', 'Sousse', 'Kairouan']],
            'Uganda' => ['code' => 'UG', 'cities' => ['Kampala', 'Gulu', 'Mbarara', 'Jinja']],
            'Zambia' => ['code' => 'ZM', 'cities' => ['Lusaka', 'Kitwe', 'Ndola', 'Kabwe']],
            'Zimbabwe' => ['code' => 'ZW', 'cities' => ['Harare', 'Bulawayo', 'Chitungwiza', 'Mutare']],

            // ===== North America =====
            'Antigua and Barbuda' => ['code' => 'AG', 'cities' => ['St. John\'s', 'All Saints']],
            'Bahamas' => ['code' => 'BS', 'cities' => ['Nassau', 'Freeport']],
            'Barbados' => ['code' => 'BB', 'cities' => ['Bridgetown', 'Speightstown']],
            'Belize' => ['code' => 'BZ', 'cities' => ['Belmopan', 'Belize City', 'San Ignacio']],
            'Canada' => ['code' => 'CA', 'cities' => ['Ottawa', 'Toronto', 'Montreal', 'Vancouver', 'Calgary', 'Edmonton', 'Quebec City']],
            'Costa Rica' => ['code' => 'CR', 'cities' => ['San José', 'Alajuela', 'Cartago', 'Heredia']],
            'Cuba' => ['code' => 'CU', 'cities' => ['Havana', 'Santiago de Cuba', 'Camagüey', 'Holguín']],
            'Dominica' => ['code' => 'DM', 'cities' => ['Roseau', 'Portsmouth']],
            'Dominican Republic' => ['code' => 'DO', 'cities' => ['Santo Domingo', 'Santiago', 'La Romana', 'Punta Cana']],
            'El Salvador' => ['code' => 'SV', 'cities' => ['San Salvador', 'Santa Ana', 'San Miguel']],
            'Grenada' => ['code' => 'GD', 'cities' => ['St. George\'s', 'Gouyave']],
            'Guatemala' => ['code' => 'GT', 'cities' => ['Guatemala City', 'Quetzaltenango', 'Antigua Guatemala']],
            'Haiti' => ['code' => 'HT', 'cities' => ['Port-au-Prince', 'Cap-Haïtien', 'Les Cayes']],
            'Honduras' => ['code' => 'HN', 'cities' => ['Tegucigalpa', 'San Pedro Sula', 'La Ceiba']],
            'Jamaica' => ['code' => 'JM', 'cities' => ['Kingston', 'Montego Bay', 'Spanish Town']],
            'Mexico' => ['code' => 'MX', 'cities' => ['Mexico City', 'Guadalajara', 'Monterrey', 'Puebla', 'Tijuana', 'Cancún', 'Mérida']],
            'Nicaragua' => ['code' => 'NI', 'cities' => ['Managua', 'León', 'Granada', 'Masaya']],
            'Panama' => ['code' => 'PA', 'cities' => ['Panama City', 'Colón', 'David']],
            'Saint Kitts and Nevis' => ['code' => 'KN', 'cities' => ['Basseterre', 'Charlestown']],
            'Saint Lucia' => ['code' => 'LC', 'cities' => ['Castries', 'Vieux Fort']],
            'Saint Vincent and the Grenadines' => ['code' => 'VC', 'cities' => ['Kingstown', 'Georgetown']],
            'Trinidad and Tobago' => ['code' => 'TT', 'cities' => ['Port of Spain', 'San Fernando', 'Chaguanas']],
            'United States' => ['code' => 'US', 'cities' => ['Washington', 'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'San Francisco', 'Miami', 'Seattle']],

            // ===== South America =====
            'Argentina' => ['code' => 'AR', 'cities' => ['Buenos Aires', 'Córdoba', 'Rosario', 'Mendoza', 'La Plata', 'Mar del Plata']],
            'Bolivia' => ['code' => 'BO', 'cities' => ['La Paz', 'Santa Cruz de la Sierra', 'Cochabamba', 'Sucre']],
            'Brazil' => ['code' => 'BR', 'cities' => ['Brasília', 'São Paulo', 'Rio de Janeiro', 'Salvador', 'Fortaleza', 'Belo Horizonte', 'Curitiba', 'Recife']],
            'Chile' => ['code' => 'CL', 'cities' => ['Santiago', 'Valparaíso', 'Concepción', 'Antofagasta', 'Viña del Mar']],
            'Colombia' => ['code' => 'CO', 'cities' => ['Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Cartagena']],
            'Ecuador' => ['code' => 'EC', 'cities' => ['Quito', 'Guayaquil', 'Cuenca', 'Santo Domingo']],
            'Guyana' => ['code' => 'GY', 'cities' => ['Georgetown', 'Linden', 'New Amsterdam']],
            'Paraguay' => ['code' => 'PY', 'cities' => ['Asunción', 'Ciudad del Este', 'Encarnación']],
            'Peru' => ['code' => 'PE', 'cities' => ['Lima', 'Arequipa', 'Trujillo', 'Cusco', 'Chiclayo']],
            'Suriname' => ['code' => 'SR', 'cities' => ['Paramaribo', 'Lelydorp', 'Nieuw Nickerie']],
            'Uruguay' => ['code' => 'UY', 'cities' => ['Montevideo', 'Salto', 'Punta del Este', 'Paysandú']],
            'Venezuela' => ['code' => 'VE', 'cities' => ['Caracas', 'Maracaibo', 'Valencia', 'Barquisimeto', 'Maracay']],

            // ===== Oceania =====
            'Australia' => ['code' => 'AU', 'cities' => ['Canberra', 'Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide', 'Gold Coast']],
            'Fiji' => ['code' => 'FJ', 'cities' => ['Suva', 'Nadi', 'Lautoka']],
            'Kiribati' => ['code' => 'KI', 'cities' => ['Tarawa', 'Betio']],
            'Marshall Islands' => ['code' => 'MH', 'cities' => ['Majuro', 'Ebeye']],
            'Micronesia' => ['code' => 'FM', 'cities' => ['Palikir', 'Weno', 'Kolonia']],
            'Nauru' => ['code' => 'NR', 'cities' => ['Yaren']],
            'New Zealand' => ['code' => 'NZ', 'cities' => ['Wellington', 'Auckland', 'Christchurch', 'Hamilton', 'Dunedin', 'Queenstown']],
            'Palau' => ['code' => 'PW', 'cities' => ['Ngerulmud', 'Koror']],
            'Papua New Guinea' => ['code' => 'PG', 'cities' => ['Port Moresby', 'Lae', 'Mount Hagen']],
            'Samoa' => ['code' => 'WS', 'cities' => ['Apia', 'Asau']],
            'Solomon Islands' => ['code' => 'SB', 'cities' => ['Honiara', 'Auki', 'Gizo']],
            'Tonga' => ['code' => 'TO', 'cities' => ['Nukuʻalofa', 'Neiafu']],
            'Tuvalu' => ['code' => 'TV', 'cities' => ['Funafuti']],
            'Vanuatu' => ['code' => 'VU', 'cities' => ['Port Vila', 'Luganville']],
        ];
    }
}
