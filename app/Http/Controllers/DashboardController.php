<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\MayaConversation;
use App\Models\PointOfInterest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The admin panel's first page: how much content is live, what the community
 * did this week, and the latest posts to look over.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $weekAgo = now()->subWeek();

        return Inertia::render('dashboard', [
            'stats' => [
                'pointsOfInterest' => [
                    'published' => PointOfInterest::query()->published()->count(),
                    'total' => PointOfInterest::query()->count(),
                ],
                'experiences' => [
                    'published' => Experience::query()->published()->count(),
                    'total' => Experience::query()->count(),
                ],
                'posts' => [
                    'thisWeek' => Post::query()->where('created_at', '>=', $weekAgo)->count(),
                    'hidden' => Post::query()->where('is_approved', false)->count(),
                ],
                'travellers' => [
                    'total' => User::query()->role('customer', 'web')->count(),
                    'thisWeek' => User::query()->role('customer', 'web')->where('created_at', '>=', $weekAgo)->count(),
                ],
                'mayaConversationsThisWeek' => MayaConversation::query()->where('created_at', '>=', $weekAgo)->count(),
            ],
            'recentPosts' => Post::query()
                ->with(['user:id,first_name,last_name', 'pointOfInterest:id,name_ro,name_en,name_ar'])
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn (Post $post): array => [
                    'id' => $post->id,
                    'body' => str($post->body)->limit(140)->value(),
                    'imageUrl' => $post->image_url,
                    'author' => $post->user?->full_name,
                    'pointOfInterest' => $post->pointOfInterest?->translated_name,
                    'isApproved' => $post->is_approved,
                    'createdAt' => $post->created_at?->toIso8601String(),
                ]),
        ]);
    }
}
