<?php

namespace Database\Factories;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Destination>
 */
class DestinationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'slug' => Str::slug($name),
            'name_ro' => $name,
            'name_en' => $name,
            'name_ar' => $name,
            'region_ro' => "comuna {$name}, Brașov",
            'region_en' => "{$name}, Brașov County",
            'region_ar' => $name,
            'sort_order' => 0,
        ];
    }
}
