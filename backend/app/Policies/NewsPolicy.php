<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "News" row: Super
 * Admin/Administrator = Full (incl. delete/publish); Content Editor =
 * Create/Edit; Marketing = Create/Edit; Admissions = no access — the
 * identical split to Blog's own row. Governs both News and NewsCategory.
 */
class NewsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('news.view');
    }

    public function create(User $user): bool
    {
        return $user->can('news.create');
    }

    public function update(User $user): bool
    {
        return $user->can('news.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('news.delete');
    }

    public function publish(User $user): bool
    {
        return $user->can('news.publish');
    }
}
