<?php

namespace App\Http\Resources;

use App\Models\PointOfInterest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A point of interest as a list row, a map marker or a card in Maya's reply.
 *
 * @mixin PointOfInterest
 */
class PointOfInterestCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->translated_name,
            'type' => $this->translated_type,
            'locality' => $this->translated_locality,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'is_historic_monument' => $this->is_historic_monument,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'cover' => $this->whenLoaded('media', fn () => $this->coverPhoto() == null ? null : PhotoResource::make($this->coverPhoto())),
            'experiences_count' => $this->whenCounted('experiences'),
            'likes_count' => $this->likes_count,
        ];
    }
}
