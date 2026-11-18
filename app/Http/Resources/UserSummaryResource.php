<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesViewer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Who wrote a post, a comment or a review.
 *
 * @mixin User
 */
class UserSummaryResource extends JsonResource
{
    use ResolvesViewer;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->full_name,
            'initials' => $this->initials,
            'avatar_url' => $this->absoluteUrl($this->avatar_url),
        ];
    }
}
