<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

/**
 * A traveller's comment on a post.
 *
 * Travellers manage their own; staff moderate everyone's through the
 * `comment.moderate` and `comment.delete` permissions.
 */
class CommentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    /**
     * See every comment, hidden ones included, and hide or show them.
     */
    public function moderate(User $user): bool
    {
        return $user->checkPermissionTo('comment.moderate');
    }

    public function view(User $user, Comment $comment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->created_by_user_id == $user->id || $user->checkPermissionTo('comment.delete');
    }
}
