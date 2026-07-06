<?php

namespace App\Http\Controllers\Api\V1\Downloads;

use App\Http\Controllers\Controller;
use App\Http\Requests\Downloads\DownloadCategoryRequest;
use App\Http\Resources\DownloadCategoryResource;
use App\Models\DownloadCategory;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * Milestone 18 (Downloads) — groups the catalog by document type. Gated
 * by downloads.* (DownloadPolicy), same reasoning as BlogPolicy also
 * governing BlogCategory.
 */
class DownloadCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', DownloadCategory::class);

        $categories = DownloadCategory::query()
            ->withCount('downloads')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 50));

        return ApiResponse::success(DownloadCategoryResource::collection($categories));
    }

    public function store(DownloadCategoryRequest $request): JsonResponse
    {
        Gate::authorize('create', DownloadCategory::class);

        $category = DownloadCategory::create($request->validated());

        return ApiResponse::success(new DownloadCategoryResource($category), 201);
    }

    public function show(DownloadCategory $downloadCategory): JsonResponse
    {
        Gate::authorize('viewAny', DownloadCategory::class);

        return ApiResponse::success(new DownloadCategoryResource($downloadCategory->loadCount('downloads')));
    }

    public function update(DownloadCategoryRequest $request, DownloadCategory $downloadCategory): JsonResponse
    {
        Gate::authorize('update', DownloadCategory::class);

        $downloadCategory->update($request->validated());

        return ApiResponse::success(new DownloadCategoryResource($downloadCategory));
    }

    /**
     * category_id is nullOnDelete on downloads, so deleting a category
     * just leaves its downloads uncategorized rather than being blocked
     * — same reasoning as BlogCategory's own destroy().
     */
    public function destroy(DownloadCategory $downloadCategory): Response
    {
        Gate::authorize('delete', DownloadCategory::class);

        $downloadCategory->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/download-categories — public, unauthenticated.
     */
    public function publicIndex(): JsonResponse
    {
        $categories = DownloadCategory::query()->orderBy('order')->get();

        return ApiResponse::success(DownloadCategoryResource::collection($categories));
    }
}
