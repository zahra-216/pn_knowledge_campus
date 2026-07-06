<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Hero Slider" row: Super
 * Admin/Administrator = Full (incl. delete); Content Editor = no access;
 * Marketing = Create/Edit (no delete — delete stays SA/Admin-only across
 * every content module per the matrix's own note); Admissions = no access.
 */
class HeroSlidePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hero_slides.view');
    }

    public function create(User $user): bool
    {
        return $user->can('hero_slides.create');
    }

    public function update(User $user): bool
    {
        return $user->can('hero_slides.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('hero_slides.delete');
    }
}
