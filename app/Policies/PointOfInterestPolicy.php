<?php

namespace App\Policies;

use App\Models\PointOfInterest;
use App\Models\User;

/**
 * Points of interest are curated content, managed in the admin panel.
 *
 * Travellers read what is published through the API; changing it is staff work,
 * granted per ability through the `point_of_interest.*` permissions.
 */
class PointOfInterestPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('point_of_interest.viewAny');
    }

    public function view(User $user, PointOfInterest $pointOfInterest): bool
    {
        return $user->checkPermissionTo('point_of_interest.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('point_of_interest.create');
    }

    public function update(User $user, PointOfInterest $pointOfInterest): bool
    {
        return $user->checkPermissionTo('point_of_interest.update');
    }

    public function delete(User $user, PointOfInterest $pointOfInterest): bool
    {
        return $user->checkPermissionTo('point_of_interest.delete');
    }
}
