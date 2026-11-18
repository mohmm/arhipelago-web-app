<?php

use App\Models\PointOfInterestCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('point_of_interest_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name_ro');
            $table->string('icon', 50)->nullable()->after('image_url');
            $table->string('color', 7)->nullable()->after('icon');
            $table->boolean('is_active')->default(true)->after('color');
            $table->unsignedInteger('sort_order')->default(0)->after('is_active');
        });

        PointOfInterestCategory::withTrashed()->orderBy('id')->get()->each(function (PointOfInterestCategory $category): void {
            if (! empty($category->slug)) {
                return;
            }

            $base = Str::slug((string) $category->name_en) ?: 'category-'.$category->id;
            $slug = $base;
            $suffix = 1;

            while (PointOfInterestCategory::withTrashed()->where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $suffix++;
                $slug = $base.'-'.$suffix;
            }

            $category->slug = $slug;
            $category->sort_order = $category->id;
            $category->saveQuietly();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_of_interest_categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'icon', 'color', 'is_active', 'sort_order']);
        });
    }
};
