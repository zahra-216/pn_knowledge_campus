<?php

namespace App\Policies;

use App\Models\User;

/**
 * Inquiry Management admin inbox (SRS Permission Matrix, Section 10's
 * "Inquiry Management" row: Super Admin/Administrator/Admissions =
 * Full; Marketing = View; Content Editor = no access). Mirrors
 * ApplicationPolicy's shape exactly — `manage` covers status changes
 * and deleting spam, the same "review" idea under a different name
 * since Inquiry has no approve/reject workflow, just a flat status set.
 */
class InquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('inquiries.view');
    }

    public function manage(User $user): bool
    {
        return $user->can('inquiries.manage');
    }

    public function export(User $user): bool
    {
        return $user->can('inquiries.export');
    }
}
