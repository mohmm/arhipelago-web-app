<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\ProfileResource;
use App\Models\Comment;
use App\Models\Experience;
use App\Models\PointOfInterest;
use App\Models\Post;
use App\Models\Review;
use App\Models\User;
use App\Services\Accounts\ProfileQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * A traveller's public profile, their posts and their recent activity.
 */
class UserController extends Controller
{
    /** How many recent comments and reviews the "Activitate" tab shows. */
    private const ACTIVITY_LIMIT = 30;

    public function __construct(private ProfileQuery $profiles) {}

    public function show(Request $request, User $user): ProfileResource
    {
        return ProfileResource::make($this->profiles->load($user, $request->user('sanctum')));
    }

    public function posts(Request $request, User $user): AnonymousResourceCollection
    {
        $posts = $user->posts()
            ->visible()
            ->with(['user', 'pointOfInterest', 'experience.destination', 'experience.stops:id,experience_id,point_of_interest_id,position'])
            ->withLikedBy($request->user('sanctum'))
            ->latest()
            ->latest('id')
            ->paginate(18);

        return PostResource::collection($posts);
    }

    /**
     * The traveller's latest comments and reviews, newest first.
     */
    public function activity(User $user): JsonResponse
    {
        $comments = $user->comments()
            ->with('commentable')
            ->latest()
            ->latest('id')
            ->limit(self::ACTIVITY_LIMIT)
            ->get()
            ->filter(fn (Comment $comment): bool => $comment->commentable instanceof Post && $comment->commentable->is_approved)
            ->map(fn (Comment $comment): array => [
                'type' => 'comment',
                'id' => $comment->id,
                'body' => $comment->body,
                'created_at' => $comment->created_at?->toIso8601String(),
                'post_id' => $comment->commentable_id,
            ]);

        $reviews = $user->reviews()
            ->with('reviewable')
            ->latest()
            ->latest('id')
            ->limit(self::ACTIVITY_LIMIT)
            ->get()
            ->filter(fn (Review $review): bool => ($review->reviewable instanceof PointOfInterest || $review->reviewable instanceof Experience) && $review->reviewable->is_published)
            ->map(fn (Review $review): array => [
                'type' => 'review',
                'id' => $review->id,
                'rating' => $review->rating,
                'body' => $review->body,
                'created_at' => $review->created_at?->toIso8601String(),
                'target' => $review->reviewable instanceof PointOfInterest
                    ? ['type' => 'point_of_interest', 'id' => $review->reviewable->id, 'name' => $review->reviewable->translated_name]
                    : ['type' => 'experience', 'id' => $review->reviewable->id, 'name' => $review->reviewable->title()],
            ]);

        return response()->json([
            'data' => $comments->concat($reviews)
                ->sortByDesc('created_at')
                ->take(self::ACTIVITY_LIMIT)
                ->values(),
        ]);
    }
}
