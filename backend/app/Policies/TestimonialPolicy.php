<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Testimonials" row: Super
 * Admin/Administrator = Full; Content Editor = Create/Edit; Marketing =
 * Create/Edit; Admissions = no access.
 */
class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('testimonials.view');
    }

    public function create(User $user): bool
    {
        return $user->can('testimonials.create');
    }

    public function update(User $user): bool
    {
        return $user->can('testimonials.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('testimonials.delete');
    }
}
