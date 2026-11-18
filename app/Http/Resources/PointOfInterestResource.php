<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesViewer;
use App\Models\PointOfInterest;
use Illuminate\Http\Request;

/**
 * The page of one point of interest: everything on the card, plus the full
 * text, the photo gallery and the experiences that stop there.
 *
 * @mixin PointOfInterest
 */
class PointOfInterestResource extends PointOfInterestCardResource
{
    use ResolvesViewer;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'address' => $this->translated_address,
            'description' => $this->translated_description,
            'website' => $this->website,
            'photos' => PhotoResource::collection($this->getMedia(PointOfInterest::PHOTOS)),
            'liked_by_viewer' => $this->isLikedBy($this->viewer($request)),
            'reviews' => [
                'count' => (int) ($this->reviews_count ?? 0),
                'average' => $this->reviews_avg_rating == null ? null : round((float) $this->reviews_avg_rating, 1),
            ],
            'posts_count' => $this->whenCounted('posts'),
            'experiences' => ExperienceCardResource::collection($this->whenLoaded('experiences')),
        ];
    }
}
