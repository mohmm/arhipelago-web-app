<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * "Urmărește": one tap follows a traveller, the next unfollows.
 */
class FollowController extends Controller
{
    public function store(Request $request, User $user): JsonResponse
    {
        $viewer = $request->user();

        if ($viewer->is($user)) {
            throw ValidationException::withMessages(['user' => __('You cannot follow yourself.')]);
        }

        $changes = $viewer->following()->toggle([$user->id]);

        return response()->json(['data' => [
            'following' => $changes['attached'] != [],
            'followers_count' => $user->followers()->count(),
        ]]);
    }
}
