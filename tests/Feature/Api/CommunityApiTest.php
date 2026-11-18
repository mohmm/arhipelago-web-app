<?php

use App\Models\Comment;
use App\Models\Experience;
use App\Models\PointOfInterest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(fn () => $this->withHeader('Accept-Language', 'ro'));

it('lists visible posts newest first, with what the viewer liked', function () {
    $older = Post::factory()->create(['body' => 'Primul']);
    $newer = Post::factory()->create(['body' => 'Al doilea']);
    Post::factory()->hidden()->create(['body' => 'Ascuns']);
    $viewer = User::factory()->create();
    $older->toggleLikeBy($viewer);

    Sanctum::actingAs($viewer);

    $this->getJson('/api/posts')
        ->assertOk()
        ->assertJsonPath('data.*.body', ['Al doilea', 'Primul'])
        ->assertJsonPath('data.1.liked_by_viewer', true)
        ->assertJsonPath('data.1.likes_count', 1)
        ->assertJsonPath('data.0.can_delete', false);
});

it('lists the posts nearest to the traveller first', function () {
    $brasov = Post::factory()->create(['body' => 'Brașov', 'latitude' => 45.6427, 'longitude' => 25.5887]);
    $cluj = Post::factory()->create(['body' => 'Cluj', 'latitude' => 46.7712, 'longitude' => 23.6236]);
    Post::factory()->create(['body' => 'Fără loc']);

    $this->getJson('/api/posts?feed=nearby&latitude=45.52&longitude=25.37')
        ->assertOk()
        ->assertJsonPath('data.*.body', ['Brașov', 'Cluj']);

    $this->getJson('/api/posts?feed=nearby')->assertJsonValidationErrors(['latitude', 'longitude']);
});

it('publishes a post with a photo, placed where its point of interest is', function () {
    Storage::fake('public');
    $bran = PointOfInterest::factory()->create(['name_ro' => 'Castelul Bran', 'latitude' => 45.5152, 'longitude' => 25.3672]);
    Sanctum::actingAs($author = User::factory()->create());

    $response = $this->post('/api/posts', [
        'body' => 'Am urcat devreme, înainte de grupuri.',
        'photo' => UploadedFile::fake()->image('bran.jpg', 3000, 2000),
        'point_of_interest_id' => $bran->id,
    ], ['Accept' => 'application/json']);

    $response->assertCreated()
        ->assertJsonPath('data.point_of_interest.name', 'Castelul Bran')
        ->assertJsonPath('data.author.id', $author->id)
        ->assertJsonPath('data.can_delete', true);

    $post = Post::firstOrFail();
    [$width] = getimagesizefromstring(Storage::disk('public')->get($post->image_path));

    expect($post->latitude)->toBe(45.5152)
        ->and($width)->toBe(1600)
        ->and($response->json('data.image_url'))->toStartWith('http');
});

it('lists posts by when they were written, not by when they were saved', function () {
    $older = Post::factory()->create(['body' => 'Mai vechi', 'created_at' => now()->subDays(2)]);
    $newer = Post::factory()->create(['body' => 'Mai nou', 'created_at' => now()->subHour()]);
    $backdated = Post::factory()->create(['body' => 'Scris acum o săptămână', 'created_at' => now()->subWeek()]);

    $this->getJson('/api/posts')->assertJsonPath('data.*.id', [$newer->id, $older->id, $backdated->id]);
});

it('never stores where the traveller was standing', function () {
    $bran = PointOfInterest::factory()->create(['latitude' => 45.5152, 'longitude' => 25.3672]);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/posts', [
        'body' => 'De la fereastra hotelului.',
        'point_of_interest_id' => $bran->id,
        'latitude' => 44.4268,
        'longitude' => 26.1025,
    ])->assertCreated();

    $this->postJson('/api/posts', ['body' => 'Fără loc.', 'latitude' => 44.4268, 'longitude' => 26.1025])->assertCreated();

    expect(Post::orderBy('id')->get()->map->only('latitude', 'longitude')->all())->toBe([
        ['latitude' => 45.5152, 'longitude' => 25.3672],
        ['latitude' => null, 'longitude' => null],
    ]);
});

it('places a post about an experience at the experience\'s first stop', function () {
    $start = PointOfInterest::factory()->create(['latitude' => 45.6427, 'longitude' => 25.5887]);
    $experience = Experience::factory()->through([$start, PointOfInterest::factory()->create()])->create();
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/posts', ['body' => 'Traseul întreg.', 'experience_id' => $experience->id])->assertCreated();

    expect(Post::firstOrFail()->latitude)->toBe(45.6427);
});

it('says which stop of an experience a post is about', function () {
    $castle = PointOfInterest::factory()->create();
    $museum = PointOfInterest::factory()->create();
    $experience = Experience::factory()->through([$castle, $museum])->create();
    $post = Post::factory()->create(['point_of_interest_id' => $museum->id, 'experience_id' => $experience->id]);

    $this->getJson("/api/posts/{$post->id}")
        ->assertOk()
        ->assertJsonPath('data.point_of_interest.stop_position', 2)
        ->assertJsonPath('data.experience.title', 'Traseul clasic');
});

it('refuses to tie a post to an unpublished place', function () {
    Sanctum::actingAs(User::factory()->create());
    $draft = PointOfInterest::factory()->unpublished()->create();

    $this->postJson('/api/posts', ['body' => 'Salut', 'point_of_interest_id' => $draft->id])
        ->assertJsonValidationErrors('point_of_interest_id');
});

it('shows a hidden post to its author only', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->hidden()->create();

    $this->getJson("/api/posts/{$post->id}")->assertNotFound();

    Sanctum::actingAs(User::factory()->create());
    $this->getJson("/api/posts/{$post->id}")->assertNotFound();

    Sanctum::actingAs($author);
    $this->getJson("/api/posts/{$post->id}")->assertOk();
});

it('lets only the author delete a post, and removes its photo', function () {
    Storage::fake('public');
    Storage::disk('public')->put('posts/a.jpg', 'x');
    $post = Post::factory()->create(['image_path' => 'posts/a.jpg']);

    Sanctum::actingAs(User::factory()->create());
    $this->deleteJson("/api/posts/{$post->id}")->assertForbidden();

    Sanctum::actingAs($post->user);
    $this->deleteJson("/api/posts/{$post->id}")->assertNoContent();

    expect(Post::find($post->id))->toBeNull();
    Storage::disk('public')->assertMissing('posts/a.jpg');
});

it('likes a post and takes the like back', function () {
    Sanctum::actingAs(User::factory()->create());
    $post = Post::factory()->create();

    $this->postJson("/api/posts/{$post->id}/like")->assertJsonPath('data', ['liked' => true, 'likes_count' => 1]);
    $this->postJson("/api/posts/{$post->id}/like")->assertJsonPath('data', ['liked' => false, 'likes_count' => 0]);
});

it('turns an old dislike into a like instead of keeping both', function () {
    $viewer = User::factory()->create();
    $post = Post::factory()->create();
    $post->reactions()->create(['user_id' => $viewer->id, 'reaction_type' => 'dislike']);

    Sanctum::actingAs($viewer);

    $this->postJson("/api/posts/{$post->id}/like")->assertJsonPath('data', ['liked' => true, 'likes_count' => 1]);
    expect($post->reactions()->count())->toBe(1);
});

it('keeps the comment count exact as comments come and go', function () {
    $post = Post::factory()->create();
    Sanctum::actingAs($commenter = User::factory()->create());

    $id = $this->postJson("/api/posts/{$post->id}/comments", ['body' => 'Ai mers și pe traseul Fără mașină?'])
        ->assertCreated()
        ->assertJsonPath('data.author.id', $commenter->id)
        ->json('data.id');

    expect($post->fresh()->comments_count)->toBe(1);

    $this->getJson("/api/posts/{$post->id}/comments")->assertJsonCount(1, 'data');
    $this->deleteJson("/api/comments/{$id}")->assertNoContent();

    expect($post->fresh()->comments_count)->toBe(0);
});

it('lets a traveller delete only their own comments', function () {
    $comment = Comment::factory()->create();
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson("/api/comments/{$comment->id}")->assertForbidden();
});

it('shows a public profile with what its posts gathered', function () {
    $ana = User::factory()->create(['first_name' => 'Ana', 'last_name' => 'Marin', 'city' => 'Brașov']);
    $post = Post::factory()->for($ana)->create();
    Post::factory()->for($ana)->hidden()->create();
    $post->toggleLikeBy(User::factory()->create());
    Comment::factory()->for($post, 'commentable')->create();

    $this->getJson("/api/users/{$ana->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Ana Marin')
        ->assertJsonPath('data.initials', 'AM')
        ->assertJsonPath('data.stats', ['posts' => 1, 'likes' => 1, 'comments' => 1])
        ->assertJsonMissingPath('data.email');

    $this->getJson("/api/users/{$ana->id}/posts")->assertJsonCount(1, 'data');
});

it('follows and unfollows a traveller, but not oneself', function () {
    $ana = User::factory()->create();
    Sanctum::actingAs($viewer = User::factory()->create());

    $this->postJson("/api/users/{$ana->id}/follow")->assertJsonPath('data', ['following' => true, 'followers_count' => 1]);
    $this->getJson("/api/users/{$ana->id}")->assertJsonPath('data.followed_by_viewer', true);
    $this->postJson("/api/users/{$ana->id}/follow")->assertJsonPath('data', ['following' => false, 'followers_count' => 0]);
    $this->postJson("/api/users/{$viewer->id}/follow")->assertUnprocessable();
});

it('lists a traveller\'s latest comments and reviews as activity', function () {
    $ana = User::factory()->create();
    $place = PointOfInterest::factory()->create(['name_ro' => 'Castelul Bran']);
    $place->reviewBy($ana, 5, 'Superb.');
    Comment::factory()->create(['created_by_user_id' => $ana->id, 'body' => 'Frumos!']);

    $this->getJson("/api/users/{$ana->id}/activity")
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['type' => 'review', 'rating' => 5])
        ->assertJsonFragment(['name' => 'Castelul Bran'])
        ->assertJsonFragment(['type' => 'comment', 'body' => 'Frumos!']);
});
