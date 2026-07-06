<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Menu Builder" row: Super
 * Admin/Administrator = Full; every other role = no access at all.
 * Governs both Menu and MenuItem (AppServiceProvider) — one module, one
 * policy, same reasoning already applied to Media/MediaFolder.
 */
class MenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('menus.view');
    }

    public function update(User $user): bool
    {
        return $user->can('menus.edit');
    }
}
