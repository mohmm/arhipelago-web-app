<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestinationRequest;
use App\Models\Destination;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The areas experiences are set in, such as Bran.
 */
class DestinationController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Destination::class);

        return Inertia::render('admin/destinations/index', [
            'destinations' => Destination::query()->withCount('experiences')->ordered()->get()
                ->map(fn (Destination $destination): array => [
                    'id' => $destination->id,
                    'name' => $destination->translated_name,
                    'region' => $destination->translated_region,
                    'experiences_count' => $destination->experiences_count,
                    'sort_order' => $destination->sort_order,
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Destination::class);

        return Inertia::render('admin/destinations/form', ['destination' => null]);
    }

    public function store(DestinationRequest $request): RedirectResponse
    {
        $this->authorize('create', Destination::class);

        Destination::create($request->validated());

        Toast::success(__('Destination created.'));

        return to_route('admin.destinations.index');
    }

    public function edit(Destination $destination): Response
    {
        $this->authorize('update', $destination);

        return Inertia::render('admin/destinations/form', [
            'destination' => $destination->only(['id', 'slug', 'name_ro', 'name_en', 'name_ar', 'region_ro', 'region_en', 'region_ar', 'sort_order']),
        ]);
    }

    public function update(DestinationRequest $request, Destination $destination): RedirectResponse
    {
        $this->authorize('update', $destination);

        $destination->update($request->validated());

        Toast::success(__('Destination saved.'));

        return to_route('admin.destinations.index');
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        $this->authorize('delete', $destination);

        if ($destination->experiences()->exists()) {
            Toast::error(__('Delete or move this destination\'s experiences first.'));

            return back();
        }

        $destination->delete();

        Toast::success(__('Destination deleted.'));

        return to_route('admin.destinations.index');
    }
}
