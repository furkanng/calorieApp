<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    protected $table = "foods";

    protected $hidden = ['created_at','updated_at','country_id'];

    protected $fillable = [
        'country_id','language','name',
        'calories','protein','fat','carbs',
        'sugar','fiber','sodium','vitamins_json','image_url'
    ];

    protected $casts = [
        'vitamins_json' => 'array',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
