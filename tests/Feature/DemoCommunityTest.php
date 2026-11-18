<?php

use App\Models\Comment;
use App\Models\PointOfInterest;
use App\Models\Post;
use App\Models\Review;
use App\Models\User;
use App\Services\Media\ImageStore;
use Database\Seeders\DemoCommunitySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    seedRoles();

    $this->bran = PointOfInterest::factory()->create(['slug' => 'castelul-bran', 'latitude' => 45.515, 'longitude' => 25.3671]);
    $this->photos = sys_get_temp_dir().'/demo-community-'.uniqid();
    mkdir($this->photos);
    // Held in a variable: the fake file is deleted as soon as nothing refers to it.
    $photo = UploadedFile::fake()->image('bran.jpg', 2400, 1600);
    copy($photo->getRealPath(), $this->photos.'/bran.jpg');

    $this->data = $this->photos.'/community.json';
    file_put_contents($this->data, json_encode([
        'seed' => 7,
        'likes_per_traveller' => ['places' => [1, 1], 'posts' => [1, 1]],
        'users' => [
            ['key' => 'ana', 'first_name' => 'Ana', 'last_name' => 'Popescu', 'city' => 'Brașov', 'joined_days_ago' => 90],
            ['key' => 'mihai', 'first_name' => 'Mihai', 'last_name' => 'Ionescu', 'city' => 'Iași', 'joined_days_ago' => 60],
            ['key' => 'ioana', 'first_name' => 'Ioana', 'last_name' => 'Dumitru', 'joined_days_ago' => 30],
        ],
        'posts' => [
            ['key' => 'bran', 'author' => 'ana', 'place' => 'castelul-bran', 'body' => 'Am urcat dimineața, înainte de grupuri.', 'photo' => 'bran.jpg', 'days_ago' => 10, 'hour' => 9],
            ['key' => 'missing', 'author' => 'mihai', 'place' => 'not-in-the-catalogue', 'body' => 'Nu apare.', 'days_ago' => 5],
        ],
        'comments' => [['post' => 'bran', 'author' => 'mihai', 'body' => 'Superbă lumina!', 'minutes_after' => 90]],
        'reviews' => [['author' => 'ioana', 'place' => 'castelul-bran', 'rating' => 5, 'body' => 'Merită.', 'days_ago' => 3]],
    ]));
});

afterEach(function () {
    array_map('unlink', glob($this->photos.'/*'));
    rmdir($this->photos);
});

function seedDemoCommunity(): void
{
    (new DemoCommunitySeeder(app(ImageStore::class), test()->data, test()->photos))->run();
}

it('fills the community from its data, with addresses no real traveller can own', function () {
    seedDemoCommunity();

    $travellers = User::query()->where('email', 'like', '%@'.DemoCommunitySeeder::EMAIL_DOMAIN)->get();
    $post = Post::sole();

    expect($travellers)->toHaveCount(3)
        ->and($travellers->every(fn (User $user): bool => $user->hasRole('customer')))->toBeTrue()
        ->and($post->latitude)->toBe(45.515)
        ->and($post->comments_count)->toBe(1)
        ->and($post->created_at->isSameDay(now()->subDays(10)))->toBeTrue()
        ->and(Comment::sole()->created_at->toDateTimeString())->toBe($post->created_at->copy()->addMinutes(90)->toDateTimeString())
        ->and(Review::sole()->rating)->toBe(5)
        ->and($this->bran->fresh()->likes_count)->toBe(3);

    Storage::disk('public')->assertExists($post->image_path);
});

it('fills the community only once', function () {
    seedDemoCommunity();
    seedDemoCommunity();

    expect(User::query()->where('email', 'like', '%@'.DemoCommunitySeeder::EMAIL_DOMAIN)->count())->toBe(3)
        ->and(Post::count())->toBe(1);
});

it('never fills the community of a live service', function () {
    app()->detectEnvironment(fn (): string => 'production');

    expect(fn () => seedDemoCommunity())->toThrow(RuntimeException::class);

    expect(User::query()->where('email', 'like', '%@'.DemoCommunitySeeder::EMAIL_DOMAIN)->count())->toBe(0);
});

it('removes the demonstration community and nothing else', function () {
    seedDemoCommunity();
    $traveller = User::factory()->create();
    $this->bran->toggleLikeBy($traveller);
    $realPost = Post::factory()->for($traveller)->create();

    $this->artisan('community:remove-demo')->assertSuccessful();

    expect(User::query()->where('email', 'like', '%@'.DemoCommunitySeeder::EMAIL_DOMAIN)->count())->toBe(0)
        ->and(Post::pluck('id')->all())->toBe([$realPost->id])
        ->and(Review::count())->toBe(0)
        ->and(Comment::count())->toBe(0)
        ->and($this->bran->fresh()->likes_count)->toBe(1);
});
