<?php

namespace App\Services\Accounts;

use App\Models\PointOfInterest;
use App\Models\Post;
use App\Models\User;
use App\Services\Media\ImageStore;
use Illuminate\Support\Facades\DB;

/**
 * Deletes a traveller's account and everything it published.
 *
 * The database removes the rows (posts, comments, likes, reviews,
 * conversations with Maya) through its foreign keys. This class does what the
 * database cannot: it deletes the photo files, and recounts the likes and
 * comments on what the traveller had liked or commented on, so no counter
 * keeps a person who is no longer there.
 */
class DeleteAccount
{
    public function __construct(private ImageStore $images) {}

    public function handle(User $user): void
    {
        $files = $user->posts()->whereNotNull('image_path')->pluck('image_path')
            ->push($user->avatar_path)
            ->filter()
            ->all();

        DB::transaction(function () use ($user): void {
            $postsCommentedOn = $user->comments()->where('commentable_type', 'post')->pluck('commentable_id');
            $likedPosts = $user->reactions()->where('reactionable_type', 'post')->pluck('reactionable_id');
            $likedPlaces = $user->reactions()->where('reactionable_type', 'point_of_interest')->pluck('reactionable_id');

            $user->tokens()->delete();
            $user->delete();

            Post::query()->whereKey($postsCommentedOn->merge($likedPosts)->unique())->get()
                ->each(function (Post $post): void {
                    $post->refreshCommentsCount();
                    $post->forceFill(['likes_count' => $post->likes()->count()])->saveQuietly();
                });

            PointOfInterest::query()->whereKey($likedPlaces->unique())->get()
                ->each(fn (PointOfInterest $place) => $place->forceFill(['likes_count' => $place->likes()->count()])->saveQuietly());
        });

        foreach ($files as $path) {
            $this->images->delete($path);
        }
    }
}
