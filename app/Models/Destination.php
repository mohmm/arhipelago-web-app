<?php

namespace App\Models;

use App\Traits\Translatable;
use Database\Factories\DestinationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The area experiences are set in, such as Bran. It offers at most one
 * experience of each kind.
 *
 * @property int $id
 * @property string $slug
 * @property int $sort_order
 * @property-read string|null $translated_name
 * @property-read string|null $translated_region
 */
class Destination extends Model
{
    /** @use HasFactory<DestinationFactory> */
    use HasFactory, Translatable;

    /** @var list<string> */
    protected array $translatable = ['name', 'region'];

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'name_ro', 'name_en', 'name_ar',
        'region_ro', 'region_en', 'region_ar',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    /**
     * @return HasMany<Experience, $this>
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name_ro');
    }
}
