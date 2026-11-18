<?php

namespace App\Models;

use Database\Factories\MayaConversationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * A traveller's conversation with Maya, optionally about one point of interest
 * or experience. A conversation nobody has written in for the period set in
 * config/maya.php is deleted with its messages, as the Privacy Policy states.
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string|null $title
 * @property Carbon|null $last_message_at
 */
class MayaConversation extends Model
{
    /** @use HasFactory<MayaConversationFactory> */
    use HasFactory, MassPrunable;

    /** @var list<string> */
    protected $fillable = [
        'title',
        'last_message_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    /**
     * Conversations left untouched for longer than they are kept. Their
     * messages go with them, through the foreign key.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('last_message_at', '<', now()->subDays((int) config('maya.keep_conversations_for_days')));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The point of interest or experience the conversation is about.
     *
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<MayaMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(MayaMessage::class)->orderBy('id');
    }
}
