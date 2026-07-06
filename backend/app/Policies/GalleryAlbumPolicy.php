<?php

namespace App\Policies;

use App\Models\User;

/**
 * SRS Permission Matrix (Section 10), "Gallery" row: Super
 * Admin/Administrator = Full; Content Editor = Create/Edit; Marketing =
 * Create/Edit; Admissions = no access — identical split to Blog/News/
 * Events. No `publish` ability — like Events, the API Design doesn't
 * document a separate publish endpoint for Gallery Albums; visibility is
 * just the `is_active` flag, set directly via the regular update
 * endpoint.
 */
class GalleryAlbumPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gallery.view');
    }

    public function create(User $user): bool
    {
        return $user->can('gallery.create');
    }

    public function update(User $user): bool
    {
        return $user->can('gallery.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('gallery.delete');
    }
}
