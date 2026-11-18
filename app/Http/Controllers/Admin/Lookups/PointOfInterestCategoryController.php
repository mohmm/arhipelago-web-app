<?php

namespace App\Http\Controllers\Admin\Lookups;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLookupRequest;
use App\Http\Requests\UpdateLookupRequest;
use App\Models\PointOfInterestCategory;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PointOfInterestCategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', PointOfInterestCategory::class);

        $categories = PointOfInterestCategory::query()
            ->ordered()
            ->paginate(15)
            ->through(fn (PointOfInterestCategory $item) => $this->formatItem($item));

        return Inertia::render('admin/lookups/categories/index', [
            'items' => $categories,
            'filters' => [],
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): Response
    {
        $this->authorize('create', PointOfInterestCategory::class);

        return Inertia::render('admin/lookups/categories/create');
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreLookupRequest $request): RedirectResponse
    {
        $this->authorize('create', PointOfInterestCategory::class);

        PointOfInterestCategory::create($request->validated());

        Toast::success(__('Reference item created successfully.'));

        return to_route('admin.lookups.categories.index');
    }

    /**
     * Display the specified category.
     */
    public function show(PointOfInterestCategory $category): Response
    {
        $this->authorize('view', $category);

        return Inertia::render('admin/lookups/categories/show', [
            'item' => $this->formatItem($category),
        ]);
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(PointOfInterestCategory $category): Response
    {
        $this->authorize('update', $category);

        return Inertia::render('admin/lookups/categories/edit', [
            'item' => $this->formatItem($category),
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateLookupRequest $request, PointOfInterestCategory $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update($request->validated());

        Toast::success(__('Reference item updated successfully.'));

        return to_route('admin.lookups.categories.index');
    }

    /**
     * Remove a category nothing is filed under.
     *
     * Refused with a reason while points of interest still use it; the
     * database would refuse too, but without telling the editor why.
     */
    public function destroy(PointOfInterestCategory $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $inUse = $category->pointsOfInterest()->count();

        if ($inUse > 0) {
            /*
             * Phrased around the count rather than agreeing with it. Romanian
             * needs three plural forms and this sentence would need all of them
             * to say nothing extra; the number in brackets carries the same
             * information in every language.
             */
            Toast::error(__(
                'This category is in use (:count) and cannot be deleted. Deactivate it instead.',
                ['count' => number_format($inUse)],
            ));

            return back();
        }

        $category->delete();

        Toast::success(__('Reference item deleted successfully.'));

        return to_route('admin.lookups.categories.index');
    }

    /**
     * Format a category for the frontend.
     *
     * @return array<string, mixed>
     */
    private function formatItem(PointOfInterestCategory $item): array
    {
        return [
            'id' => $item->id,
            'slug' => $item->slug,
            'name_en' => $item->name_en,
            'name_ro' => $item->name_ro,
            'name_ar' => $item->name_ar,
            'translated_name' => $item->translated_name,
            'icon' => $item->icon,
            'color' => $item->color,
            'image_url' => $item->image_url,
            'is_active' => $item->is_active,
            'sort_order' => $item->sort_order,
            'created_at' => $item->created_at?->toISOString(),
            'updated_at' => $item->updated_at?->toISOString(),
        ];
    }
}
