<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

/**
 * A traveller's review of a point of interest or an experience.
 *
 * Travellers manage their own; staff moderate everyone's through the
 * `review.moderate` and `review.delete` permissions.
 */
class ReviewPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    /**
     * See every review, hidden ones included, and hide or show them.
     */
    public function moderate(User $user): bool
    {
        return $user->checkPermissionTo('review.moderate');
    }

    public function view(User $user, Review $review): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Review $review): bool
    {
        return $review->user_id == $user->id || $user->checkPermissionTo('review.delete');
    }
}
