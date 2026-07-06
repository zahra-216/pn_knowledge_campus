<?php

namespace App\Http\Controllers\Api\V1\Homepage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Homepage\PartnerCategoryRequest;
use App\Http\Resources\PartnerCategoryResource;
use App\Models\PartnerCategory;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * Milestone 16 (Partners) — groups partners by type. Gated by
 * partners.* (same PartnerPolicy as Partner itself — one "Partners" row
 * in the SRS Permission Matrix), same reasoning as BlogPolicy also
 * governing BlogCategory.
 */
class PartnerCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', PartnerCategory::class);

        $categories = PartnerCategory::query()
            ->withCount('partners')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 50));

        return ApiResponse::success(PartnerCategoryResource::collection($categories));
    }

    public function store(PartnerCategoryRequest $request): JsonResponse
    {
        Gate::authorize('create', PartnerCategory::class);

        $category = PartnerCategory::create($request->validated());

        return ApiResponse::success(new PartnerCategoryResource($category), 201);
    }

    public function show(PartnerCategory $partnerCategory): JsonResponse
    {
        Gate::authorize('viewAny', PartnerCategory::class);

        return ApiResponse::success(new PartnerCategoryResource($partnerCategory->loadCount('partners')));
    }

    public function update(PartnerCategoryRequest $request, PartnerCategory $partnerCategory): JsonResponse
    {
        Gate::authorize('update', PartnerCategory::class);

        $partnerCategory->update($request->validated());

        return ApiResponse::success(new PartnerCategoryResource($partnerCategory));
    }

    /**
     * category_id is nullOnDelete on partners, so deleting a category
     * just leaves its partners uncategorized rather than being blocked —
     * same reasoning as BlogCategory's own destroy().
     */
    public function destroy(PartnerCategory $partnerCategory): Response
    {
        Gate::authorize('delete', PartnerCategory::class);

        $partnerCategory->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/partner-categories — public, unauthenticated.
     */
    public function publicIndex(): JsonResponse
    {
        $categories = PartnerCategory::query()->orderBy('order')->get();

        return ApiResponse::success(PartnerCategoryResource::collection($categories));
    }
}
