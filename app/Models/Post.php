<?php

namespace App\Models;

use App\Traits\Commentable;
use App\Traits\Likeable;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A traveller's photo and impressions in the community feed, optionally
 * about a point of interest or an experience.
 *
 * @property int $id
 * @property int $user_id
 * @property string $body
 * @property string|null $image_path
 * @property int|null $point_of_interest_id
 * @property int|null $experience_id
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int $likes_count
 * @property int $comments_count
 * @property bool $is_approved
 * @property-read string|null $image_url
 */
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use Commentable, HasFactory, Likeable;

    /** The folder on the public disk that post photos are stored in. */
    public const IMAGE_DIRECTORY = 'posts';

    /** @var list<string> */
    protected $fillable = [
        'body',
        'image_path',
        'point_of_interest_id',
        'experience_id',
        'is_approved',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // The photo goes with the post.
        static::deleted(function (Post $post): void {
            if ($post->image_path != null) {
                Storage::disk('public')->delete($post->image_path);
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Puts the post where the place it is about is, or at the start of the
     * experience it is about, so "Aproape de tine" can find it. A post about
     * neither has no position and only appears in "Recente". It never takes
     * where the traveller was standing.
     */
    public function placeOnTheMap(): void
    {
        $place = $this->point_of_interest_id != null
            ? PointOfInterest::query()->find($this->point_of_interest_id)
            : $this->experience?->stops()->with('pointOfInterest')->orderBy('position')->first()?->pointOfInterest;

        $this->latitude = $place?->latitude;
        $this->longitude = $place?->longitude;
    }

    /**
     * @return BelongsTo<PointOfInterest, $this>
     */
    public function pointOfInterest(): BelongsTo
    {
        return $this->belongsTo(PointOfInterest::class);
    }

    /**
     * @return BelongsTo<Experience, $this>
     */
    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    /**
     * Posts the community can see: approved, and not hidden by a moderator.
     *
     * @param  Builder<static>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_approved', true);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path == null ? null : Storage::disk('public')->url($this->image_path);
    }
}
