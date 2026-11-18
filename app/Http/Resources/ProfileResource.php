<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesViewer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A traveller's public profile: who they are and what their posts gathered.
 *
 * Expects the counts from ProfileQuery, so the numbers come from one query.
 *
 * @mixin User
 */
class ProfileResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->full_name,
            'initials' => $this->initials,
            'avatar_url' => $this->absoluteUrl($this->avatar_url),
            'city' => $this->city,
            'bio' => $this->bio,
            'stats' => [
                'posts' => (int) ($this->posts_count ?? 0),
                'likes' => (int) ($this->likes_received ?? 0),
                'comments' => (int) ($this->comments_received ?? 0),
            ],
            'followers_count' => (int) ($this->followers_count ?? 0),
            'following_count' => (int) ($this->following_count ?? 0),
            'followed_by_viewer' => (bool) ($this->followed_by_viewer ?? false),
            'is_viewer' => $viewer?->is($this->resource) ?? false,
        ];
    }
}
