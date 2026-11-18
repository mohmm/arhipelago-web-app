<?php

namespace Database\Factories;

use App\Enums\ExperienceKind;
use App\Models\Destination;
use App\Models\Experience;
use App\Models\PointOfInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'destination_id' => Destination::factory(),
            'kind' => ExperienceKind::Classic,
            'travel_mode' => ExperienceKind::Classic->travelMode(),
            'description_ro' => fake()->paragraph(),
            'description_en' => fake()->paragraph(),
            'description_ar' => fake()->paragraph(),
            'description_ai_generated' => true,
            'is_published' => true,
        ];
    }

    public function kind(ExperienceKind $kind): static
    {
        return $this->state(['kind' => $kind, 'travel_mode' => $kind->travelMode()]);
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }

    /**
     * A measured route through the given places, in order: one kilometre and two
     * minutes between consecutive stops.
     *
     * @param  iterable<PointOfInterest>  $pointsOfInterest
     */
    public function through(iterable $pointsOfInterest): static
    {
        return $this->afterCreating(function (Experience $experience) use ($pointsOfInterest): void {
            $position = 0;

            foreach ($pointsOfInterest as $pointOfInterest) {
                $experience->stops()->create([
                    'point_of_interest_id' => $pointOfInterest->id,
                    'position' => $position,
                    'distance_from_start_m' => $position * 1000,
                    'duration_from_start_s' => $position * 120,
                ]);
                $position++;
            }

            $experience->forceFill([
                'distance_m' => max(0, $position - 1) * 1000,
                'duration_s' => max(0, $position - 1) * 120,
            ])->save();
        });
    }
}
