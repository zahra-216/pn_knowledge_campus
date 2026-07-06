<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserCreateRequest;
use App\Http\Requests\Users\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

/**
 * Users, Roles & Permissions module (SRS FR-29) — staff account CRUD,
 * gated by UserPolicy (users.*, Super-Admin-only in practice today).
 * Role management itself (listing/creating custom roles, editing a
 * role's permission set) lives in RoleController — this controller only
 * ever assigns a user to exactly one already-existing role by name (see
 * User model's own docblock: "every row here is assigned exactly one
 * role").
 */
class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $users = User::query()
            ->when($request->filled('filter.role'), fn ($q) => $q->role($request->input('filter.role')))
            ->when($request->has('filter.is_active'), fn ($q) => $q->where('is_active', $request->boolean('filter.is_active')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search');
                $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(UserResource::collection($users));
    }

    public function show(User $user): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        return ApiResponse::success(new UserResource($user));
    }

    public function store(UserCreateRequest $request): JsonResponse
    {
        Gate::authorize('create', User::class);

        $user = User::create([
            ...$request->safe()->except(['role', 'password']),
            'password' => Hash::make($request->validated('password')),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->syncRoles([$request->validated('role')]);

        return ApiResponse::success(new UserResource($user), 201);
    }

    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        Gate::authorize('update', User::class);

        $data = $request->safe()->except(['role', 'password']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        // A Super Admin can't lock themselves out by deactivating their
        // own account — every other field (name/email/phone/role) can
        // still be self-edited.
        if ($user->is($request->user()) && $request->has('is_active') && ! $request->boolean('is_active')) {
            return ApiResponse::error('You cannot deactivate your own account.', 422);
        }

        $user->update($data);

        if ($request->filled('role')) {
            $this->guardAgainstRemovingLastSuperAdmin($user, $request->validated('role'));
            $user->syncRoles([$request->validated('role')]);
        }

        return ApiResponse::success(new UserResource($user->fresh()));
    }

    public function destroy(Request $request, User $user): Response
    {
        Gate::authorize('delete', User::class);

        abort_if($user->is($request->user()), 422, 'You cannot delete your own account.');
        $this->guardAgainstRemovingLastSuperAdmin($user, null);

        $user->delete();

        return response()->noContent();
    }

    /**
     * Losing every Super Admin would mean nobody left holding the
     * users, settings, or roles permission groups — an unrecoverable
     * lockout without direct database access. $newRole === null means
     * "this user is being removed entirely" (destroy); a non-null value
     * means "this user is being reassigned away from Super Admin"
     * (update).
     */
    private function guardAgainstRemovingLastSuperAdmin(User $user, ?string $newRole): void
    {
        if (! $user->hasRole('Super Admin')) {
            return;
        }

        if ($newRole === 'Super Admin') {
            return;
        }

        $remaining = User::role('Super Admin')->where('id', '!=', $user->id)->exists();

        abort_if(! $remaining, 422, 'At least one Super Admin must remain.');
    }
}
