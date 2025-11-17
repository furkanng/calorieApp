<?php

namespace App\Http\Controllers;

use App\Jobs\FetchNutritionJob;
use App\Models\Country;
use App\Models\Food;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function index()
    {
        $foods = Food::all();
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
        $food = Food::query()->where($food,"name")->firstOrFail();

        return response()->json([
            "success" => true,
            "data" => $food
        ]);

    }
}
