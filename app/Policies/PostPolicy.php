<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

/**
 * A traveller's post in the community feed.
 *
 * Travellers manage their own; staff moderate everyone's through the
 * `post.moderate` and `post.delete` permissions.
 */
class PostPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    /**
     * See every post, hidden ones included, and hide or show them.
     */
    public function moderate(User $user): bool
    {
        return $user->checkPermissionTo('post.moderate');
    }

    public function view(User $user, Post $post): bool
    {
        return $post->is_approved || $post->user_id == $user->id || $user->checkPermissionTo('post.moderate');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Post $post): bool
    {
        return $post->user_id == $user->id || $user->checkPermissionTo('post.delete');
    }
}
