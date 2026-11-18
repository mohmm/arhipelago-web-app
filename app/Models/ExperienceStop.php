<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One stop of an experience, in order, with how far along the route it is.
 *
 * @property int $id
 * @property int $experience_id
 * @property int $point_of_interest_id
 * @property int $position
 * @property int $distance_from_start_m
 * @property int $duration_from_start_s
 */
class ExperienceStop extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'point_of_interest_id',
        'position',
        'distance_from_start_m',
        'duration_from_start_s',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'distance_from_start_m' => 'integer',
            'duration_from_start_s' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Experience, $this>
     */
    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    /**
     * @return BelongsTo<PointOfInterest, $this>
     */
    public function pointOfInterest(): BelongsTo
    {
        return $this->belongsTo(PointOfInterest::class);
    }
}
