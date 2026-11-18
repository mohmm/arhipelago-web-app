<?php

namespace App\Policies;

use App\Models\LegalDocument;
use App\Models\User;

/**
 * Only `viewAny`, `view` and `update` are declared, and deliberately so: the
 * permission catalogue is reflected out of the policies, so every ability
 * declared here becomes an ability the roles screen can grant. There is no
 * `create` and no `delete`, because the set of documents is fixed by the routes
 * and footer links that point at them.
 */
class LegalDocumentPolicy
{
    /**
     * Super admins bypass all checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('legal_document.viewAny');
    }

    public function view(User $user, LegalDocument $legalDocument): bool
    {
        return $user->checkPermissionTo('legal_document.view');
    }

    public function update(User $user, LegalDocument $legalDocument): bool
    {
        return $user->checkPermissionTo('legal_document.update');
    }
}
