<?php

namespace App\Models;

use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use App\Services\Notifications\Notifier;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * A traveller using the app, or a member of staff using the admin panel.
 *
 * Email verification is sent but not enforced: the app works before the
 * address is confirmed.
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $locale
 * @property string|null $avatar_path
 * @property string|null $bio
 * @property string|null $city
 * @property string|null $travel_preferences
 * @property-read string $full_name
 * @property-read string $initials
 * @property-read string|null $avatar_url
 */
class User extends Authenticatable implements HasLocalePreference, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /** The folder on the public disk that avatars are stored in. */
    public const AVATAR_DIRECTORY = 'avatars';

    /**
     * The guard whose roles decide staff membership, so the answer does not
     * depend on how the current request authenticated.
     */
    private const STAFF_GUARD = 'web';

    /** @var list<string> */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'locale',
        'password',
        'bio',
        'city',
        'travel_preferences',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The language to write to this person in.
     */
    public function preferredLocale(): ?string
    {
        return $this->locale;
    }

    public function sendEmailVerificationNotification(): void
    {
        app(Notifier::class)->send($this, new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        app(Notifier::class)->send($this, new ResetPasswordNotification($token));
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /**
     * "AM" for Ana Marin: what the avatar shows when there is no photo.
     */
    public function getInitialsAttribute(): string
    {
        return Str::upper(Str::substr((string) $this->first_name, 0, 1).Str::substr((string) $this->last_name, 0, 1));
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path == null ? null : Storage::disk('public')->url($this->avatar_path);
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'created_by_user_id');
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<Reaction, $this>
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * @return HasMany<MayaConversation, $this>
     */
    public function mayaConversations(): HasMany
    {
        return $this->hasMany(MayaConversation::class);
    }

    /**
     * The travellers this one follows.
     *
     * @return BelongsToMany<User, $this>
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')->withTimestamps();
    }

    /**
     * The travellers following this one.
     *
     * @return BelongsToMany<User, $this>
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin', self::STAFF_GUARD);
    }

    /**
     * Whether this person may sign in to the admin panel at all.
     */
    public function canAccessAdminPanel(): bool
    {
        return $this->can('admin.view');
    }

    /**
     * The role names this user may assign to others: none without the
     * `user.update` permission, and super_admin only by a super admin.
     *
     * @return array<int, string>
     */
    public function assignableRoleNames(): array
    {
        if (! $this->isSuperAdmin() && ! $this->can('user.update')) {
            return [];
        }

        return Role::query()
            ->where('guard_name', self::STAFF_GUARD)
            ->when(! $this->isSuperAdmin(), fn ($query) => $query->where('name', '!=', 'super_admin'))
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}
