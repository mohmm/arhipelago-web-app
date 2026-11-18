<?php

namespace App\Policies;

use App\Models\Destination;
use App\Models\User;

/**
 * Destinations group experiences by area and are managed in the admin panel.
 *
 * Travellers read what is published through the API; changing it is staff work,
 * granted per ability through the `destination.*` permissions.
 */
class DestinationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('destination.viewAny');
    }

    public function view(User $user, Destination $destination): bool
    {
        return $user->checkPermissionTo('destination.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('destination.create');
    }

    public function update(User $user, Destination $destination): bool
    {
        return $user->checkPermissionTo('destination.update');
    }

    public function delete(User $user, Destination $destination): bool
    {
        return $user->checkPermissionTo('destination.delete');
    }
}
