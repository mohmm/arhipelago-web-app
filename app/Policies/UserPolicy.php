<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Super admins bypass all checks except delete, which is resolved explicitly.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($ability == 'delete') {
            return null;
        }

        return $user->isSuperAdmin() ? true : null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('user.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->id == $model->id) {
            return true;
        }

        if (! $this->outranks($user, $model)) {
            return false;
        }

        return $user->checkPermissionTo('user.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('user.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * There is deliberately no self-edit shortcut here. This policy guards the
     * user administration screen, which can also change a user's role, so
     * allowing it on your own account lets anyone who can reach the admin panel
     * promote themselves. Editing your own profile lives at /settings/profile.
     */
    public function update(User $user, User $model): bool
    {
        if (! $this->outranks($user, $model)) {
            return false;
        }

        return $user->checkPermissionTo('user.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id == $model->id) {
            return false;
        }

        if (! $this->outranks($user, $model)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->checkPermissionTo('user.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->checkPermissionTo('user.restore');
    }

    /**
     * Determine whether the actor outranks the target user.
     *
     * A non-super-admin may never act on a super-admin account.
     */
    protected function outranks(User $user, User $model): bool
    {
        return $user->isSuperAdmin() || ! $model->isSuperAdmin();
    }
}
