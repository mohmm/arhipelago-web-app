<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A traveller's reaction to a post or a place. The app shows likes; a few
 * dislikes from the previous app remain stored and are not counted.
 *
 * @property int $id
 * @property int $user_id
 * @property string $reaction_type
 */
class Reaction extends Model
{
    public const LIKE = 'like';

    public const DISLIKE = 'dislike';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'reaction_type',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reactionable(): MorphTo
    {
        return $this->morphTo();
    }
}
