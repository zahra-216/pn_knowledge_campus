<?php

namespace App\Policies;

use App\Models\User;

/**
 * Milestone 17 (FAQ) — global Site FAQ row, same Content Editor/
 * Marketing = Create/Edit, Admissions = no access split as Testimonials/
 * Gallery. Governs FaqCategory directly (Gate::policy binding). The
 * global Faq model itself can't be bound here too — Faq::class is
 * already bound to CoursePolicy for the course-scoped sub-resource (one
 * model, one policy) — so FaqController checks these same faq.*
 * permissions via raw Gate::authorize() ability strings instead of a
 * class-based check. See FaqController's docblock.
 */
class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('faq.view');
    }

    public function create(User $user): bool
    {
        return $user->can('faq.create');
    }

    public function update(User $user): bool
    {
        return $user->can('faq.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('faq.delete');
    }
}
