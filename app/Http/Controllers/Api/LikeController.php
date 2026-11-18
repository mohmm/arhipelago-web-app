<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PointOfInterest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The heart on a post or a place: one tap likes it, the next takes it back.
 */
class LikeController extends Controller
{
    public function post(Request $request, Post $post): JsonResponse
    {
        abort_unless($post->is_approved, 404);

        return $this->toggled($post->toggleLikeBy($request->user()), $post->likes_count);
    }

    public function pointOfInterest(Request $request, PointOfInterest $pointOfInterest): JsonResponse
    {
        abort_unless($pointOfInterest->is_published, 404);

        return $this->toggled($pointOfInterest->toggleLikeBy($request->user()), $pointOfInterest->likes_count);
    }

    private function toggled(bool $liked, int $likesCount): JsonResponse
    {
        return response()->json(['data' => ['liked' => $liked, 'likes_count' => $likesCount]]);
    }
}
