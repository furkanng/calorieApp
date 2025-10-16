<?php

use App\Http\Controllers\FoodController;
use App\Jobs\FetchNutritionJob;
use App\Models\Country;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;


Route::post('/foods', [FoodController::class, 'store']);
Route::get('/foods', [FoodController::class, 'index']);
Route::get('/food/{food}', [FoodController::class, 'show']);
