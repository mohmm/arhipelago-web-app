<?php

namespace App\Http\Resources;

use App\Models\Experience;
use App\Models\ExperienceStop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An experience as a list card, a map line or a card in Maya's reply: enough
 * to draw the route with its numbered stops and print "5 opriri · 28,4 km".
 *
 * @mixin Experience
 */
class ExperienceCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kind' => $this->kind->value,
            'title' => $this->title(),
            'full_title' => $this->whenLoaded('destination', fn () => $this->fullTitle()),
            'destination' => DestinationResource::make($this->whenLoaded('destination')),
            'travel_mode' => $this->travel_mode->value,
            'distance_m' => $this->distance_m,
            'duration_s' => $this->duration_s,
            'route_polyline' => $this->route_polyline,
            'stops_count' => $this->whenLoaded('stops', fn () => $this->stops->count()),
            'stops' => $this->whenLoaded('stops', fn () => $this->stops->map(fn (ExperienceStop $stop): array => [
                'position' => $stop->position + 1,
                'point_of_interest_id' => $stop->point_of_interest_id,
                'name' => $stop->pointOfInterest?->translated_name,
                'latitude' => $stop->pointOfInterest?->latitude,
                'longitude' => $stop->pointOfInterest?->longitude,
            ])->values()),
        ];
    }
}
