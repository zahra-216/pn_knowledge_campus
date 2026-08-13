<?php

namespace App\Http\Controllers\Api\V1\News;

use App\Http\Controllers\Controller;
use App\Http\Requests\News\NewsGalleryRequest;
use App\Http\Requests\News\NewsRequest;
use App\Http\Resources\NewsResource;
use App\Models\Media;
use App\Models\News;
use App\Models\NewsCategory;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.4. SRS Permission Matrix, "News" row: Super
 * Admin/Administrator = Full (incl. delete/publish); Content Editor =
 * Create/Edit; Marketing = Create/Edit; Admissions = no access.
 */
class NewsController extends Controller
{
    // 'media' (audit fix, Medium remediation) — see CourseController's
    // identical WITH-constant comment for why this avoids an N+1 on
    // NewsResource's featured_image/gallery lookups.
    private const WITH = ['category', 'author', 'media'];

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', News::class);

        $articles = News::query()
            ->with(self::WITH)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->filled('author'), fn ($q) => $q->where('author_id', $request->integer('author')))
            ->when($request->boolean('featured'), fn ($q) => $q->where('is_featured', true))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($sq) => $sq
                ->where('title', 'like', '%'.$request->string('search').'%')
                ->orWhere('excerpt', 'like', '%'.$request->string('search').'%')))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(NewsResource::collection($articles));
    }

    public function store(NewsRequest $request): JsonResponse
    {
        Gate::authorize('create', News::class);

        $article = DB::transaction(function () use ($request) {
            $data = $request->safe()->except(['featured_image_media_id', 'gallery_media_ids', 'seo']);
            $data['author_id'] = $data['author_id'] ?? Auth::id();

            $article = News::create($data);

            $this->syncMedia($article, $request);
            $this->syncSeo($article, $request);

            return $article;
        });

        // Audit fix (Medium remediation) — see TestimonialController's docblock.
        PublicCache::forgetHomepage();

        return ApiResponse::success(new NewsResource($article->fresh()->load(self::WITH)), 201);
    }

    public function show(News $news): JsonResponse
    {
        Gate::authorize('viewAny', News::class);

        return ApiResponse::success(new NewsResource($news->load(self::WITH)));
    }

    public function update(NewsRequest $request, News $news): JsonResponse
    {
        Gate::authorize('update', News::class);

        DB::transaction(function () use ($request, $news) {
            $news->update($request->safe()->except(['featured_image_media_id', 'gallery_media_ids', 'seo']));

            $this->syncMedia($news, $request);
            $this->syncSeo($news, $request);
        });

        PublicCache::forgetHomepage();

        return ApiResponse::success(new NewsResource($news->fresh()->load(self::WITH)));
    }

    /**
     * Soft delete (Database Design, Section 2.1).
     */
    public function destroy(News $news): Response
    {
        Gate::authorize('delete', News::class);

        $news->delete();

        PublicCache::forgetHomepage();

        return response()->noContent();
    }

    /**
     * PATCH /api/v1/admin/news/{id}/publish — "Publish a
     * draft/scheduled article." Sets published_at on first publish only.
     */
    public function publish(News $news): JsonResponse
    {
        Gate::authorize('publish', News::class);

        $news->update([
            'status' => 'published',
            'published_at' => $news->published_at ?? Carbon::now(),
        ]);

        PublicCache::forgetHomepage();

        return ApiResponse::success(new NewsResource($news->fresh()->load(self::WITH)));
    }

    /**
     * POST /api/v1/admin/news/{id}/media — attach existing Media Library
     * items to this article's gallery collection.
     */
    public function attachMedia(NewsGalleryRequest $request, News $news): JsonResponse
    {
        Gate::authorize('update', News::class);

        foreach ($request->input('media_ids') as $mediaId) {
            /** @var Media $media */
            $media = Media::findOrFail($mediaId);
            $media->moveKeepingCustomFields($news, 'gallery');
        }

        return ApiResponse::success(new NewsResource($news->fresh()->load(self::WITH)));
    }

    /**
     * DELETE /api/v1/admin/news/{id}/media/{mediaId} — remove one item
     * from the article's gallery (not in the API Design's own endpoint
     * list, but mirrors FacultyController::detachGallery's same
     * reasoning).
     */
    public function detachMedia(News $news, int $mediaId): Response
    {
        Gate::authorize('update', News::class);

        $news->getMedia('gallery')->where('id', $mediaId)->each->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/news — public, unauthenticated. "List published news.
     * filter[category], search supported" (API Design, Section 7.3).
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $filters = $request->input('filter', []);

        $articles = News::published()
            ->with(self::WITH)
            ->when(! empty($filters['category']), function ($query) use ($filters) {
                $category = NewsCategory::where('slug', $filters['category'])->first();
                $query->where('category_id', $category?->id ?? 0);
            })
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($sq) => $sq
                ->where('title', 'like', '%'.$request->string('search').'%')
                ->orWhere('excerpt', 'like', '%'.$request->string('search').'%')))
            ->orderByDesc('published_at')
            ->paginate($request->integer('per_page', 12));

        return ApiResponse::success(NewsResource::collection($articles));
    }

    /**
     * GET /api/v1/news/{slug} — public, unauthenticated. Article detail;
     * increments the denormalized views_count (Database Design, Section
     * 4.6) on each view, same as BlogPostController::publicShow().
     */
    public function publicShow(string $slug): JsonResponse
    {
        $article = News::published()->with([...self::WITH, 'seoMeta'])->where('slug', $slug)->firstOrFail();
        $article->increment('views_count');

        return ApiResponse::success(new NewsResource($article));
    }

    private function syncMedia(News $article, NewsRequest $request): void
    {
        if ($request->has('featured_image_media_id')) {
            $mediaId = $request->input('featured_image_media_id');

            if ($mediaId === null) {
                $article->clearMediaCollection('featured_image');
            } else {
                /** @var Media $media */
                $media = Media::findOrFail($mediaId);
                $media->moveKeepingCustomFields($article, 'featured_image');
            }
        }

        foreach ($request->input('gallery_media_ids', []) as $mediaId) {
            /** @var Media $media */
            $media = Media::findOrFail($mediaId);
            $media->moveKeepingCustomFields($article, 'gallery');
        }
    }

    private function syncSeo(News $article, NewsRequest $request): void
    {
        if (! $request->has('seo')) {
            return;
        }

        // Goes through the seoMeta() relation (App\Support\Concerns\HasSeoMeta)
        // rather than a hand-built ['seoable_type' => News::class] array —
        // see SeoMetaController's docblock for why that would silently
        // mismatch the morph map alias the relation itself resolves to.
        $article->seoMeta()->updateOrCreate([], $request->input('seo'));
    }
}
