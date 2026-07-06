<?php

namespace App\Http\Controllers\Api\V1\News;

use App\Http\Controllers\Controller;
use App\Http\Requests\News\NewsCategoryRequest;
use App\Http\Resources\NewsCategoryResource;
use App\Models\NewsCategory;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.4. SRS Permission Matrix, "News" row: Super
 * Admin/Administrator = Full; Content Editor/Marketing = Create/Edit;
 * Admissions = no access.
 */
class NewsCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', NewsCategory::class);

        $categories = NewsCategory::query()
            ->withCount('news')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 50));

        return ApiResponse::success(NewsCategoryResource::collection($categories));
    }

    public function store(NewsCategoryRequest $request): JsonResponse
    {
        Gate::authorize('create', NewsCategory::class);

        $category = NewsCategory::create($request->validated());

        return ApiResponse::success(new NewsCategoryResource($category), 201);
    }

    public function show(NewsCategory $newsCategory): JsonResponse
    {
        Gate::authorize('viewAny', NewsCategory::class);

        return ApiResponse::success(new NewsCategoryResource($newsCategory->loadCount('news')));
    }

    public function update(NewsCategoryRequest $request, NewsCategory $newsCategory): JsonResponse
    {
        Gate::authorize('update', NewsCategory::class);

        $newsCategory->update($request->validated());

        return ApiResponse::success(new NewsCategoryResource($newsCategory));
    }

    /**
     * category_id is nullOnDelete on news, so deleting a category just
     * leaves its articles uncategorized rather than being blocked — same
     * reasoning as BlogCategory's own destroy().
     */
    public function destroy(NewsCategory $newsCategory): Response
    {
        Gate::authorize('delete', NewsCategory::class);

        $newsCategory->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/news-categories — public, unauthenticated. "List news
     * categories" (API Design, Section 7.3).
     */
    public function publicIndex(): JsonResponse
    {
        $categories = NewsCategory::query()->orderBy('order')->get();

        return ApiResponse::success(NewsCategoryResource::collection($categories));
    }
}
