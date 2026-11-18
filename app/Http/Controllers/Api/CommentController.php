<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Comments under a post, oldest first so they read as a conversation.
 */
class CommentController extends Controller
{
    public function index(Post $post): AnonymousResourceCollection
    {
        abort_unless($post->is_approved, 404);

        return CommentResource::collection(
            $post->comments()->with('user')->oldest()->oldest('id')->paginate(50)
        );
    }

    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        abort_unless($post->is_approved, 404);

        $comment = $post->comments()->create([
            'body' => $request->validated('body'),
            'created_by_user_id' => $request->user()->id,
        ]);

        return CommentResource::make($comment->load('user'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(Request $request, Comment $comment): Response
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->noContent();
    }
}
