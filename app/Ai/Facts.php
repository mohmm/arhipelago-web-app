<?php

namespace App\Ai;

use App\Models\Experience;
use App\Models\ExperienceStop;
use App\Models\PointOfInterest;
use Illuminate\Support\Str;

/**
 * What Maya is told about a place or an experience: the app's own data, in the
 * traveller's language, and nothing else. Every fact she may repeat comes
 * from here, which is what lets her say "I don't know" for everything else.
 */
class Facts
{
    /**
     * @return array<string, mixed>
     */
    public static function pointOfInterest(PointOfInterest $place, bool $detailed = false): array
    {
        $facts = [
            'id' => $place->id,
            'name' => $place->translated_name,
            'type' => $place->translated_type,
            'category' => $place->category?->translated_name,
            'locality' => $place->translated_locality,
            'is_historic_monument' => $place->is_historic_monument,
            'summary' => Str::limit((string) $place->translated_description, 220),
        ];

        if (! $detailed) {
            return $facts;
        }

        return [
            ...$facts,
            'address' => $place->translated_address,
            'description' => $place->translated_description,
            'website' => $place->website,
            'latitude' => $place->latitude,
            'longitude' => $place->longitude,
            'likes' => $place->likes_count,
            'experiences_stopping_here' => $place->experiences
                ->map(fn (Experience $experience): array => [
                    'experience_id' => $experience->id,
                    'title' => $experience->fullTitle(),
                    'stop_number' => $experience->pivot->position + 1,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function experience(Experience $experience, bool $detailed = false): array
    {
        $facts = [
            'id' => $experience->id,
            'title' => $experience->fullTitle(),
            'kind' => $experience->kind->value,
            'travel_mode' => $experience->travel_mode->value,
            'distance_km' => round($experience->distance_m / 1000, 1),
            'duration_minutes' => (int) round($experience->duration_s / 60),
            'stops' => $experience->stops
                ->map(fn (ExperienceStop $stop): string => (string) $stop->pointOfInterest?->translated_name)
                ->values()
                ->all(),
        ];

        if (! $detailed) {
            return $facts;
        }

        return [
            ...$facts,
            'area' => $experience->destination->translated_region,
            'description' => $experience->translated_description,
            'stops' => $experience->stops
                ->map(fn (ExperienceStop $stop): array => [
                    'stop_number' => $stop->position + 1,
                    'point_of_interest_id' => $stop->point_of_interest_id,
                    'name' => $stop->pointOfInterest?->translated_name,
                    'type' => $stop->pointOfInterest?->translated_type,
                    'km_from_start' => round($stop->distance_from_start_m / 1000, 1),
                    'minutes_from_start' => (int) round($stop->duration_from_start_s / 60),
                ])
                ->values()
                ->all(),
            'other_kinds_in_this_area' => $experience->destination->experiences
                ->reject(fn (Experience $sibling): bool => $sibling->is($experience))
                ->map(fn (Experience $sibling): array => ['experience_id' => $sibling->id, 'title' => $sibling->fullTitle()])
                ->values()
                ->all(),
        ];
    }
}
