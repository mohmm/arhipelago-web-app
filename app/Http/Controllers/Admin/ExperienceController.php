<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExperienceKind;
use App\Enums\TravelMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExperienceRequest;
use App\Models\Destination;
use App\Models\Experience;
use App\Models\ExperienceStop;
use App\Models\PointOfInterest;
use App\Services\Experiences\MeasureExperience;
use App\Support\Toast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Experiences in the admin panel. Saving new stops or a new travel mode
 * measures the route again on real roads, so the app never shows a distance
 * that belongs to a different route.
 */
class ExperienceController extends Controller
{
    public function __construct(private MeasureExperience $measure) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Experience::class);

        $filters = $request->validate([
            'destination' => ['nullable', 'integer'],
            'kind' => ['nullable', 'string'],
            'status' => ['nullable', 'in:published,draft'],
        ]);

        $experiences = Experience::query()
            ->with('destination')
            ->withCount('stops')
            ->when($filters['destination'] ?? null, fn (Builder $query, int $destination) => $query->where('destination_id', $destination))
            ->when($filters['kind'] ?? null, fn (Builder $query, string $kind) => $query->where('kind', $kind))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('is_published', $status == 'published'))
            ->join('destinations', 'destinations.id', '=', 'experiences.destination_id')
            ->orderBy('destinations.sort_order')
            ->orderBy('destinations.name_ro')
            ->orderByRaw(ExperienceKind::orderExpression('experiences.kind'))
            ->select('experiences.*')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Experience $experience): array => [
                'id' => $experience->id,
                'title' => $experience->fullTitle(),
                'kind' => $experience->kind->value,
                'travel_mode' => $experience->travel_mode->value,
                'distance_m' => $experience->distance_m,
                'duration_s' => $experience->duration_s,
                'stops_count' => $experience->stops_count,
                'is_published' => $experience->is_published,
            ]);

        return Inertia::render('admin/experiences/index', [
            'experiences' => $experiences,
            'filters' => $filters,
            ...$this->options(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Experience::class);

        return Inertia::render('admin/experiences/form', ['experience' => null, ...$this->options()]);
    }

    public function store(ExperienceRequest $request): RedirectResponse
    {
        $this->authorize('create', Experience::class);

        $experience = DB::transaction(function () use ($request): Experience {
            $experience = Experience::create($request->safe()->except('stops'));
            $this->syncStops($experience, $request->validated('stops'));

            return $experience;
        });

        return $this->measured($experience, to_route('admin.experiences.edit', $experience), __('Experience created.'));
    }

    public function edit(Experience $experience): Response
    {
        $this->authorize('update', $experience);

        $experience->load(['destination', 'stops.pointOfInterest']);

        return Inertia::render('admin/experiences/form', [
            'experience' => [
                ...$experience->only(['id', 'destination_id', 'description_ro', 'description_en', 'description_ar', 'description_ai_generated', 'is_published', 'distance_m', 'duration_s', 'route_polyline']),
                'kind' => $experience->kind->value,
                'travel_mode' => $experience->travel_mode->value,
                'title' => $experience->fullTitle(),
                'stops' => $experience->stops->pluck('point_of_interest_id'),
                'measured_stops' => $experience->stops->map(fn (ExperienceStop $stop): array => [
                    'point_of_interest_id' => $stop->point_of_interest_id,
                    'distance_from_start_m' => $stop->distance_from_start_m,
                    'duration_from_start_s' => $stop->duration_from_start_s,
                ])->values(),
            ],
            ...$this->options(),
        ]);
    }

    public function update(ExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $this->authorize('update', $experience);

        $routeChanged = $experience->stops()->pluck('point_of_interest_id')->all() != $request->validated('stops')
            || $experience->travel_mode->value != $request->validated('travel_mode');

        DB::transaction(function () use ($request, $experience, $routeChanged): void {
            $experience->update($request->safe()->except('stops'));

            if ($routeChanged) {
                $this->syncStops($experience, $request->validated('stops'));
            }
        });

        if ($routeChanged) {
            return $this->measured($experience, back(), __('Experience saved.'));
        }

        Toast::success(__('Experience saved.'));

        return back();
    }

    /**
     * Measures the route again, for when the router was unreachable at the last save.
     */
    public function measure(Experience $experience): RedirectResponse
    {
        $this->authorize('update', $experience);

        return $this->measured($experience, back(), __('Route measured.'));
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $this->authorize('delete', $experience);

        $experience->delete();

        Toast::success(__('Experience deleted.'));

        return to_route('admin.experiences.index');
    }

    /**
     * @param  list<int>  $placeIds
     */
    private function syncStops(Experience $experience, array $placeIds): void
    {
        $experience->stops()->delete();

        foreach (array_values($placeIds) as $position => $placeId) {
            $experience->stops()->create(['point_of_interest_id' => $placeId, 'position' => $position]);
        }
    }

    /**
     * Measures the route, then redirects. When the router cannot be reached,
     * the experience is kept offline rather than shown with wrong distances.
     */
    private function measured(Experience $experience, RedirectResponse $redirect, string $success): RedirectResponse
    {
        try {
            $this->measure->handle($experience);

            Toast::success($success);

            return $redirect;
        } catch (RuntimeException $exception) {
            report($exception);

            $experience->forceFill(['is_published' => false, 'distance_m' => 0, 'duration_s' => 0, 'route_polyline' => null])->save();

            Toast::error(__('Saved, but the route could not be measured because the routing service is not reachable. The experience stays unpublished until it is measured.'));

            return $redirect;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'destinations' => Destination::query()->ordered()->get()
                ->map(fn (Destination $destination): array => ['value' => $destination->id, 'label' => (string) $destination->translated_name])
                ->values(),
            'kinds' => collect(ExperienceKind::cases())
                ->map(fn (ExperienceKind $kind): array => ['value' => $kind->value, 'label' => $kind->label(), 'travel_mode' => $kind->travelMode()->value])
                ->values(),
            'travelModes' => collect(TravelMode::cases())
                ->map(fn (TravelMode $mode): array => ['value' => $mode->value, 'label' => $mode->label()])
                ->values(),
            'places' => PointOfInterest::query()->published()->orderBy('name_ro')->get()
                ->map(fn (PointOfInterest $place): array => [
                    'value' => $place->id,
                    'label' => $place->translated_name.' · '.$place->translated_locality,
                    'latitude' => $place->latitude,
                    'longitude' => $place->longitude,
                ])
                ->values(),
        ];
    }
}
