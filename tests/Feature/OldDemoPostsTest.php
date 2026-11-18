<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('removes the old website\'s demonstration posts and keeps what travellers publish in the app', function () {
    Storage::fake('public');
    Storage::disk('public')->put('experiences/stock.jpg', 'jpeg');
    Storage::disk('public')->put('posts/bran.jpg', 'jpeg');

    $demo = Post::factory()->create(['image_path' => 'experiences/stock.jpg', 'created_at' => '2025-01-15 08:30:00']);
    $comment = Comment::factory()->create(['commentable_type' => 'post', 'commentable_id' => $demo->id]);
    $demo->toggleLikeBy(User::factory()->create());
    $travellers = Post::factory()->create(['image_path' => 'posts/bran.jpg', 'created_at' => '2026-11-04 10:00:00']);

    (require database_path('migrations/2026_11_03_001100_remove_the_old_website_demo_posts.php'))->up();

    expect(Post::pluck('id')->all())->toBe([$travellers->id])
        ->and(Comment::find($comment->id))->toBeNull()
        ->and(Reaction::count())->toBe(0);

    Storage::disk('public')->assertMissing('experiences/stock.jpg');
    Storage::disk('public')->assertExists('posts/bran.jpg');
});
