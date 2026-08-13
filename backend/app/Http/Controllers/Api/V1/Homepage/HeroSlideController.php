<?php

namespace App\Http\Controllers\Api\V1\Homepage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Homepage\HeroSlideRequest;
use App\Http\Resources\HeroSlideResource;
use App\Models\HeroSlide;
use App\Models\Media;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.3. SRS Permission Matrix, "Hero Slider" row:
 * Super Admin/Administrator = Full; Marketing = Create/Edit; Content
 * Editor/Admissions = no access.
 */
class HeroSlideController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', HeroSlide::class);

        $slides = HeroSlide::query()
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(HeroSlideResource::collection($slides));
    }

    public function store(HeroSlideRequest $request): JsonResponse
    {
        Gate::authorize('create', HeroSlide::class);

        $slide = HeroSlide::create($request->safe()->except('media_id'));

        $this->attachMedia($slide, $request->input('media_id'));

        // Audit fix (Medium remediation) — see TestimonialController's docblock.
        PublicCache::forgetHomepage();

        return ApiResponse::success(new HeroSlideResource($slide->fresh()), 201);
    }

    public function show(HeroSlide $heroSlide): JsonResponse
    {
        Gate::authorize('viewAny', HeroSlide::class);

        return ApiResponse::success(new HeroSlideResource($heroSlide));
    }

    public function update(HeroSlideRequest $request, HeroSlide $heroSlide): JsonResponse
    {
        Gate::authorize('update', HeroSlide::class);

        $heroSlide->update($request->safe()->except('media_id'));

        if ($request->has('media_id')) {
            $this->attachMedia($heroSlide, $request->input('media_id'));
        }

        PublicCache::forgetHomepage();

        return ApiResponse::success(new HeroSlideResource($heroSlide->fresh()));
    }

    public function destroy(HeroSlide $heroSlide): Response
    {
        Gate::authorize('delete', HeroSlide::class);

        $heroSlide->delete();

        PublicCache::forgetHomepage();

        return response()->noContent();
    }

    /**
     * GET /api/v1/hero-slides — public, unauthenticated. Only active,
     * currently-scheduled slides, ordered.
     */
    public function publicIndex(): JsonResponse
    {
        $slides = HeroSlide::where('is_active', true)->currentlyScheduled()->orderBy('order')->get();

        return ApiResponse::success(HeroSlideResource::collection($slides));
    }

    /**
     * Reassigns an existing Media Library item onto this slide's
     * 'slide_image' collection via Media::moveKeepingCustomFields() —
     * the mechanism the Media Library milestone built for exactly this
     * "a real content model claims an asset" scenario. A null media_id
     * clears the slide's current image without touching the Media
     * Library row itself.
     */
    private function attachMedia(HeroSlide $slide, ?int $mediaId): void
    {
        if ($mediaId === null) {
            $slide->clearMediaCollection('slide_image');

            return;
        }

        /** @var Media $media */
        $media = Media::findOrFail($mediaId);
        $media->moveKeepingCustomFields($slide, 'slide_image');
    }
}
