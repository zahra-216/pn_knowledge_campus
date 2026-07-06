<?php

namespace App\Policies;

use App\Models\User;

/**
 * Users, Roles & Permissions module (SRS Permission Matrix, Section
 * 10's "Users, Roles & Permissions" row: Super Admin = Full; every
 * other role = no access at all). `users.*` permission keys were
 * already declared in RoleSeeder's master permission list from
 * Milestone 0's RBAC scaffold and granted to Super Admin via
 * `syncPermissions(Permission::all())` — no other role has ever been
 * given any of them, so this Policy is effectively Super-Admin-only in
 * practice today, but is written against the permission strings (not a
 * hardcoded role check) so that can change without touching this file,
 * the same as every other module.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user): bool
    {
        return $user->can('users.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('users.delete');
    }
}
