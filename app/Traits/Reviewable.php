<?php

namespace App\Traits;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Something travellers can rate from one to five stars, once each.
 */
trait Reviewable
{
    /**
     * @return MorphMany<Review, $this>
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * Write the traveller's review, or replace the one they already wrote.
     */
    public function reviewBy(User $user, int $rating, ?string $body): Review
    {
        return $this->reviews()->updateOrCreate(
            ['user_id' => $user->id],
            ['rating' => $rating, 'body' => $body],
        );
    }

    /**
     * Adds `reviews_count` and `reviews_avg_rating` to every row, in the same query.
     *
     * @param  Builder<static>  $query
     */
    public function scopeWithRatingSummary(Builder $query): void
    {
        $query->withCount('reviews')->withAvg('reviews', 'rating');
    }
}
