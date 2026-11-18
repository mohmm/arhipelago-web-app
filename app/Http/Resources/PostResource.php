<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesViewer;
use App\Models\ExperienceStop;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A post in the community feed.
 *
 * @mixin Post
 */
class PostResource extends JsonResource
{
    use ResolvesViewer;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $this->viewer($request);

        return [
            'id' => $this->id,
            'body' => $this->body,
            'image_url' => $this->absoluteUrl($this->image_url),
            'created_at' => $this->created_at?->toIso8601String(),
            'author' => UserSummaryResource::make($this->whenLoaded('user')),
            'point_of_interest' => $this->whenLoaded('pointOfInterest', fn () => $this->pointOfInterest == null ? null : [
                'id' => $this->pointOfInterest->id,
                'name' => $this->pointOfInterest->translated_name,
                'type' => $this->pointOfInterest->translated_type,
                'locality' => $this->pointOfInterest->translated_locality,
                // "prima oprire pe Traseul clasic", when the post names both.
                'stop_position' => $this->stopPosition(),
            ]),
            'experience' => $this->whenLoaded('experience', fn () => $this->experience == null ? null : [
                'id' => $this->experience->id,
                'kind' => $this->experience->kind->value,
                'title' => $this->experience->title(),
                'full_title' => $this->experience->relationLoaded('destination') ? $this->experience->fullTitle() : null,
            ]),
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'liked_by_viewer' => (bool) ($this->liked_by_viewer ?? false),
            'can_delete' => $viewer?->can('delete', $this->resource) ?? false,
        ];
    }

    private function stopPosition(): ?int
    {
        if ($this->experience == null || ! $this->experience->relationLoaded('stops')) {
            return null;
        }

        $stop = $this->experience->stops->first(fn (ExperienceStop $stop): bool => $stop->point_of_interest_id == $this->point_of_interest_id);

        return $stop == null ? null : $stop->position + 1;
    }
}
