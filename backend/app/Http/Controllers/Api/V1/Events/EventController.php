<?php

namespace App\Http\Controllers\Api\V1\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\EventGalleryRequest;
use App\Http\Requests\Events\EventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Media;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.4. SRS Permission Matrix, "Events" row: Super
 * Admin/Administrator = Full; Content Editor = Create/Edit; Marketing =
 * Create/Edit; Admissions = no access. No publish() action — see
 * EventPolicy's docblock.
 */
class EventController extends Controller
{
    // 'media' (audit fix, Medium remediation) — see CourseController's
    // identical WITH-constant comment for why this avoids an N+1 on
    // EventResource's featured_image/gallery lookups.
    private const WITH = ['speakers', 'media'];

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Event::class);

        $events = Event::query()
            ->with(self::WITH)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->boolean('upcoming'), fn ($q) => $q->upcoming())
            ->when($request->boolean('past'), fn ($q) => $q->past())
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->orderBy('starts_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(EventResource::collection($events));
    }

    public function store(EventRequest $request): JsonResponse
    {
        Gate::authorize('create', Event::class);

        $event = DB::transaction(function () use ($request) {
            $event = Event::create($request->safe()->except(['featured_image_media_id', 'gallery_media_ids', 'seo']));

            $this->syncMedia($event, $request);
            $this->syncSeo($event, $request);

            return $event;
        });

        // Audit fix (Medium remediation) — see TestimonialController's docblock.
        PublicCache::forgetHomepage();

        return ApiResponse::success(new EventResource($event->fresh()->load(self::WITH)), 201);
    }

    public function show(Event $event): JsonResponse
    {
        Gate::authorize('viewAny', Event::class);

        return ApiResponse::success(new EventResource($event->load(self::WITH)));
    }

    public function update(EventRequest $request, Event $event): JsonResponse
    {
        Gate::authorize('update', Event::class);

        DB::transaction(function () use ($request, $event) {
            $event->update($request->safe()->except(['featured_image_media_id', 'gallery_media_ids', 'seo']));

            $this->syncMedia($event, $request);
            $this->syncSeo($event, $request);
        });

        PublicCache::forgetHomepage();

        return ApiResponse::success(new EventResource($event->fresh()->load(self::WITH)));
    }

    /**
     * Soft delete (Database Design, Section 2.1).
     */
    public function destroy(Event $event): Response
    {
        Gate::authorize('delete', Event::class);

        $event->delete();

        PublicCache::forgetHomepage();

        return response()->noContent();
    }

    /**
     * POST /api/v1/admin/events/{id}/media — attach existing Media
     * Library items to this event's gallery collection.
     */
    public function attachMedia(EventGalleryRequest $request, Event $event): JsonResponse
    {
        Gate::authorize('update', Event::class);

        foreach ($request->input('media_ids') as $mediaId) {
            /** @var Media $media */
            $media = Media::findOrFail($mediaId);
            $media->moveKeepingCustomFields($event, 'gallery');
        }

        return ApiResponse::success(new EventResource($event->fresh()->load(self::WITH)));
    }

    /**
     * DELETE /api/v1/admin/events/{id}/media/{mediaId} — remove one item
     * from the event's gallery (not in the API Design's own endpoint
     * list, but mirrors FacultyController::detachGallery's same
     * reasoning).
     */
    public function detachMedia(Event $event, int $mediaId): Response
    {
        Gate::authorize('update', Event::class);

        $event->getMedia('gallery')->where('id', $mediaId)->each->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/events — public, unauthenticated. "List published
     * events. filter[upcoming]=1 (default) or filter[past]=1, sorted by
     * starts_at" (API Design, Section 7.3).
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $filters = $request->input('filter', []);
        $wantsPast = ($filters['past'] ?? null) == 1;

        $events = Event::published()
            ->with(self::WITH)
            ->when($wantsPast, fn ($q) => $q->past(), fn ($q) => $q->upcoming())
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->orderBy('starts_at', $wantsPast ? 'desc' : 'asc')
            ->paginate($request->integer('per_page', 12));

        return ApiResponse::success(EventResource::collection($events));
    }

    /**
     * GET /api/v1/events/{slug} — public, unauthenticated. Event detail.
     */
    public function publicShow(string $slug): JsonResponse
    {
        $event = Event::published()->with([...self::WITH, 'seoMeta'])->where('slug', $slug)->firstOrFail();

        return ApiResponse::success(new EventResource($event));
    }

    private function syncMedia(Event $event, EventRequest $request): void
    {
        if ($request->has('featured_image_media_id')) {
            $mediaId = $request->input('featured_image_media_id');

            if ($mediaId === null) {
                $event->clearMediaCollection('featured_image');
            } else {
                /** @var Media $media */
                $media = Media::findOrFail($mediaId);
                $media->moveKeepingCustomFields($event, 'featured_image');
            }
        }

        foreach ($request->input('gallery_media_ids', []) as $mediaId) {
            /** @var Media $media */
            $media = Media::findOrFail($mediaId);
            $media->moveKeepingCustomFields($event, 'gallery');
        }
    }

    private function syncSeo(Event $event, EventRequest $request): void
    {
        if (! $request->has('seo')) {
            return;
        }

        // Goes through the seoMeta() relation (App\Support\Concerns\HasSeoMeta)
        // rather than a hand-built ['seoable_type' => Event::class] array —
        // see SeoMetaController's docblock for why that would silently
        // mismatch the morph map alias the relation itself resolves to.
        $event->seoMeta()->updateOrCreate([], $request->input('seo'));
    }
}
