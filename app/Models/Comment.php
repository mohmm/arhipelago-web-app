<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A traveller's comment on a post.
 *
 * Adding or removing one keeps the post's `comments_count` exact.
 *
 * @property int $id
 * @property string $commentable_type
 * @property int $commentable_id
 * @property string $body
 * @property int $created_by_user_id
 */
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'body',
        'created_by_user_id',
    ];

    protected static function booted(): void
    {
        $refresh = function (Comment $comment): void {
            $commentable = $comment->commentable;

            if ($commentable != null && method_exists($commentable, 'refreshCommentsCount')) {
                $commentable->refreshCommentsCount();
            }
        };

        static::created($refresh);
        static::deleted($refresh);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
