<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * The signed-in traveller's own profile, with what only they may see.
 *
 * @mixin User
 */
class MeResource extends ProfileResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'email' => $this->email,
            'email_verified' => $this->email_verified_at != null,
            'locale' => $this->locale,
            // "Ce știe Maya despre tine": what Maya reads before recommending anything.
            'travel_preferences' => $this->travel_preferences,
        ];
    }
}
