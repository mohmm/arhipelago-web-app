<?php

namespace App\Http\Controllers\Api;

use App\Enums\ExperienceKind;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExperienceCardResource;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * Day routes, as a list filtered by kind and one page each.
 */
class ExperienceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'kind' => ['nullable', Rule::enum(ExperienceKind::class)],
            'destination' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $experiences = Experience::query()
            ->published()
            ->with(['destination', 'stops.pointOfInterest'])
            ->when($filters['kind'] ?? null, fn (Builder $query, string $kind) => $query->where('kind', $kind))
            ->when($filters['destination'] ?? null, fn (Builder $query, string $slug) => $query->whereRelation('destination', 'slug', $slug))
            ->join('destinations', 'destinations.id', '=', 'experiences.destination_id')
            ->orderBy('destinations.sort_order')
            ->orderBy('destinations.name_ro')
            ->orderByRaw(ExperienceKind::orderExpression('experiences.kind'))
            ->select('experiences.*')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();

        return ExperienceCardResource::collection($experiences);
    }

    public function show(Experience $experience): ExperienceResource
    {
        abort_unless($experience->is_published, 404);

        $experience->load([
            'destination.experiences' => fn ($siblings) => $siblings->published()->orderByRaw(ExperienceKind::orderExpression()),
            'stops.pointOfInterest' => fn ($places) => $places->with(['category', 'media']),
        ])
            ->loadCount('reviews')
            ->loadAvg('reviews', 'rating');

        return ExperienceResource::make($experience);
    }
}
