<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\County;
use App\Models\City;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $counties = County::all();
        
        // Sample cities for each county (using Hungarian county names and cities)
        $citiesData = [
            'Bács-Kiskun' => ['Baja', 'Kecskemét', 'Kiskunfélegyháza', 'Kalocsa', 'Kiskunhalas'],
            'Baranya' => ['Pécs', 'Mohács', 'Pécsvárad', 'Siklós', 'Sellye'],
            'Békés' => ['Békéscsaba', 'Gyula', 'Orosháza', 'Békés', 'Mezőkovácsháza'],
            'Borsod-Abaúj-Zemplén' => ['Miskolc', 'Ózd', 'Kazincbarcika', 'Sátoraljaújhely', 'Mezőkövesd'],
            'Csongrád-Csanád' => ['Szeged', 'Hódmezővásárhely', 'Makó', 'Szentes', 'Csongrád'],
            'Fejér' => ['Székesfehérvár', 'Dunaújváros', 'Mór', 'Gárdony', 'Bicske'],
            'Győr-Moson-Sopron' => ['Győr', 'Sopron', 'Mosonmagyaróvár', 'Csorna', 'Kapuvár'],
            'Hajdú-Bihar' => ['Debrecen', 'Hajdúböszörmény', 'Hajdúnánás', 'Berettyóújfalu', 'Püspökladány'],
            'Heves' => ['Eger', 'Hatvan', 'Gyöngyös', 'Heves', 'Füzesabony'],
            'Jász-Nagykun-Szolnok' => ['Szolnok', 'Jászberény', 'Karcag', 'Törökszentmiklós', 'Mezőtúr'],
            'Komárom-Esztergom' => ['Tatabánya', 'Esztergom', 'Komárom', 'Tata', 'Oroszlány'],
            'Nógrád' => ['Salgótarján', 'Balassagyarmat', 'Pásztó', 'Rétság', 'Szécsény'],
            'Pest' => ['Budapest', 'Érd', 'Gödöllő', 'Vác', 'Dunakeszi', 'Cegléd', 'Vecsés', 'Aszód'],
            'Somogy' => ['Kaposvár', 'Siófok', 'Marcali', 'Barcs', 'Fonyód'],
            'Szabolcs-Szatmár-Bereg' => ['Nyíregyháza', 'Kisvárda', 'Mátészalka', 'Nyírbátor', 'Fehérgyarmat'],
            'Tolna' => ['Szekszárd', 'Dombóvár', 'Paks', 'Tamási', 'Bonyhád'],
            'Vas' => ['Szombathely', 'Sárvár', 'Körmend', 'Celldömölk', 'Vasvár'],
            'Veszprém' => ['Veszprém', 'Ajka', 'Várpalota', 'Pápa', 'Balatonfüred'],
            'Zala' => ['Zalaegerszeg', 'Nagykanizsa', 'Keszthely', 'Lenti', 'Zalaszentgrót'],
        ];

        foreach ($counties as $county) {
            if (isset($citiesData[$county->name])) {
                foreach ($citiesData[$county->name] as $cityName) {
                    City::create([
                        'county_id' => $county->id,
                        'name' => $cityName,
                    ]);
                }
            }
        }
    }
}
