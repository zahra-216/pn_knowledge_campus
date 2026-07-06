<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Faculty Management" row: Super
 * Admin/Administrator = Full (incl. delete); Content Editor = Create/Edit;
 * Marketing = View; Admissions = View.
 */
class FacultyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('faculties.view');
    }

    public function create(User $user): bool
    {
        return $user->can('faculties.create');
    }

    public function update(User $user): bool
    {
        return $user->can('faculties.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('faculties.delete');
    }
}
