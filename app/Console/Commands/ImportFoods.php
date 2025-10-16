<?php

namespace App\Console\Commands;

use App\Jobs\FetchNutritionJob;
use App\Models\Country;
use App\Models\Food;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportFoods extends Command
{
    protected $signature = 'foods:import';
    protected $description = 'storage/app/foods altındaki tüm JSON dosyalarını okuyup AI ile kaydeder';

    public function handle()
    {
        $files = Storage::files('foods');

        foreach ($files as $file) {
            $lang = pathinfo($file, PATHINFO_FILENAME);

            $country = Country::where('iso_code', strtoupper($lang))->first();

            $foods = json_decode(Storage::get($file), true);

            foreach ($foods as $name) {
                $food = Food::firstOrCreate([
                    'country_id' => $country->id,
                    'language'   => $lang,
                    'name'       => $name,
                ]);

                // Nutrition fetch job dispatch
                FetchNutritionJob::dispatch($food);
            }

            $this->info("{$lang} için yemekler kuyruğa atıldı.");
        }
    }
}
