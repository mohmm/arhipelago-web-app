<?php

namespace App\Models;

use Database\Factories\LegalAcceptanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One person agreeing to one document, once.
 *
 * Append-only: a row is written and never updated, so `$timestamps` is off and
 * `accepted_at` is the only time that matters. Anything that changes the answer
 * is a new row with a different `context`.
 *
 * @property int $id
 * @property int $user_id
 * @property string $slug
 * @property string $version
 * @property string $body_sha256
 * @property string|null $locale
 * @property string $context
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon $accepted_at
 */
class LegalAcceptance extends Model
{
    /** @use HasFactory<LegalAcceptanceFactory> */
    use HasFactory;

    /** Sign-up: the moment the contract is formed. */
    public const CONTEXT_REGISTRATION = 'registration';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'slug',
        'version',
        'body_sha256',
        'locale',
        'context',
        'ip_address',
        'user_agent',
        'accepted_at',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }
}
