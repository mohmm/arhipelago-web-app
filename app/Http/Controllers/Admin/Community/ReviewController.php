<?php

namespace App\Http\Controllers\Admin\Community;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\PointOfInterest;
use App\Models\Review;
use App\Support\Toast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Moderating the reviews of places and experiences.
 */
class ReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('moderate', Review::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $reviews = Review::query()
            ->with(['user', 'reviewable'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where('body', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->when($filters['rating'] ?? null, fn (Builder $query, int $rating) => $query->where('rating', $rating))
            ->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Review $review): array => [
                'id' => $review->id,
                'rating' => $review->rating,
                'body' => $review->body,
                'author' => $review->user == null ? null : ['id' => $review->user->id, 'name' => $review->user->full_name],
                'target' => match (true) {
                    $review->reviewable instanceof PointOfInterest => $review->reviewable->translated_name,
                    $review->reviewable instanceof Experience => $review->reviewable->fullTitle(),
                    default => null,
                },
                'created_at' => $review->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/community/reviews', ['reviews' => $reviews, 'filters' => $filters]);
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        Toast::success(__('Review deleted.'));

        return back();
    }
}
