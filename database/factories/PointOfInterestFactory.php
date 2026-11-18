<?php

namespace Database\Factories;

use App\Models\PointOfInterest;
use App\Models\PointOfInterestCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PointOfInterest>
 */
class PointOfInterestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Castelul '.fake()->unique()->lastName();
        $locality = fake()->city();

        return [
            'category_id' => PointOfInterestCategory::factory(),
            'slug' => Str::slug($name),
            'name_ro' => $name,
            'name_en' => $name,
            'name_ar' => $name,
            'type_ro' => 'Castel',
            'type_en' => 'Castle',
            'type_ar' => 'قلعة',
            'locality_ro' => $locality,
            'locality_en' => $locality,
            'locality_ar' => $locality,
            'address_ro' => "comuna {$locality}, județul Brașov",
            'address_en' => "{$locality}, Brașov County",
            'address_ar' => $locality,
            'description_ro' => fake()->paragraph(),
            'description_en' => fake()->paragraph(),
            'description_ar' => fake()->paragraph(),
            // Somewhere in Romania.
            'latitude' => fake()->latitude(44.0, 47.5),
            'longitude' => fake()->longitude(21.0, 28.0),
            'is_historic_monument' => false,
            'website' => null,
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }

    public function at(float $latitude, float $longitude): static
    {
        return $this->state(['latitude' => $latitude, 'longitude' => $longitude]);
    }
}
