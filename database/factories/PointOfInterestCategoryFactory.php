<?php

namespace Database\Factories;

use App\Models\PointOfInterestCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PointOfInterestCategory>
 */
class PointOfInterestCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(2, true));

        return [
            'slug' => Str::slug($name),
            'name_ro' => $name,
            'name_en' => $name,
            'name_ar' => $name,
            'is_active' => true,
        ];
    }
}
