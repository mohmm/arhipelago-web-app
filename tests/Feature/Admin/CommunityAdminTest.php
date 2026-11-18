<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\Review;
use App\Models\User;
use App\Settings\MayaSettings;
use App\Support\Toast;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->staff = staffMember('admin');
});

it('hides a post from the app and shows it again', function () {
    $post = Post::factory()->create();

    actingAs($this->staff)->patch("/admin/community/posts/{$post->id}", ['is_approved' => false])->assertSessionHas(Toast::SESSION_KEY.'.type', Toast::SUCCESS);
    expect($post->fresh()->is_approved)->toBeFalse();

    actingAs($this->staff)->patch("/admin/community/posts/{$post->id}", ['is_approved' => true]);
    expect($post->fresh()->is_approved)->toBeTrue();
});

it('lists hidden and visible posts for moderators', function () {
    Post::factory()->create();
    Post::factory()->hidden()->create();

    actingAs($this->staff)->get('/admin/community/posts?status=hidden')
        ->assertInertia(fn ($page) => $page->component('admin/community/posts')->has('posts.data', 1));
});

it('deletes comments and reviews, keeping the counts exact', function () {
    $comment = Comment::factory()->create();
    $review = Review::factory()->create();

    actingAs($this->staff)->delete("/admin/community/comments/{$comment->id}")->assertSessionHas(Toast::SESSION_KEY.'.type', Toast::SUCCESS);
    actingAs($this->staff)->delete("/admin/community/reviews/{$review->id}")->assertSessionHas(Toast::SESSION_KEY.'.type', Toast::SUCCESS);

    expect(Comment::count())->toBe(0)
        ->and(Review::count())->toBe(0)
        ->and($comment->commentable->fresh()->comments_count)->toBe(0);
});

it('gives a moderator role the community and nothing else', function () {
    seedRoles();
    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);
    $role->givePermissionTo(['admin.view', 'community.view', 'post.moderate', 'comment.moderate', 'review.moderate']);
    $moderator = User::factory()->create();
    $moderator->assignRole($role);

    actingAs($moderator)->get('/admin/community/posts')->assertOk();
    actingAs($moderator)->get('/admin/points-of-interest')->assertForbidden();
    actingAs($moderator)->get('/admin/maya')->assertForbidden();
});

it('saves Maya\'s instructions', function () {
    $instructions = trim(str_repeat('Ești Maya, asistentul de călătorie Smartsense. ', 3));

    actingAs($this->staff)->put('/admin/maya', ['instructions' => $instructions])->assertSessionHas(Toast::SESSION_KEY.'.type', Toast::SUCCESS);

    expect(app(MayaSettings::class)->instructions)->toBe($instructions);
});

it('refuses instructions too short to describe Maya', function () {
    actingAs($this->staff)->put('/admin/maya', ['instructions' => 'Fii amabilă.'])->assertSessionHasErrors('instructions');
});

it('shows the dashboard with what is live and the latest posts', function () {
    Post::factory()->count(2)->create();

    actingAs($this->staff)->get('/admin/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('dashboard')->has('stats')->has('recentPosts', 2));
});
