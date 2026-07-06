<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Partners" row: Super
 * Admin/Administrator = Full; Content Editor = no access; Marketing =
 * Create/Edit; Admissions = no access.
 */
class PartnerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('partners.view');
    }

    public function create(User $user): bool
    {
        return $user->can('partners.create');
    }

    public function update(User $user): bool
    {
        return $user->can('partners.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('partners.delete');
    }
}
