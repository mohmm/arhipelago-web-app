<?php

namespace App\Policies;

use App\Models\Experience;
use App\Models\User;

/**
 * Experiences are curated content, managed in the admin panel.
 *
 * Travellers read what is published through the API; changing it is staff work,
 * granted per ability through the `experience.*` permissions.
 */
class ExperiencePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('experience.viewAny');
    }

    public function view(User $user, Experience $experience): bool
    {
        return $user->checkPermissionTo('experience.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('experience.create');
    }

    public function update(User $user, Experience $experience): bool
    {
        return $user->checkPermissionTo('experience.update');
    }

    public function delete(User $user, Experience $experience): bool
    {
        return $user->checkPermissionTo('experience.delete');
    }
}
