<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Blog" row: Super
 * Admin/Administrator = Full (incl. delete/publish); Content Editor =
 * Create/Edit; Marketing = Create/Edit (unlike Faculty/Course, Marketing
 * gets write access here — Section 5's narrative lists Blog among
 * Marketing's outward-facing/promotional modules); Admissions = no
 * access. Governs BlogPost, BlogCategory, and Tag — one "Blog" row
 * covers all three (Tag is shared with a future News module too, but
 * only Blog consumes it today).
 */
class BlogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('blog.view');
    }

    public function create(User $user): bool
    {
        return $user->can('blog.create');
    }

    public function update(User $user): bool
    {
        return $user->can('blog.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('blog.delete');
    }

    public function publish(User $user): bool
    {
        return $user->can('blog.publish');
    }
}
