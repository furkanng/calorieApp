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
        'sugar','fiber','sodium','vitamins_json','image_url',
        'gluten','dairy','nuts'
    ];

    protected $casts = [
        'vitamins_json' => 'array',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // Aynı ülkeye ait diğer dil versiyonlarını getir (eski - kullanılmıyor)
    public function translations()
    {
        return $this->hasMany(Food::class, 'country_id', 'country_id')
            ->where('id', '!=', $this->id);
    }

    // Çevirileri getir
    public function nameTranslations()
    {
        return $this->hasMany(FoodTranslation::class);
    }
}
