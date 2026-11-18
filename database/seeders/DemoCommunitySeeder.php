<?php

namespace Database\Seeders;

use App\Enums\ExperienceKind;
use App\Models\Comment;
use App\Models\Experience;
use App\Models\PointOfInterest;
use App\Models\Post;
use App\Models\User;
use App\Services\Media\ImageStore;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Fills the community with demonstration travellers, so the app can be shown
 * and tested with a living feed: posts with photos, comments, likes, reviews
 * and follows.
 *
 * Demonstration only. Invented reviews are unlawful on a live service (EU
 * Directive 2019/2161 bans fake consumer reviews), so this refuses to run in
 * production, is never called by DatabaseSeeder, and everything it creates
 * belongs to accounts at EMAIL_DOMAIN, which `community:remove-demo` deletes
 * in one go before launch.
 *
 *     php artisan db:seed --class=DemoCommunitySeeder
 *
 * The texts and photos live in data/community.json and data/community-photos;
 * the photos are public domain or CC0, like every photo in the app. Likes and
 * follows are drawn with a fixed seed, so every run gives the same community.
 */
class DemoCommunitySeeder extends Seeder
{
    /**
     * RFC 2606 reserves `.invalid`: no real traveller can have an address
     * here, so these accounts can never be mistaken for real ones. (Test
     * factories use example.com, so that domain would not do.)
     */
    public const EMAIL_DOMAIN = 'smartsense-demo.invalid';

    public const DATA = __DIR__.'/data/community.json';

    public const PHOTOS = __DIR__.'/data/community-photos';

    /** Post photos are stored like the app's own uploads. */
    private const PHOTO_MAX_SIDE = 1600;

    /** @var array<string, User> */
    private array $users = [];

    /** @var array<string, Post> */
    private array $posts = [];

    public function __construct(private ImageStore $images, private string $data = self::DATA, private string $photos = self::PHOTOS) {}

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('The demonstration community is never seeded in production.');
        }

        if (User::query()->where('email', 'like', '%@'.self::EMAIL_DOMAIN)->exists()) {
            $this->command?->warn('The demonstration community is already there. Remove it first with `php artisan community:remove-demo`.');

            return;
        }

        $data = json_decode((string) file_get_contents($this->data), true, flags: JSON_THROW_ON_ERROR);
        mt_srand((int) $data['seed']);

        DB::transaction(function () use ($data): void {
            $this->travellers($data['users']);
            $this->follows();
            $this->stories($data['posts']);
            $this->comments($data['comments']);
            $this->reviews($data['reviews']);
            $this->likes($data['likes_per_traveller']);
        });

        $this->command?->info(sprintf('Demonstration community: %d travellers, %d posts, %d comments, %d reviews.',
            count($this->users), count($this->posts), count($data['comments']), count($data['reviews'])));
    }

    /**
     * @param  list<array<string, mixed>>  $users
     */
    private function travellers(array $users): void
    {
        foreach ($users as $data) {
            $joined = now()->subDays((int) $data['joined_days_ago']);

            $user = new User([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => Str::slug($data['first_name'].'.'.$data['last_name'], '.').'@'.self::EMAIL_DOMAIN,
                'password' => Str::password(32),
            ]);
            $user->forceFill([
                'city' => $data['city'] ?? null,
                'bio' => $data['bio'] ?? null,
                'travel_preferences' => $data['travel_preferences'] ?? null,
                'locale' => $data['locale'] ?? 'ro',
                'email_verified_at' => $joined,
                'created_at' => $joined,
                'updated_at' => $joined,
            ])->save();
            $user->assignRole('customer');

            $this->users[$data['key']] = $user;
        }
    }

    /**
     * Each traveller follows a few others.
     */
    private function follows(): void
    {
        $keys = array_keys($this->users);

        foreach ($this->users as $key => $user) {
            $others = array_values(array_diff($keys, [$key]));
            shuffle($others);
            $user->following()->attach(collect(array_slice($others, 0, mt_rand(2, 6)))->map(fn (string $other): int => $this->users[$other]->id)->all());
        }
    }

    /**
     * @param  list<array<string, mixed>>  $posts
     */
    private function stories(array $posts): void
    {
        foreach ($posts as $data) {
            $place = isset($data['place']) ? $this->place($data['place']) : null;
            $experience = isset($data['experience']) ? $this->experience($data['experience']) : null;

            if ((isset($data['place']) && $place == null) || (isset($data['experience']) && $experience == null)) {
                $this->command?->warn("Skipped a post about {$this->subjectName($data)}: it is not in the catalogue.");

                continue;
            }

            $post = new Post(['body' => $data['body'], 'point_of_interest_id' => $place?->id, 'experience_id' => $experience?->id]);
            $post->user()->associate($this->users[$data['author']]);
            $post->placeOnTheMap();

            if (isset($data['photo'])) {
                $path = $this->photos.'/'.$data['photo'];
                $post->image_path = $this->images->storeScaled(new UploadedFile($path, basename($path), test: true), Post::IMAGE_DIRECTORY, self::PHOTO_MAX_SIDE);
            }

            $post->created_at = $this->moment($data);
            $post->updated_at = $post->created_at;
            $post->save();

            $this->posts[$data['key']] = $post;
        }
    }

    /**
     * @param  list<array<string, mixed>>  $comments
     */
    private function comments(array $comments): void
    {
        foreach ($comments as $data) {
            $post = $this->posts[$data['post']] ?? null;

            if ($post == null) {
                continue;
            }

            $comment = new Comment(['body' => $data['body']]);
            $comment->commentable()->associate($post);
            $comment->forceFill([
                'created_by_user_id' => $this->users[$data['author']]->id,
                'created_at' => $post->created_at->copy()->addMinutes((int) $data['minutes_after']),
            ])->save();
        }
    }

    /**
     * @param  list<array<string, mixed>>  $reviews
     */
    private function reviews(array $reviews): void
    {
        foreach ($reviews as $data) {
            $target = isset($data['place']) ? $this->place($data['place']) : $this->experience($data['experience']);

            if ($target == null) {
                continue;
            }

            $review = $target->reviewBy($this->users[$data['author']], (int) $data['rating'], $data['body'] ?? null);
            $review->forceFill(['created_at' => $this->moment($data), 'updated_at' => $this->moment($data)])->saveQuietly();
        }
    }

    /**
     * Each traveller likes some places and some posts by others.
     *
     * @param  array{places: array{int, int}, posts: array{int, int}}  $range
     */
    private function likes(array $range): void
    {
        $places = PointOfInterest::query()->published()->get()->all();

        foreach ($this->users as $user) {
            shuffle($places);

            foreach (array_slice($places, 0, mt_rand(...$range['places'])) as $place) {
                $place->toggleLikeBy($user);
            }

            $posts = array_values(array_filter($this->posts, fn (Post $post): bool => $post->user_id != $user->id));
            shuffle($posts);

            foreach (array_slice($posts, 0, mt_rand(...$range['posts'])) as $post) {
                $post->toggleLikeBy($user);
            }
        }
    }

    private function place(string $slug): ?PointOfInterest
    {
        return PointOfInterest::query()->published()->where('slug', $slug)->first();
    }

    /**
     * An experience named "destination/kind", e.g. "bran/classic".
     */
    private function experience(string $name): ?Experience
    {
        [$destination, $kind] = explode('/', $name);

        return Experience::query()->published()
            ->where('kind', ExperienceKind::from($kind))
            ->whereRelation('destination', 'slug', $destination)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function subjectName(array $data): string
    {
        return (string) ($data['place'] ?? $data['experience']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function moment(array $data): Carbon
    {
        return now()->subDays((int) $data['days_ago'])->setTime((int) ($data['hour'] ?? 12), (int) ($data['minute'] ?? 0));
    }
}
