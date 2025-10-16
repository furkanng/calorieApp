<?php

namespace App\Jobs;

use App\Models\Food;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenAI\Laravel\Facades\OpenAI;

class FetchNutritionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $food;

    public function __construct(Food $food)
    {
        $this->food = $food;
    }

    public function handle()
    {
        $prompt = "Give nutritional facts for {$this->food->name} (food from {$this->food->country->name}) ".
            "in JSON with keys: calories, protein, fat, carbs, sugar, fiber, sodium, vitamins_json";

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'response_format' => ['type' => 'json_object'],
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ]);

        $data = json_decode($result->choices[0]->message->content ?? '{}', true);

        if ($data) {
            $this->food->update($data);
        }
    }
}
