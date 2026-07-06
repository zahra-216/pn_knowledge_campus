<?php

namespace App\Http\Controllers\Api\V1\Branches;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branches\BranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Models\Setting;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.9 (admin) and Section 7.5 (public). Branches are
 * a sub-part of the Settings module (SRS Section 7.4), so every write
 * here is gated by the same settings.edit permission as SettingController
 * — there is no separate branches.* permission set.
 */
class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Setting::class);

        $branches = Branch::orderBy('order')->paginate(20);

        return ApiResponse::success(BranchResource::collection($branches));
    }

    public function store(BranchRequest $request): JsonResponse
    {
        Gate::authorize('update', Setting::class);

        $branch = Branch::create($request->validated());

        PublicCache::forgetBranches();

        return ApiResponse::success(new BranchResource($branch), 201);
    }

    public function show(Branch $branch): JsonResponse
    {
        Gate::authorize('viewAny', Setting::class);

        return ApiResponse::success(new BranchResource($branch));
    }

    public function update(BranchRequest $request, Branch $branch): JsonResponse
    {
        Gate::authorize('update', Setting::class);

        $branch->update($request->validated());

        PublicCache::forgetBranches();

        return ApiResponse::success(new BranchResource($branch));
    }

    public function destroy(Branch $branch): Response
    {
        Gate::authorize('update', Setting::class);

        $branch->delete();

        PublicCache::forgetBranches();

        return response()->noContent();
    }

    /**
     * GET /api/v1/branches — public, active branches only, ordered.
     * Cached (see PublicCache's docblock).
     */
    public function publicIndex(): JsonResponse
    {
        $data = PublicCache::remember(
            'public.branches',
            fn () => BranchResource::collection(Branch::where('is_active', true)->orderBy('order')->get())->resolve()
        );

        return ApiResponse::success($data);
    }
}
