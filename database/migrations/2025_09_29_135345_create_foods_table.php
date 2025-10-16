<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->integer('country_id');
            $table->string('language', 5); // tr, en, de...
            $table->string('name');        // yemek adı
            $table->integer('calories')->nullable();
            $table->decimal('protein', 5,2)->nullable();
            $table->decimal('fat', 5,2)->nullable();
            $table->decimal('carbs', 5,2)->nullable();
            $table->decimal('sugar', 5,2)->nullable();
            $table->decimal('fiber', 5,2)->nullable();
            $table->decimal('sodium', 6,2)->nullable();
            $table->json('vitamins_json')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
