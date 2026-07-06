<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Page Builder" row: Super
 * Admin/Administrator = Full (including delete/publish); Content Editor =
 * Create/Edit only (delete is SA/Admin-only across all content modules
 * per the matrix's own note, and publish is an Administrator/Super Admin
 * action in Phase 1 per Section 5's narrative); Marketing = View only;
 * Admissions = no access. Governs both Page and PageBlock
 * (AppServiceProvider) — block CRUD is gated by the same `update` ability
 * as the owning page, mirroring the Menu/MenuItem pattern.
 */
class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('pages.view');
    }

    public function create(User $user): bool
    {
        return $user->can('pages.create');
    }

    public function update(User $user): bool
    {
        return $user->can('pages.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('pages.delete');
    }

    public function publish(User $user): bool
    {
        return $user->can('pages.publish');
    }
}
