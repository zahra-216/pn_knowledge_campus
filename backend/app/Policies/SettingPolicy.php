<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

/**
 * SRS Permission Matrix (Section 10) — Settings is Super Admin only;
 * every other role gets nothing, not even read access. This also governs
 * Branches and Social Links (BranchController/SocialLinkController call
 * these same abilities directly), which the SRS treats as sub-parts of
 * the Settings module rather than modules of their own.
 */
class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('settings.view');
    }

    public function update(User $user, ?Setting $setting = null): bool
    {
        return $user->can('settings.edit');
    }
}
