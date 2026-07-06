<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Events" row: Super
 * Admin/Administrator = Full; Content Editor = Create/Edit; Marketing =
 * Create/Edit; Admissions = no access — identical split to Blog/News.
 * No `publish` ability: unlike Page/Course/Blog/News, the API Design
 * doesn't document a separate /publish endpoint for Events (Section
 * 8.4 lists only the five plain CRUD verbs) — status transitions
 * (including to 'published') go through the regular update endpoint,
 * gated by the same `update` ability every Content Editor/Marketing
 * user already has. Governs both Event and EventSpeaker.
 */
class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('events.view');
    }

    public function create(User $user): bool
    {
        return $user->can('events.create');
    }

    public function update(User $user): bool
    {
        return $user->can('events.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('events.delete');
    }
}
