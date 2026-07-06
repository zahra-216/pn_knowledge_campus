<?php

namespace App\Http\Controllers\Api\V1\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\BlogCategoryRequest;
use App\Http\Resources\BlogCategoryResource;
use App\Models\BlogCategory;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.4. SRS Permission Matrix, "Blog" row: Super
 * Admin/Administrator = Full; Content Editor/Marketing = Create/Edit;
 * Admissions = no access.
 */
class BlogCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', BlogCategory::class);

        $categories = BlogCategory::query()
            ->withCount('posts')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 50));

        return ApiResponse::success(BlogCategoryResource::collection($categories));
    }

    public function store(BlogCategoryRequest $request): JsonResponse
    {
        Gate::authorize('create', BlogCategory::class);

        $category = BlogCategory::create($request->validated());

        return ApiResponse::success(new BlogCategoryResource($category), 201);
    }

    public function show(BlogCategory $blogCategory): JsonResponse
    {
        Gate::authorize('viewAny', BlogCategory::class);

        return ApiResponse::success(new BlogCategoryResource($blogCategory->loadCount('posts')));
    }

    public function update(BlogCategoryRequest $request, BlogCategory $blogCategory): JsonResponse
    {
        Gate::authorize('update', BlogCategory::class);

        $blogCategory->update($request->validated());

        return ApiResponse::success(new BlogCategoryResource($blogCategory));
    }

    /**
     * category_id is nullOnDelete on blog_posts, so deleting a category
     * just leaves its posts uncategorized rather than being blocked —
     * same reasoning as CourseCategory's own destroy().
     */
    public function destroy(BlogCategory $blogCategory): Response
    {
        Gate::authorize('delete', BlogCategory::class);

        $blogCategory->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/blog-categories — public, unauthenticated. "List blog
     * categories" (API Design, Section 7.3).
     */
    public function publicIndex(): JsonResponse
    {
        $categories = BlogCategory::query()->orderBy('order')->get();

        return ApiResponse::success(BlogCategoryResource::collection($categories));
    }
}
