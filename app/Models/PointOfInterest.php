<?php

namespace App\Models;

use App\Traits\Likeable;
use App\Traits\Reviewable;
use App\Traits\Topicable;
use App\Traits\Translatable;
use Database\Factories\PointOfInterestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A place worth seeing: a castle, a fortress, a museum, a piece of nature.
 *
 * Text is stored in Romanian, English and Arabic; `translated_*` reads the
 * current language with a fallback to Romanian. Photos live in the media
 * library with the author and licence each one was published under.
 *
 * @property int $id
 * @property int $category_id
 * @property string $slug
 * @property float $latitude
 * @property float $longitude
 * @property bool $is_historic_monument
 * @property string|null $website
 * @property bool $is_published
 * @property int $likes_count
 * @property-read string|null $translated_name
 * @property-read string|null $translated_type
 * @property-read string|null $translated_locality
 * @property-read string|null $translated_address
 * @property-read string|null $translated_description
 */
class PointOfInterest extends Model implements HasMedia
{
    /** @use HasFactory<PointOfInterestFactory> */
    use HasFactory, InteractsWithMedia, Likeable, Reviewable, Topicable, Translatable;

    /** The media collection holding the photo gallery; the first photo is the cover. */
    public const PHOTOS = 'photos';

    /** The custom properties every photo carries, so it can be credited. */
    /** Where a photo came from, kept with it for the record. The app shows none of it. */
    public const PHOTO_SOURCE_FIELDS = ['license', 'author', 'source_url'];

    protected $table = 'points_of_interest';

    /** @var list<string> */
    protected array $translatable = ['name', 'type', 'locality', 'address', 'description'];

    /** @var list<string> */
    protected $fillable = [
        'category_id',
        'slug',
        'name_ro', 'name_en', 'name_ar',
        'type_ro', 'type_en', 'type_ar',
        'locality_ro', 'locality_en', 'locality_ar',
        'address_ro', 'address_en', 'address_ar',
        'description_ro', 'description_en', 'description_ar',
        'latitude',
        'longitude',
        'is_historic_monument',
        'website',
        'is_published',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_historic_monument' => 'boolean',
            'is_published' => 'boolean',
            'likes_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<PointOfInterestCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PointOfInterestCategory::class, 'category_id');
    }

    /**
     * The experiences that stop here.
     *
     * @return BelongsToMany<Experience, $this>
     */
    public function experiences(): BelongsToMany
    {
        return $this->belongsToMany(Experience::class, 'experience_stops')
            ->withPivot(['position', 'distance_from_start_m', 'duration_from_start_s'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * Matches the name in any language, so a traveller finds "Bran" whatever
     * language the app is in.
     *
     * @param  Builder<static>  $query
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $like = '%'.addcslashes($term, '%_\\').'%';

        $query->where(function (Builder $names) use ($like): void {
            $names->where('name_ro', 'like', $like)
                ->orWhere('name_en', 'like', $like)
                ->orWhere('name_ar', 'like', $like)
                ->orWhere('locality_ro', 'like', $like);
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::PHOTOS)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Resized when the photo is uploaded rather than on a queue, so a place is
     * never published with a photo that has no thumbnail yet.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 480, 360)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1600, 1600)
            ->nonQueued();
    }

    public function coverPhoto(): ?Media
    {
        return $this->getFirstMedia(self::PHOTOS);
    }
}
