<?php

namespace App\Http\Controllers\Api\V1\Faq;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\FaqCategoryRequest;
use App\Http\Resources\FaqCategoryResource;
use App\Models\FaqCategory;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * Milestone 17 (FAQ) — groups the global Site FAQ by topic. Gated by
 * faq.* (FaqPolicy), same reasoning as BlogPolicy also governing
 * BlogCategory.
 */
class FaqCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', FaqCategory::class);

        $categories = FaqCategory::query()
            ->withCount('faqs')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 50));

        return ApiResponse::success(FaqCategoryResource::collection($categories));
    }

    public function store(FaqCategoryRequest $request): JsonResponse
    {
        Gate::authorize('create', FaqCategory::class);

        $category = FaqCategory::create($request->validated());

        return ApiResponse::success(new FaqCategoryResource($category), 201);
    }

    public function show(FaqCategory $faqCategory): JsonResponse
    {
        Gate::authorize('viewAny', FaqCategory::class);

        return ApiResponse::success(new FaqCategoryResource($faqCategory->loadCount('faqs')));
    }

    public function update(FaqCategoryRequest $request, FaqCategory $faqCategory): JsonResponse
    {
        Gate::authorize('update', FaqCategory::class);

        $faqCategory->update($request->validated());

        return ApiResponse::success(new FaqCategoryResource($faqCategory));
    }

    /**
     * category_id is nullOnDelete on faqs, so deleting a category just
     * leaves its FAQs uncategorized rather than being blocked — same
     * reasoning as BlogCategory's own destroy().
     */
    public function destroy(FaqCategory $faqCategory): Response
    {
        Gate::authorize('delete', FaqCategory::class);

        $faqCategory->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/faq-categories — public, unauthenticated.
     */
    public function publicIndex(): JsonResponse
    {
        $categories = FaqCategory::query()->orderBy('order')->get();

        return ApiResponse::success(FaqCategoryResource::collection($categories));
    }
}
