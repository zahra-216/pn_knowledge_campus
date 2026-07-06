<?php

namespace App\Http\Controllers\Api\V1\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pages\PageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.3. SRS Permission Matrix — Page Builder: Super
 * Admin/Administrator = Full; Content Editor = Create/Edit; Marketing =
 * View; Admissions = no access.
 */
class PageController extends Controller
{
    /**
     * GET /admin/pages — "List pages (paginated, filterable)". Filters:
     * ?status=draft, ?search=term (title).
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Page::class);

        $pages = Page::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->withCount('blocks')
            ->orderBy('title')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(PageResource::collection($pages));
    }

    public function store(PageRequest $request): JsonResponse
    {
        Gate::authorize('create', Page::class);

        $page = Page::create($request->validated());

        return ApiResponse::success(new PageResource($page->load('blocks')), 201);
    }

    public function show(Page $page): JsonResponse
    {
        Gate::authorize('viewAny', Page::class);

        return ApiResponse::success(new PageResource($page->load('blocks')));
    }

    public function update(PageRequest $request, Page $page): JsonResponse
    {
        Gate::authorize('update', Page::class);

        $page->update($request->validated());

        return ApiResponse::success(new PageResource($page->load('blocks')));
    }

    public function destroy(Page $page): Response
    {
        Gate::authorize('delete', Page::class);

        $page->delete();

        return response()->noContent();
    }

    /**
     * PATCH /admin/pages/{id}/publish — "Publish a draft/scheduled page."
     * Sets published_at on first publish only, so republishing an
     * already-published page doesn't wipe its original publish date.
     */
    public function publish(Page $page): JsonResponse
    {
        Gate::authorize('publish', Page::class);

        $page->update([
            'status' => 'published',
            'published_at' => $page->published_at ?? Carbon::now(),
        ]);

        return ApiResponse::success(new PageResource($page->load('blocks')));
    }

    /**
     * GET /api/v1/pages/{slug} — public, unauthenticated. Only active
     * blocks on a publicly visible page are returned, ordered.
     */
    public function publicShow(string $slug): JsonResponse
    {
        $page = Page::publiclyVisible()->where('slug', $slug)->firstOrFail();

        $page->load(['blocks' => fn ($q) => $q->where('is_active', true), 'seoMeta']);

        return ApiResponse::success(new PageResource($page));
    }
}
