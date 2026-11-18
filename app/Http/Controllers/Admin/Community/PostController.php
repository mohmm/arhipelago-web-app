<?php

namespace App\Http\Controllers\Admin\Community;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\Toast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Moderating the community feed: a post can be hidden, shown again or deleted.
 */
class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('moderate', Post::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:visible,hidden'],
        ]);

        $posts = Post::query()
            ->with(['user', 'pointOfInterest'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where('body', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('is_approved', $status == 'visible'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Post $post): array => [
                'id' => $post->id,
                'body' => $post->body,
                'image_url' => $post->image_url,
                'author' => $post->user == null ? null : ['id' => $post->user->id, 'name' => $post->user->full_name, 'email' => $post->user->email],
                'place' => $post->pointOfInterest?->translated_name,
                'likes_count' => $post->likes_count,
                'comments_count' => $post->comments_count,
                'is_approved' => $post->is_approved,
                'created_at' => $post->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/community/posts', ['posts' => $posts, 'filters' => $filters]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('moderate', Post::class);

        $post->update($request->validate(['is_approved' => ['required', 'boolean']]));

        Toast::success($post->is_approved ? __('The post is visible again.') : __('The post is hidden from the app.'));

        return back();
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        Toast::success(__('Post deleted.'));

        return back();
    }
}
