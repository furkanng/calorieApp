<?php

namespace App\Console\Commands;

use App\Jobs\TranslateFoodNameJob;
use App\Models\Food;
use Illuminate\Console\Command;

class TranslateFoodNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'foods:translate {--country_id= : Belirli bir ülke ID\'si için çeviri yap}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DB\'deki yemek isimlerini AI kullanarak diğer dillere çevirir';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $countryId = $this->option('country_id');

        // Tüm dilleri al (storage/app/foods altındaki dosyalardan)
        $targetLanguages = ['tr', 'en', 'de', 'ru'];

        $query = Food::query();

        if ($countryId) {
            $query->where('country_id', $countryId);
            $this->info("Ülke ID {$countryId} için çeviri başlatılıyor...");
        } else {
            $this->info("Tüm yemekler için çeviri başlatılıyor...");
        }

        // Tüm yemekleri al
        $foods = $query->get();

        $totalJobs = 0;
        $bar = $this->output->createProgressBar($foods->count());
        $bar->start();

        foreach ($foods as $food) {

            // Job'u kuyruğa ekle
            TranslateFoodNameJob::dispatch($food, $targetLanguages);
            $totalJobs++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Toplam {$totalJobs} job kuyruğa eklendi. Çeviriler arka planda yapılacak.");
        $this->info("Kuyruğu çalıştırmak için: php artisan queue:work");
    }
}
