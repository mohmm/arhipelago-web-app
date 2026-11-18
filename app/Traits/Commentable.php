<?php

namespace App\Traits;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Something travellers can comment on.
 *
 * The model keeps a `comments_count` column; Comment refreshes it whenever a
 * comment is added or removed.
 */
trait Commentable
{
    /**
     * @return MorphMany<Comment, $this>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function refreshCommentsCount(): void
    {
        $this->forceFill(['comments_count' => $this->comments()->count()])->saveQuietly();
    }
}
