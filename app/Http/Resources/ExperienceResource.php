<?php

namespace App\Http\Resources;

use App\Models\Experience;
use App\Models\ExperienceStop;
use Illuminate\Http\Request;

/**
 * The page of one experience: the route, its description, every stop with how
 * far along it is, and the other kinds of day the same destination offers.
 *
 * @mixin Experience
 */
class ExperienceResource extends ExperienceCardResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'description' => $this->translated_description,
            'description_ai_generated' => $this->description_ai_generated,
            'stops' => $this->whenLoaded('stops', fn () => $this->stops->map(fn (ExperienceStop $stop): array => [
                'position' => $stop->position + 1,
                'distance_from_start_m' => $stop->distance_from_start_m,
                'duration_from_start_s' => $stop->duration_from_start_s,
                'point_of_interest' => PointOfInterestCardResource::make($stop->pointOfInterest),
            ])->values()),
            'siblings' => $this->whenLoaded('destination', fn () => $this->destination->relationLoaded('experiences')
                ? $this->destination->experiences->map(fn (Experience $sibling): array => [
                    'id' => $sibling->id,
                    'kind' => $sibling->kind->value,
                    'title' => $sibling->title(),
                ])->values()
                : []),
            'reviews' => [
                'count' => (int) ($this->reviews_count ?? 0),
                'average' => $this->reviews_avg_rating == null ? null : round((float) $this->reviews_avg_rating, 1),
            ],
        ];
    }
}
