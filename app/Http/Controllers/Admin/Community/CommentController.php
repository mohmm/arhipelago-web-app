<?php

namespace App\Http\Controllers\Admin\Community;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Support\Toast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Moderating the comments under posts.
 */
class CommentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('moderate', Comment::class);

        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100']]);

        $comments = Comment::query()
            ->with(['user', 'commentable'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where('body', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Comment $comment): array => [
                'id' => $comment->id,
                'body' => $comment->body,
                'author' => $comment->user == null ? null : ['id' => $comment->user->id, 'name' => $comment->user->full_name],
                'post' => $comment->commentable == null ? null : ['id' => $comment->commentable_id, 'body' => str($comment->commentable->body ?? '')->limit(80)->value()],
                'created_at' => $comment->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/community/comments', ['comments' => $comments, 'filters' => $filters]);
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        Toast::success(__('Comment deleted.'));

        return back();
    }
}
