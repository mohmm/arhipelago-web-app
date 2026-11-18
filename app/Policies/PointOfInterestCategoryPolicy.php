<?php

namespace App\Policies;

use App\Models\PointOfInterestCategory;
use App\Models\User;

class PointOfInterestCategoryPolicy
{
    /**
     * Super admins bypass all checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('point_of_interest_category.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PointOfInterestCategory $category): bool
    {
        return $user->checkPermissionTo('point_of_interest_category.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('point_of_interest_category.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PointOfInterestCategory $category): bool
    {
        return $user->checkPermissionTo('point_of_interest_category.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PointOfInterestCategory $category): bool
    {
        return $user->checkPermissionTo('point_of_interest_category.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PointOfInterestCategory $category): bool
    {
        return $user->checkPermissionTo('point_of_interest_category.restore');
    }
}
