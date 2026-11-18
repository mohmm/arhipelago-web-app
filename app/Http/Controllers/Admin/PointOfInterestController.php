<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PhotoLicense;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PointOfInterestRequest;
use App\Models\Experience;
use App\Models\PointOfInterest;
use App\Models\PointOfInterestCategory;
use App\Models\Topic;
use App\Support\Toast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Points of interest in the admin panel: added, edited, given photos and published.
 */
class PointOfInterestController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PointOfInterest::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:published,draft'],
        ]);

        $places = PointOfInterest::query()
            ->with(['category', 'media'])
            ->withCount('experiences')
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->search($search))
            ->when($filters['category'] ?? null, fn (Builder $query, int $category) => $query->where('category_id', $category))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('is_published', $status == 'published'))
            ->orderBy('name_ro')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (PointOfInterest $place): array => [
                'id' => $place->id,
                'name' => $place->translated_name,
                'type' => $place->translated_type,
                'locality' => $place->translated_locality,
                'category' => $place->category?->translated_name,
                'thumb_url' => $place->coverPhoto()?->getUrl('thumb'),
                'experiences_count' => $place->experiences_count,
                'likes_count' => $place->likes_count,
                'is_published' => $place->is_published,
                'missing_languages' => $this->missingLanguages($place),
            ]);

        return Inertia::render('admin/points-of-interest/index', [
            'places' => $places,
            'filters' => $filters,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', PointOfInterest::class);

        return Inertia::render('admin/points-of-interest/form', [
            'place' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(PointOfInterestRequest $request): RedirectResponse
    {
        $this->authorize('create', PointOfInterest::class);

        $place = DB::transaction(function () use ($request): PointOfInterest {
            $place = PointOfInterest::create($request->safe()->except('topics'));
            $place->topics()->sync($request->validated('topics'));

            return $place;
        });

        Toast::success(__('Point of interest created. Add a photo, then publish it.'));

        return to_route('admin.points-of-interest.edit', $place);
    }

    public function edit(PointOfInterest $pointOfInterest): Response
    {
        $this->authorize('update', $pointOfInterest);

        $pointOfInterest->load(['media', 'topics:id', 'experiences.destination']);

        return Inertia::render('admin/points-of-interest/form', [
            'place' => [
                ...$pointOfInterest->only([
                    'id', 'category_id', 'slug',
                    'name_ro', 'name_en', 'name_ar', 'type_ro', 'type_en', 'type_ar',
                    'locality_ro', 'locality_en', 'locality_ar', 'address_ro', 'address_en', 'address_ar',
                    'description_ro', 'description_en', 'description_ar',
                    'latitude', 'longitude', 'is_historic_monument', 'website', 'is_published', 'likes_count',
                ]),
                'topics' => $pointOfInterest->topics->pluck('id'),
                'photos' => $pointOfInterest->getMedia(PointOfInterest::PHOTOS)->map(fn (Media $photo): array => [
                    'id' => $photo->id,
                    'thumb_url' => $photo->getUrl('thumb'),
                    'url' => $photo->getUrl('large'),
                    'author' => $photo->getCustomProperty('author'),
                    'license' => PhotoLicense::tryFrom((string) $photo->getCustomProperty('license'))?->label() ?? $photo->getCustomProperty('license'),
                    'source_url' => $photo->getCustomProperty('source_url'),
                ])->values(),
                'experiences' => $pointOfInterest->experiences->map(fn (Experience $experience): array => [
                    'id' => $experience->id,
                    'title' => $experience->fullTitle(),
                ])->values(),
            ],
            ...$this->formOptions(),
        ]);
    }

    public function update(PointOfInterestRequest $request, PointOfInterest $pointOfInterest): RedirectResponse
    {
        $this->authorize('update', $pointOfInterest);

        DB::transaction(function () use ($request, $pointOfInterest): void {
            $pointOfInterest->update($request->safe()->except('topics'));
            $pointOfInterest->topics()->sync($request->validated('topics'));
        });

        Toast::success(__('Point of interest saved.'));

        return back();
    }

    public function destroy(PointOfInterest $pointOfInterest): RedirectResponse
    {
        $this->authorize('delete', $pointOfInterest);

        $routes = $pointOfInterest->experiences()->count();

        if ($routes > 0) {
            Toast::error(__('This place is a stop in :count experiences. Remove it from them first.', ['count' => $routes]));

            return back();
        }

        $pointOfInterest->delete();

        Toast::success(__('Point of interest deleted.'));

        return to_route('admin.points-of-interest.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'categories' => $this->categoryOptions(),
            'topics' => Topic::query()->active()->ordered()->get()
                ->map(fn (Topic $topic): array => ['value' => $topic->id, 'label' => $topic->translated_name])
                ->values(),
            'photoLicenses' => array_map(fn (PhotoLicense $license): array => ['value' => $license->value, 'label' => $license->label()], PhotoLicense::cases()),
        ];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    private function categoryOptions(): array
    {
        return PointOfInterestCategory::query()->ordered()->get()
            ->map(fn (PointOfInterestCategory $category): array => ['value' => $category->id, 'label' => (string) $category->translated_name])
            ->values()
            ->all();
    }

    /**
     * The languages a place is not yet written in, so the list can say so.
     *
     * @return list<string>
     */
    private function missingLanguages(PointOfInterest $place): array
    {
        return collect(['en', 'ar'])
            ->filter(fn (string $locale): bool => blank($place->{"name_{$locale}"}) || blank($place->{"description_{$locale}"}))
            ->values()
            ->all();
    }
}
