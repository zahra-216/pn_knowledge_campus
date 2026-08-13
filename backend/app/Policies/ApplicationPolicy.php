<?php

namespace App\Policies;

use App\Models\User;

/**
 * Milestone 20 (Online Applications) — this is Admissions' own module,
 * the way Course Management already gives Admissions elevated access
 * beyond its usual read-only role elsewhere (see CoursePolicy's
 * docblock: "the one module besides Faculty/Department where it gets
 * more than read access"). Content Editor/Marketing get nothing here —
 * applications carry applicant PII, not editorial content.
 */
class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('applications.view');
    }

    /** Approve / reject / mark under review. */
    public function review(User $user): bool
    {
        return $user->can('applications.review');
    }

    public function export(User $user): bool
    {
        return $user->can('applications.export');
    }

    /**
     * Audit fix (Low remediation) — narrowly scoped to abandoned drafts
     * only (enforced in ApplicationController::destroy(), not here,
     * since a Policy checks "can this user act on this resource type",
     * not resource state). Reuses applications.review rather than a new
     * permission — the same Super Admin/Administrator/Admissions roles
     * already trusted to decide an application's outcome.
     */
    public function delete(User $user): bool
    {
        return $user->can('applications.review');
    }
}
