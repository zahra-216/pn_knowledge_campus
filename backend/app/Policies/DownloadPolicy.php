<?php

namespace App\Policies;

use App\Models\User;

/**
 * Milestone 18 (Downloads) — same Content Editor/Marketing =
 * Create/Edit, Admissions = no access split as Testimonials/Gallery/FAQ.
 * Governs both Download and DownloadCategory (one "Downloads" row in the
 * SRS Permission Matrix), same reasoning as BlogPolicy also governing
 * BlogCategory. Unlike FaqPolicy, there's no cross-model collision here
 * — Download is a brand-new model with no prior policy binding.
 */
class DownloadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('downloads.view');
    }

    public function create(User $user): bool
    {
        return $user->can('downloads.create');
    }

    public function update(User $user): bool
    {
        return $user->can('downloads.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('downloads.delete');
    }
}
