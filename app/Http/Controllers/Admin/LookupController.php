<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointOfInterestCategory;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class LookupController extends Controller
{
    /**
     * Display the reference-table overview.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $lookupTypes = collect([
            [
                'ability' => fn (): bool => $user->can('viewAny', PointOfInterestCategory::class),
                'name' => __('Categories'),
                'slug' => 'categories',
                'href' => route('admin.lookups.categories.index'),
                'count' => PointOfInterestCategory::count(),
                'description' => __('Categories used to classify points of interest.'),
                'icon' => 'tags',
            ],
            [
                'ability' => fn (): bool => $user->can('viewAny', Topic::class),
                'name' => __('Topics'),
                'slug' => 'topics',
                'href' => route('admin.lookups.topics.index'),
                'count' => Topic::count(),
                'description' => __('Themes used to tag points of interest and experiences.'),
                'icon' => 'hash',
            ],
        ])
            ->filter(fn (array $type): bool => $type['ability']())
            ->map(fn (array $type): array => Arr::except($type, 'ability'))
            ->values()
            ->all();

        abort_if($lookupTypes == [], 403);

        return Inertia::render('admin/lookups/index', [
            'lookupTypes' => $lookupTypes,
        ]);
    }
}
