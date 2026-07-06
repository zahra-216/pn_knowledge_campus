<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Department Management" row: Super
 * Admin/Administrator = Full (incl. delete); Content Editor = Create/Edit;
 * Marketing = View; Admissions = View — identical split to Faculty
 * Management.
 */
class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('departments.view');
    }

    public function create(User $user): bool
    {
        return $user->can('departments.create');
    }

    public function update(User $user): bool
    {
        return $user->can('departments.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('departments.delete');
    }
}
