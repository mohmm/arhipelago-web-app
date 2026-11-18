<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The area an experience is set in, such as Bran or Râșnov.
 *
 * Each destination offers up to one experience of each kind (classic, family,
 * car-free, winter), which is what lets the app switch between them in place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();

            foreach (['ro', 'en', 'ar'] as $locale) {
                // "Bran"
                $table->string("name_{$locale}", 120)->nullable();
                // "comuna Bran, Brașov"
                $table->string("region_{$locale}")->nullable();
            }

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
