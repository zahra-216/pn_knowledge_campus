<?php

namespace App\Http\Controllers\Api\V1\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pages\PageBlockReorderRequest;
use App\Http\Requests\Pages\PageBlockRequest;
use App\Http\Resources\PageBlockResource;
use App\Models\Page;
use App\Models\PageBlock;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.3 — /admin/pages/{page}/blocks sub-resource.
 */
class PageBlockController extends Controller
{
    public function index(Page $page): JsonResponse
    {
        Gate::authorize('viewAny', PageBlock::class);

        return ApiResponse::success(PageBlockResource::collection($page->blocks));
    }

    public function store(PageBlockRequest $request, Page $page): JsonResponse
    {
        Gate::authorize('update', PageBlock::class);

        $block = $page->blocks()->create($request->validated());

        return ApiResponse::success($this->resolved($block), 201);
    }

    public function update(PageBlockRequest $request, Page $page, PageBlock $block): JsonResponse
    {
        Gate::authorize('update', PageBlock::class);

        $block->update($request->validated());

        return ApiResponse::success($this->resolved($block));
    }

    /**
     * A single PageBlockResource has a top-level `data` field (matching
     * the page_blocks.data column). Laravel's automatic response
     * wrapping detects any pre-existing `data` key and skips wrapping
     * entirely to avoid double-nesting — which here means it would skip
     * wrapping the *envelope*, breaking the API's standard
     * {"data": ...} contract. Resolving to a plain array first and
     * passing it through ApiResponse::success()'s array branch forces
     * the wrap regardless. Collections (index/reorder) aren't affected:
     * their resolved array is a numeric list, not a `data`-keyed array.
     */
    private function resolved(PageBlock $block): array
    {
        return (new PageBlockResource($block))->resolve();
    }

    public function destroy(Page $page, PageBlock $block): Response
    {
        Gate::authorize('update', PageBlock::class);

        $block->delete();

        return response()->noContent();
    }

    /**
     * PATCH /api/v1/admin/pages/{page}/blocks/reorder — bulk order
     * update, applied in one transaction (mirrors MenuItemController's
     * reorder).
     */
    public function reorder(PageBlockReorderRequest $request, Page $page): JsonResponse
    {
        Gate::authorize('update', PageBlock::class);

        DB::transaction(function () use ($request, $page) {
            foreach ($request->input('items') as $entry) {
                $page->blocks()->whereKey($entry['id'])->update(['order' => $entry['order']]);
            }
        });

        return ApiResponse::success(PageBlockResource::collection($page->blocks()->get()));
    }
}
