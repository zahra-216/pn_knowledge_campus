<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Homepage Builder" row: Super
 * Admin/Administrator = Full; Content Editor = no access; Marketing =
 * Create/Edit (mapped here to the single `update` ability, since
 * sections are a fixed seeded set — there's nothing to "create"); no
 * access for Admissions.
 */
class HomepageSectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('homepage.view');
    }

    public function update(User $user): bool
    {
        return $user->can('homepage.edit');
    }
}
