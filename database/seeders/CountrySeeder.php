<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Country::query()->truncate();

        $data = [
            ['name' => 'Turkey', 'iso_code' => 'TR'],
            ['name' => 'Germany', 'iso_code' => 'DE'],
            ['name' => 'Italy', 'iso_code' => 'IT'],
            ['name' => 'France', 'iso_code' => 'FR'],
            ['name' => 'Spain', 'iso_code' => 'ES'],
            ['name' => 'United States', 'iso_code' => 'US'],
            ['name' => 'Japan', 'iso_code' => 'JP'],
            ['name' => 'China', 'iso_code' => 'CN'],
            ['name' => 'India', 'iso_code' => 'IN'],
            ['name' => 'Brazil', 'iso_code' => 'BR'],
        ];

        foreach ($data as $datum) {
            Country::query()->create($datum);
        }
    }
}
