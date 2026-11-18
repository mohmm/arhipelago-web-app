<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One message in a conversation with Maya.
 *
 * Maya's replies can carry cards (the places and experiences she talks about)
 * and suggested follow-up questions.
 *
 * @property int $id
 * @property int $maya_conversation_id
 * @property string $role
 * @property string $content
 * @property list<array{type: string, id: int}>|null $attachments
 * @property list<string>|null $suggestions
 */
class MayaMessage extends Model
{
    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    /** @var list<string> */
    protected $fillable = [
        'role',
        'content',
        'attachments',
        'suggestions',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'suggestions' => 'array',
        ];
    }

    /**
     * @return BelongsTo<MayaConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(MayaConversation::class, 'maya_conversation_id');
    }

    public function isFromMaya(): bool
    {
        return $this->role == self::ROLE_ASSISTANT;
    }
}
