<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Course Management" row: Super
 * Admin/Administrator = Full (incl. delete/publish); Content Editor =
 * Create/Edit; Marketing = View; Admissions = Create/Edit (Admissions is
 * the enrollment-funnel role, per Section 5's narrative — the one module
 * besides Faculty/Department where it gets more than read access).
 * Governs Course, CourseCurriculumItem, and Faq (course-scoped) — one
 * "Course Management" row covers all three.
 */
class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('courses.view');
    }

    public function create(User $user): bool
    {
        return $user->can('courses.create');
    }

    public function update(User $user): bool
    {
        return $user->can('courses.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('courses.delete');
    }

    public function publish(User $user): bool
    {
        return $user->can('courses.publish');
    }
}
