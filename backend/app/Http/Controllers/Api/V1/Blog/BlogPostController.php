<?php

namespace App\Http\Controllers\Api\V1\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\BlogPostGalleryRequest;
use App\Http\Requests\Blog\BlogPostRequest;
use App\Http\Resources\BlogPostResource;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Tag;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * API Design, Section 8.4. SRS Permission Matrix, "Blog" row: Super
 * Admin/Administrator = Full (incl. delete/publish); Content Editor =
 * Create/Edit; Marketing = Create/Edit; Admissions = no access.
 */
class BlogPostController extends Controller
{
    private const WITH = ['category', 'author', 'tags'];

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', BlogPost::class);

        $posts = BlogPost::query()
            ->with(self::WITH)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->filled('author'), fn ($q) => $q->where('author_id', $request->integer('author')))
            ->when($request->filled('tag'), fn ($q) => $q->whereHas('tags', fn ($tq) => $tq->where('tags.id', $request->integer('tag'))))
            ->when($request->boolean('featured'), fn ($q) => $q->where('is_featured', true))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($sq) => $sq
                ->where('title', 'like', '%'.$request->string('search').'%')
                ->orWhere('excerpt', 'like', '%'.$request->string('search').'%')))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(BlogPostResource::collection($posts));
    }

    public function store(BlogPostRequest $request): JsonResponse
    {
        Gate::authorize('create', BlogPost::class);

        $post = DB::transaction(function () use ($request) {
            $data = $request->safe()->except(['featured_image_media_id', 'gallery_media_ids', 'tags', 'seo']);
            $data['author_id'] = $data['author_id'] ?? Auth::id();

            $post = BlogPost::create($data);

            $this->syncMedia($post, $request);
            $this->syncTags($post, $request);
            $this->syncSeo($post, $request);

            return $post;
        });

        return ApiResponse::success(new BlogPostResource($post->fresh()->load(self::WITH)), 201);
    }

    public function show(BlogPost $blogPost): JsonResponse
    {
        Gate::authorize('viewAny', BlogPost::class);

        return ApiResponse::success(new BlogPostResource($blogPost->load(self::WITH)));
    }

    public function update(BlogPostRequest $request, BlogPost $blogPost): JsonResponse
    {
        Gate::authorize('update', BlogPost::class);

        DB::transaction(function () use ($request, $blogPost) {
            $blogPost->update($request->safe()->except(['featured_image_media_id', 'gallery_media_ids', 'tags', 'seo']));

            $this->syncMedia($blogPost, $request);
            $this->syncTags($blogPost, $request);
            $this->syncSeo($blogPost, $request);
        });

        return ApiResponse::success(new BlogPostResource($blogPost->fresh()->load(self::WITH)));
    }

    /**
     * Soft delete (Database Design, Section 2.1).
     */
    public function destroy(BlogPost $blogPost): Response
    {
        Gate::authorize('delete', BlogPost::class);

        $blogPost->delete();

        return response()->noContent();
    }

    /**
     * PATCH /api/v1/admin/blog/{id}/publish — "Publish a draft/scheduled
     * post." Sets published_at on first publish only, so republishing an
     * already-published post doesn't wipe its original publish date.
     */
    public function publish(BlogPost $blogPost): JsonResponse
    {
        Gate::authorize('publish', BlogPost::class);

        $blogPost->update([
            'status' => 'published',
            'published_at' => $blogPost->published_at ?? Carbon::now(),
        ]);

        return ApiResponse::success(new BlogPostResource($blogPost->fresh()->load(self::WITH)));
    }

    /**
     * POST /api/v1/admin/blog/{id}/media — attach existing Media Library
     * items to this post's gallery collection.
     */
    public function attachMedia(BlogPostGalleryRequest $request, BlogPost $blogPost): JsonResponse
    {
        Gate::authorize('update', BlogPost::class);

        foreach ($request->input('media_ids') as $mediaId) {
            /** @var Media $media */
            $media = Media::findOrFail($mediaId);
            $media->moveKeepingCustomFields($blogPost, 'gallery');
        }

        return ApiResponse::success(new BlogPostResource($blogPost->fresh()->load(self::WITH)));
    }

    /**
     * DELETE /api/v1/admin/blog/{id}/media/{mediaId} — remove one item
     * from the post's gallery (not in the API Design's own endpoint
     * list, but mirrors FacultyController::detachGallery's same
     * reasoning — a gallery manager needs a way to remove items).
     */
    public function detachMedia(BlogPost $blogPost, int $mediaId): Response
    {
        Gate::authorize('update', BlogPost::class);

        $blogPost->getMedia('gallery')->where('id', $mediaId)->each->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/blog — public, unauthenticated. "List published blog
     * posts. filter[category], filter[tag], search supported" (API
     * Design, Section 7.3) — both filters resolve by slug, matching the
     * public course filters' own convention.
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $filters = $request->input('filter', []);

        $posts = BlogPost::published()
            ->with(self::WITH)
            ->when(! empty($filters['category']), function ($query) use ($filters) {
                $category = BlogCategory::where('slug', $filters['category'])->first();
                $query->where('category_id', $category?->id ?? 0);
            })
            ->when(! empty($filters['tag']), function ($query) use ($filters) {
                $tag = Tag::where('slug', $filters['tag'])->first();
                $query->whereHas('tags', fn ($tq) => $tq->where('tags.id', $tag?->id ?? 0));
            })
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($sq) => $sq
                ->where('title', 'like', '%'.$request->string('search').'%')
                ->orWhere('excerpt', 'like', '%'.$request->string('search').'%')))
            ->orderByDesc('published_at')
            ->paginate($request->integer('per_page', 12));

        return ApiResponse::success(BlogPostResource::collection($posts));
    }

    /**
     * GET /api/v1/blog/{slug} — public, unauthenticated. Post detail
     * with related posts; increments the denormalized views_count
     * (Database Design, Section 4.6) on each view.
     */
    public function publicShow(string $slug): JsonResponse
    {
        $post = BlogPost::published()->with([...self::WITH, 'seoMeta'])->where('slug', $slug)->firstOrFail();
        $post->increment('views_count');
        $post->setRelation('relatedPosts', $post->findRelated());

        return ApiResponse::success(new BlogPostResource($post));
    }

    private function syncMedia(BlogPost $post, BlogPostRequest $request): void
    {
        if ($request->has('featured_image_media_id')) {
            $mediaId = $request->input('featured_image_media_id');

            if ($mediaId === null) {
                $post->clearMediaCollection('featured_image');
            } else {
                /** @var Media $media */
                $media = Media::findOrFail($mediaId);
                $media->moveKeepingCustomFields($post, 'featured_image');
            }
        }

        foreach ($request->input('gallery_media_ids', []) as $mediaId) {
            /** @var Media $media */
            $media = Media::findOrFail($mediaId);
            $media->moveKeepingCustomFields($post, 'gallery');
        }
    }

    /**
     * Resolves each submitted tag name to a Tag row (creating it if it
     * doesn't exist yet) and replaces the post's tag set entirely — a
     * plain sync(), not additive, since the tag editor always resends
     * the post's full current tag list.
     */
    private function syncTags(BlogPost $post, BlogPostRequest $request): void
    {
        if (! $request->has('tags')) {
            return;
        }

        $tagIds = collect($request->input('tags', []))
            ->filter()
            ->map(fn (string $name) => Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id);

        $post->tags()->sync($tagIds);
    }

    private function syncSeo(BlogPost $post, BlogPostRequest $request): void
    {
        if (! $request->has('seo')) {
            return;
        }

        // Goes through the seoMeta() relation (App\Support\Concerns\HasSeoMeta)
        // rather than a hand-built ['seoable_type' => BlogPost::class] array —
        // see SeoMetaController's docblock for why that would silently
        // mismatch the morph map alias the relation itself resolves to.
        $post->seoMeta()->updateOrCreate([], $request->input('seo'));
    }
}
