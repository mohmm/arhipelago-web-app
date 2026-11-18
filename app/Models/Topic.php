<?php

namespace App\Models;

use App\Traits\IsLookupTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A theme that places and experiences are tagged with ("Istorie", "Natură",
 * "Familie"). Keeps content organised in the admin and gives Maya a vocabulary
 * for what a traveller says they like.
 *
 * @property int $id
 * @property string $slug
 * @property string $name_ro
 * @property string $name_en
 * @property string|null $name_ar
 * @property bool $is_active
 * @property int $sort_order
 */
class Topic extends Model
{
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
     * @return MorphToMany<PointOfInterest, $this>
     */
    public function pointsOfInterest(): MorphToMany
    {
        return $this->morphedByMany(PointOfInterest::class, 'topicable');
    }

    /**
     * @return MorphToMany<Experience, $this>
     */
    public function experiences(): MorphToMany
    {
        return $this->morphedByMany(Experience::class, 'topicable');
    }
}
