<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $table = "countries";

    protected $fillable = ['name','iso_code'];

    public function foods()
    {
        return $this->hasMany(Food::class);
    }
}
