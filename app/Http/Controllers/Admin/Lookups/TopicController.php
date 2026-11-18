<?php

namespace App\Http\Controllers\Admin\Lookups;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLookupRequest;
use App\Http\Requests\UpdateLookupRequest;
use App\Models\Topic;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TopicController extends Controller
{
    /**
     * Display a listing of topics.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Topic::class);

        $topics = Topic::query()
            ->ordered()
            ->paginate(15)
            ->through(fn (Topic $item) => $this->formatItem($item));

        return Inertia::render('admin/lookups/topics/index', [
            'items' => $topics,
            'filters' => [],
        ]);
    }

    /**
     * Show the form for creating a new topic.
     */
    public function create(): Response
    {
        $this->authorize('create', Topic::class);

        return Inertia::render('admin/lookups/topics/create');
    }

    /**
     * Store a newly created topic.
     */
    public function store(StoreLookupRequest $request): RedirectResponse
    {
        $this->authorize('create', Topic::class);

        Topic::create($request->validated());

        Toast::success(__('Reference item created successfully.'));

        return to_route('admin.lookups.topics.index');
    }

    /**
     * Display the specified topic.
     */
    public function show(Topic $topic): Response
    {
        $this->authorize('view', $topic);

        return Inertia::render('admin/lookups/topics/show', [
            'item' => $this->formatItem($topic),
        ]);
    }

    /**
     * Show the form for editing the specified topic.
     */
    public function edit(Topic $topic): Response
    {
        $this->authorize('update', $topic);

        return Inertia::render('admin/lookups/topics/edit', [
            'item' => $this->formatItem($topic),
        ]);
    }

    /**
     * Update the specified topic.
     */
    public function update(UpdateLookupRequest $request, Topic $topic): RedirectResponse
    {
        $this->authorize('update', $topic);

        $topic->update($request->validated());

        Toast::success(__('Reference item updated successfully.'));

        return to_route('admin.lookups.topics.index');
    }

    /**
     * Remove the specified topic.
     */
    public function destroy(Topic $topic): RedirectResponse
    {
        $this->authorize('delete', $topic);

        $topic->delete();

        Toast::success(__('Reference item deleted successfully.'));

        return to_route('admin.lookups.topics.index');
    }

    /**
     * Format a topic for the frontend.
     *
     * @return array<string, mixed>
     */
    private function formatItem(Topic $item): array
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
