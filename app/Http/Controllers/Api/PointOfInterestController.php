<?php

namespace App\Http\Controllers\Api;

use App\Enums\ExperienceKind;
use App\Http\Controllers\Controller;
use App\Http\Resources\PointOfInterestCardResource;
use App\Http\Resources\PointOfInterestResource;
use App\Models\PointOfInterest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * The places worth seeing, as a searchable list and one page each.
 */
class PointOfInterestController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $places = PointOfInterest::query()
            ->published()
            ->with(['category', 'media'])
            ->withCount(['experiences' => fn (Builder $experiences) => $experiences->where('is_published', true)])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->search($search))
            ->when($filters['category'] ?? null, fn (Builder $query, string $slug) => $query->whereRelation('category', 'slug', $slug))
            ->orderBy('name_ro')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();

        return PointOfInterestCardResource::collection($places);
    }

    public function show(PointOfInterest $pointOfInterest): PointOfInterestResource
    {
        abort_unless($pointOfInterest->is_published, 404);

        $pointOfInterest->load([
            'category',
            'media',
            'experiences' => fn ($experiences) => $experiences->published()
                ->with(['destination', 'stops.pointOfInterest'])
                ->orderByRaw(ExperienceKind::orderExpression()),
        ])
            ->loadCount([
                'experiences' => fn (Builder $experiences) => $experiences->where('is_published', true),
                'posts' => fn (Builder $posts) => $posts->where('is_approved', true),
                'reviews',
            ])
            ->loadAvg('reviews', 'rating');

        return PointOfInterestResource::make($pointOfInterest);
    }
}
