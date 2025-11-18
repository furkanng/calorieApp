<?php

namespace App\Jobs;

use App\Models\Food;
use App\Models\FoodTranslation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class TranslateFoodNameJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $food;
    public $targetLanguages;

    /**
     * Create a new job instance.
     */
    public function __construct(Food $food, array $targetLanguages = ['tr', 'en', 'de'])
    {
        $this->food = $food;
        $this->targetLanguages = $targetLanguages;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("TranslateFoodNameJob başladı", [
            'food_id' => $this->food->id,
            'food_name' => $this->food->name,
            'language' => $this->food->language,
            'country_id' => $this->food->country_id,
        ]);

        $translationsCreated = 0;

        // Mevcut dil hariç diğer dilleri kontrol et
        foreach ($this->targetLanguages as $targetLang) {
            // Aynı dildeyse atla
            if ($targetLang === $this->food->language) {
                Log::info("Aynı dil, atlanıyor", ['language' => $targetLang]);
                continue;
            }

            // Bu food için bu dilde çeviri zaten varsa atla
            $existingTranslation = FoodTranslation::where('food_id', $this->food->id)
                ->where('language', $targetLang)
                ->first();

            if ($existingTranslation) {
                Log::info("Çeviri zaten mevcut, atlanıyor", [
                    'food_id' => $this->food->id,
                    'language' => $targetLang,
                ]);
                continue;
            }

            Log::info("AI'dan çeviri isteniyor", [
                'source_name' => $this->food->name,
                'source_lang' => $this->food->language,
                'target_lang' => $targetLang,
            ]);

            // AI'dan çeviri iste
            $translatedName = $this->translateName($this->food->name, $this->food->language, $targetLang);

            if ($translatedName) {
                Log::info("Çeviri alındı, kaydediliyor", [
                    'translated_name' => $translatedName,
                    'food_id' => $this->food->id,
                    'language' => $targetLang,
                ]);

                // Çeviriyi food_translations tablosuna kaydet (mevcut food kaydı için)
                FoodTranslation::updateOrCreate(
                    [
                        'food_id' => $this->food->id,
                        'language' => $targetLang,
                    ],
                    [
                        'name' => $translatedName,
                    ]
                );
                $translationsCreated++;
            } else {
                Log::warning("Çeviri alınamadı", [
                    'source_name' => $this->food->name,
                    'target_lang' => $targetLang,
                ]);
            }
        }

        Log::info("TranslateFoodNameJob tamamlandı", [
            'food_id' => $this->food->id,
            'translations_created' => $translationsCreated,
        ]);
    }

    /**
     * AI kullanarak yemek ismini çevir
     */
    private function translateName(string $name, string $sourceLang, string $targetLang): ?string
    {
        $langNames = [
            'tr' => 'Turkish',
            'en' => 'English',
            'de' => 'German',
            'ru' => 'Russian',
        ];

        $sourceLangName = $langNames[$sourceLang] ?? $sourceLang;
        $targetLangName = $langNames[$targetLang] ?? $targetLang;

        $prompt = "Translate the food name '{$name}' from {$sourceLangName} to {$targetLangName}. " .
            "Return only the translated name, nothing else. " .
            "If it's a traditional food name that should not be translated, return the original name.";

        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);

            $translatedName = trim($result->choices[0]->message->content ?? '');

            return !empty($translatedName) ? $translatedName : null;
        } catch (\Exception $e) {
            Log::error("Translation failed for {$name}: " . $e->getMessage());
            return null;
        }
    }
}
