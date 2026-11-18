<?php

namespace App\Services\Accounts;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Loads what a profile page shows (posts, likes and comments received,
 * followers) onto a user, so ProfileResource reads plain attributes.
 */
class ProfileQuery
{
    public function load(User $user, ?User $viewer = null): User
    {
        $visible = fn (Builder $posts): Builder => $posts->where('is_approved', true);

        $user->loadCount(['posts' => $visible, 'followers', 'following'])
            ->loadSum(['posts as likes_received' => $visible], 'likes_count')
            ->loadSum(['posts as comments_received' => $visible], 'comments_count');

        // Marked as unchanged like the counts above, so saving this user later
        // never tries to write a column that does not exist.
        $user->setAttribute(
            'followed_by_viewer',
            $viewer != null && ! $viewer->is($user) && $viewer->following()->whereKey($user->id)->exists(),
        )->syncOriginalAttribute('followed_by_viewer');

        return $user;
    }
}
