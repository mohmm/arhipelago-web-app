<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Experiences: day routes with stops at points of interest.
 *
 * A destination has at most one experience of each kind. The distance, the
 * duration and the line on the map are measured on real roads by the router
 * and stored, so the app never waits for a routing call.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('destination_id')->constrained()->restrictOnDelete();
            // classic | family | car_free | winter
            $table->string('kind', 20);
            // car | foot
            $table->string('travel_mode', 10);

            foreach (['ro', 'en', 'ar'] as $locale) {
                $table->text("description_{$locale}")->nullable();
            }

            // Shown as "Text scris cu AI" next to the description.
            $table->boolean('description_ai_generated')->default(false);
            $table->unsignedInteger('distance_m')->default(0);
            $table->unsignedInteger('duration_s')->default(0);
            // The route as a polyline with six-decimal precision.
            $table->longText('route_polyline')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['destination_id', 'kind']);
            $table->index(['is_published', 'kind']);
        });

        Schema::create('experience_stops', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
            // A place cannot be deleted while a route still stops there.
            $table->foreignId('point_of_interest_id')->constrained('points_of_interest')->restrictOnDelete();
            $table->unsignedSmallInteger('position');
            // Along the route, from the first stop: "la 510 m de start · 1 min".
            $table->unsignedInteger('distance_from_start_m')->default(0);
            $table->unsignedInteger('duration_from_start_s')->default(0);
            $table->timestamps();

            $table->unique(['experience_id', 'position']);
            $table->unique(['experience_id', 'point_of_interest_id']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('experience_id')->nullable()->after('point_of_interest_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('experience_id');
        });

        Schema::dropIfExists('experience_stops');
        Schema::dropIfExists('experiences');
    }
};
