<?php

namespace App\Models;

use App\Traits\IsLookupTable;
use Database\Factories\PointOfInterestCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The filter chips above the list of places: "Castele și cetăți", "Muzee", "Natură".
 *
 * @property int $id
 * @property string $slug
 * @property string $name_ro
 * @property string $name_en
 * @property string|null $name_ar
 * @property string|null $icon
 * @property string|null $color
 * @property bool $is_active
 * @property int $sort_order
 */
class PointOfInterestCategory extends Model
{
    /** @use HasFactory<PointOfInterestCategoryFactory> */
    use HasFactory, IsLookupTable, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'name_ro',
        'name_en',
        'name_ar',
        'icon',
        'color',
        'image_url',
        'is_active',
        'sort_order',
    ];

    /**
     * @return HasMany<PointOfInterest, $this>
     */
    public function pointsOfInterest(): HasMany
    {
        return $this->hasMany(PointOfInterest::class, 'category_id');
    }
}
