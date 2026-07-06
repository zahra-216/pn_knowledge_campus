<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Users, Roles & Permissions module (SRS FR-29). Gated by raw
 * `roles.*`/`users.view` ability strings rather than a Policy class —
 * Spatie's Role/Permission models are vendor package models with no
 * natural "owner" in this app the way every other Policy target is one
 * of this project's own App\Models classes, so binding a Gate::policy()
 * to them would be an odd fit; a plain ability-string check (the same
 * escape hatch FaqController and ApplicationPolicy's siblings use when
 * a natural Policy binding isn't available) is simpler here.
 *
 * The five baseline roles seeded by RoleSeeder (Super Admin,
 * Administrator, Content Editor, Marketing, Admissions) can have their
 * permission set edited like any other role, but can never be renamed
 * or deleted — RoleSeeder's own re-seed logic assumes those exact names
 * exist, and the frontend's usePermission hook has one hardcoded
 * "Super Admin" shortcut (see its own docblock) that would silently
 * stop matching if that name changed.
 */
class RoleController extends Controller
{
    private const BASELINE_ROLES = ['Super Admin', 'Administrator', 'Content Editor', 'Marketing', 'Admissions'];

    public function index(): JsonResponse
    {
        Gate::authorize('roles.view');

        $roles = Role::with('permissions')->withCount('users')->orderBy('name')->get();

        return ApiResponse::success(RoleResource::collection($roles));
    }

    public function show(Role $role): JsonResponse
    {
        Gate::authorize('roles.view');

        return ApiResponse::success(new RoleResource($role->load('permissions')->loadCount('users')));
    }

    /** GET /admin/permissions — every permission key, grouped by module, for building a role's permission checklist. */
    public function permissions(): JsonResponse
    {
        Gate::authorize('roles.view');

        $grouped = Permission::orderBy('name')->get()->pluck('name')
            ->groupBy(fn (string $name) => explode('.', $name)[0]);

        return ApiResponse::success($grouped->all());
    }

    public function store(RoleRequest $request): JsonResponse
    {
        Gate::authorize('roles.create');

        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'sanctum']);
        $role->syncPermissions($request->input('permissions', []));

        return ApiResponse::success(new RoleResource($role->load('permissions')->loadCount('users')), 201);
    }

    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        Gate::authorize('roles.edit');

        if ($request->filled('name') && $request->validated('name') !== $role->name) {
            abort_if(in_array($role->name, self::BASELINE_ROLES, true), 422, 'The five baseline roles cannot be renamed.');
            $role->update(['name' => $request->validated('name')]);
        }

        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions', []));
        }

        return ApiResponse::success(new RoleResource($role->fresh(['permissions'])->loadCount('users')));
    }

    public function destroy(Role $role): Response
    {
        Gate::authorize('roles.delete');

        abort_if(in_array($role->name, self::BASELINE_ROLES, true), 422, 'The five baseline roles cannot be deleted.');
        abort_if($role->users()->exists(), 422, 'Reassign every user with this role before deleting it.');

        $role->delete();

        return response()->noContent();
    }
}
