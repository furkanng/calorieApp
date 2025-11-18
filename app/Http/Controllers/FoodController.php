<?php

namespace App\Http\Controllers;

use App\Jobs\FetchNutritionJob;
use App\Models\Country;
use App\Models\Food;
use App\Models\FoodTranslation;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function index()
    {
        $foods = Food::with(['country', 'nameTranslations'])->get();

        // Tüm çevirileri food_id ve language'e göre grupla (performans için)
        $allTranslations = FoodTranslation::all()
            ->groupBy('food_id')
            ->map(function ($translations) {
                return $translations->keyBy('language');
            });

        // Her yemeğe diğer dillerdeki isimlerini meta kolonlar olarak ekle
        $foods = $foods->map(function ($food) use ($allTranslations) {
            $foodArray = $food->toArray();

            // Bu yemek için çevirileri al
            $translations = $allTranslations->get($food->id, collect());

            // Tüm dillerdeki isimleri meta kolonlar olarak ekle (name_tr, name_en, name_de vb.)
            foreach ($translations as $translation) {
                $foodArray['name_' . $translation->language] = $translation->name;
            }

            // Kendi dilindeki name'i de meta kolon olarak ekle
            $foodArray['name_' . $food->language] = $food->name;

            // name değerini kendi diline göre ilgili meta kolondan al
            $nameKey = 'name_' . $food->language;
            if (isset($foodArray[$nameKey])) {
                $foodArray['name'] = $foodArray[$nameKey];
            }

            return $foodArray;
        });

        return response()->json([
            "status" => true,
            "Data" => $foods
        ]);

    }

    public function store(Request $request)
    {
        $request->validate([
            'country' => 'required',
            'language' => 'required|string',
            'name' => 'required|string',
            'type' => 'required',
        ]);

        $country = Country::query()->where("iso_code", request('country'))->first();
        $countryId = $country->id;

        $type = $request->get("type");

        if ($type == 1) {

            $food = Food::firstOrCreate([
                'country_id' => $countryId,
                'language' => $request->get("language"),
                'name' => $request->get("name"),
            ]);

            // Nutrition fetch job dispatch
            FetchNutritionJob::dispatch($food);

        } else {

            Food::firstOrCreate([
                'country_id' => $countryId,
                'language' => $request->get("language"),
                'name' => $request->get("name"),
                'calories' => $request->get("calories"),
                'protein' => $request->get("protein"),
                'fat' => $request->get("fat"),
                'carbs' => $request->get("carbs"),
                'sugar' => $request->get("sugar"),
                'fiber' => $request->get("fiber"),
                'sodium' => $request->get("sodium"),
                'gluten' => $request->get("gluten"),
                'dairy' => $request->get("dairy"),
                'nuts' => $request->get("nuts"),
                'vitamins_json' => json_encode($request->get("vitamins_json")),
            ]);

        }

        return response()->json([
            "success" => true,
        ], 201);
    }

    public function show(string $food)
    {
        $food = Food::with(['country', 'nameTranslations'])->where('name', $food)->firstOrFail();

        $foodArray = $food->toArray();

        // Tüm çevirileri meta kolonlar olarak ekle
        foreach ($food->nameTranslations as $translation) {
            $foodArray['name_' . $translation->language] = $translation->name;
        }

        // Kendi dilindeki name'i de meta kolon olarak ekle
        $foodArray['name_' . $food->language] = $food->name;

        // name değerini kendi diline göre ilgili meta kolondan al
        $nameKey = 'name_' . $food->language;
        if (isset($foodArray[$nameKey])) {
            $foodArray['name'] = $foodArray[$nameKey];
        }

        return response()->json([
            "success" => true,
            "data" => $foodArray
        ]);

    }
}
