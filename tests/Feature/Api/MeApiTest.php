<?php

use App\Models\Comment;
use App\Models\PointOfInterest;
use App\Models\Post;
use App\Models\User;
use App\Notifications\Auth\PasswordChangedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

it('shows the traveller their own profile, with private fields', function () {
    $user = User::factory()->create(['travel_preferences' => 'Cetăți vechi și trasee scurte.']);
    Sanctum::actingAs($user);

    $this->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('data.travel_preferences', 'Cetăți vechi și trasee scurte.')
        ->assertJsonPath('data.is_viewer', true);
});

it('updates the profile, the language and what Maya knows', function () {
    Sanctum::actingAs($user = User::factory()->create());

    $this->putJson('/api/me', [
        'city' => 'Brașov',
        'bio' => 'Îmi plac traseele scurte.',
        'locale' => 'ar',
        'travel_preferences' => 'Castele și natură.',
    ])->assertOk()->assertJsonPath('data.city', 'Brașov');

    expect($user->fresh())
        ->locale->toBe('ar')
        ->travel_preferences->toBe('Castele și natură.');
});

it('rejects a language the app does not offer', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/me', ['locale' => 'fr'])->assertJsonValidationErrors('locale');
});

it('changes the password, signs out other devices and says so by email', function () {
    Notification::fake();
    $user = User::factory()->create();
    $token = $user->createToken('phone')->plainTextToken;
    $user->createToken('tablet');

    $this->withToken($token)->putJson('/api/me/password', [
        'current_password' => 'password',
        'password' => 'o-parola-noua-1',
        'password_confirmation' => 'o-parola-noua-1',
    ])->assertNoContent();

    expect($user->tokens()->pluck('name')->all())->toBe(['phone']);
    Notification::assertSentTo($user, PasswordChangedNotification::class);
});

it('asks for the current password before changing it', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/me/password', [
        'current_password' => 'wrong',
        'password' => 'o-parola-noua-1',
        'password_confirmation' => 'o-parola-noua-1',
    ])->assertJsonValidationErrors('current_password');
});

it('stores the avatar as a square and replaces the previous one', function () {
    Storage::fake('public');
    Sanctum::actingAs($user = User::factory()->create());

    $this->post('/api/me/avatar', ['avatar' => UploadedFile::fake()->image('me.jpg', 1200, 800)], ['Accept' => 'application/json'])->assertOk();
    $first = $user->fresh()->avatar_path;

    $this->post('/api/me/avatar', ['avatar' => UploadedFile::fake()->image('me.png', 600, 600)], ['Accept' => 'application/json'])->assertOk();
    $second = $user->fresh()->avatar_path;

    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);
    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($second));
    expect([$width, $height])->toBe([512, 512]);
});

it('deletes the account with everything it published, and recounts what it touched', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $post = Post::factory()->create(['image_path' => 'posts/photo.jpg']);
    Storage::disk('public')->put('posts/photo.jpg', 'x');
    $ownPost = Post::factory()->for($user)->create(['image_path' => 'posts/own.jpg']);
    Storage::disk('public')->put('posts/own.jpg', 'x');
    $place = PointOfInterest::factory()->create();

    $post->toggleLikeBy($user);
    $place->toggleLikeBy($user);
    Comment::factory()->for($post, 'commentable')->create(['created_by_user_id' => $user->id]);
    expect($post->fresh())->likes_count->toBe(1)->comments_count->toBe(1);

    Sanctum::actingAs($user);
    $this->deleteJson('/api/me', ['password' => 'password'])->assertNoContent();

    expect(User::find($user->id))->toBeNull()
        ->and(Post::find($ownPost->id))->toBeNull()
        ->and($post->fresh())->likes_count->toBe(0)->comments_count->toBe(0)
        ->and($place->fresh()->likes_count)->toBe(0);
    Storage::disk('public')->assertMissing('posts/own.jpg');
    Storage::disk('public')->assertExists('posts/photo.jpg');
});

it('will not delete the account without the password', function () {
    Sanctum::actingAs($user = User::factory()->create());

    $this->deleteJson('/api/me', ['password' => 'wrong'])->assertJsonValidationErrors('password');

    expect(User::find($user->id))->not->toBeNull();
});
