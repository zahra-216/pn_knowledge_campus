<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Media Library" row: Super
 * Admin/Administrator = Full; Content Editor/Marketing = Create/Edit (no
 * delete); Admissions = View only. Registered against both Media and
 * MediaFolder (AppServiceProvider) since the matrix treats folder
 * organization as part of the same Media Library module, not a separate
 * one.
 */
class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('media.view');
    }

    public function create(User $user): bool
    {
        return $user->can('media.create');
    }

    public function update(User $user): bool
    {
        return $user->can('media.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('media.delete');
    }
}
