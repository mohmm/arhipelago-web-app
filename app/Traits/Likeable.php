<?php

namespace App\Traits;

use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

/**
 * Something travellers can like (the heart on a post or a place).
 *
 * The model keeps a `likes_count` column, recounted from the reactions on
 * every change so it can never drift.
 */
trait Likeable
{
    /**
     * @return MorphMany<Reaction, $this>
     */
    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactionable');
    }

    /**
     * @return MorphMany<Reaction, $this>
     */
    public function likes(): MorphMany
    {
        return $this->reactions()->where('reaction_type', Reaction::LIKE);
    }

    /**
     * Like it, or take the like back. Returns whether it is liked afterwards.
     *
     * One reaction row per traveller: an older "dislike" from the previous app
     * becomes the like rather than sitting beside it.
     */
    public function toggleLikeBy(User $user): bool
    {
        return DB::transaction(function () use ($user): bool {
            $reaction = $this->reactions()->where('user_id', $user->id)->lockForUpdate()->first();

            $liked = match (true) {
                $reaction == null => (bool) $this->reactions()->create(['user_id' => $user->id, 'reaction_type' => Reaction::LIKE]),
                $reaction->reaction_type == Reaction::LIKE => ! $reaction->delete(),
                default => $reaction->update(['reaction_type' => Reaction::LIKE]),
            };

            $this->forceFill(['likes_count' => $this->likes()->count()])->saveQuietly();

            return $liked;
        });
    }

    public function isLikedBy(?User $user): bool
    {
        return $user != null && $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Adds a `liked_by_viewer` boolean to every row, in the same query.
     *
     * @param  Builder<static>  $query
     */
    public function scopeWithLikedBy(Builder $query, ?User $user): void
    {
        if ($user == null) {
            return;
        }

        $query->withExists(['likes as liked_by_viewer' => fn (Builder $likes) => $likes->where('user_id', $user->id)]);
    }
}
