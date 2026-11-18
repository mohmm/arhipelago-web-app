<?php

namespace App\Models;

use App\Enums\ExperienceKind;
use App\Enums\TravelMode;
use App\Traits\Reviewable;
use App\Traits\Topicable;
use App\Traits\Translatable;
use Database\Factories\ExperienceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A day route with stops at points of interest: "Traseul clasic · Bran".
 *
 * The distance, duration and line are measured on real roads by the router
 * whenever the stops change, and stored.
 *
 * @property int $id
 * @property int $destination_id
 * @property ExperienceKind $kind
 * @property TravelMode $travel_mode
 * @property bool $description_ai_generated
 * @property int $distance_m
 * @property int $duration_s
 * @property string|null $route_polyline
 * @property bool $is_published
 * @property-read string|null $translated_description
 */
class Experience extends Model
{
    /** @use HasFactory<ExperienceFactory> */
    use HasFactory, Reviewable, Topicable, Translatable;

    /** @var list<string> */
    protected array $translatable = ['description'];

    /** @var list<string> */
    protected $fillable = [
        'destination_id',
        'kind',
        'travel_mode',
        'description_ro', 'description_en', 'description_ar',
        'description_ai_generated',
        'is_published',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => ExperienceKind::class,
            'travel_mode' => TravelMode::class,
            'description_ai_generated' => 'boolean',
            'distance_m' => 'integer',
            'duration_s' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Destination, $this>
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * @return HasMany<ExperienceStop, $this>
     */
    public function stops(): HasMany
    {
        return $this->hasMany(ExperienceStop::class)->orderBy('position');
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
     * "Traseul clasic" in the given language.
     */
    public function title(?string $locale = null): string
    {
        return $this->kind->label($locale);
    }

    /**
     * "Traseul clasic · Bran": how the experience is named wherever it is listed.
     */
    public function fullTitle(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->title($locale).' · '.$this->destination->getTranslatedIn('name', $locale);
    }
}
