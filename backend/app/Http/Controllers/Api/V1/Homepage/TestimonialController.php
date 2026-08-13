<?php

namespace App\Http\Controllers\Api\V1\Homepage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Homepage\TestimonialRequest;
use App\Http\Resources\TestimonialResource;
use App\Models\Media;
use App\Models\Testimonial;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.4. SRS Permission Matrix, "Testimonials" row:
 * Super Admin/Administrator = Full; Content Editor/Marketing =
 * Create/Edit; Admissions = no access.
 */
class TestimonialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Testimonial::class);

        $testimonials = Testimonial::query()
            ->with('course')
            ->when($request->filled('course'), fn ($q) => $q->where('course_id', $request->integer('course')))
            ->when($request->boolean('featured'), fn ($q) => $q->where('is_featured', true))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(TestimonialResource::collection($testimonials));
    }

    public function store(TestimonialRequest $request): JsonResponse
    {
        Gate::authorize('create', Testimonial::class);

        $testimonial = Testimonial::create($request->safe()->except('media_id'));

        $this->attachMedia($testimonial, $request->input('media_id'));

        // Audit fix (Medium remediation) — testimonials feed the homepage's
        // featured carousel, but writes here never invalidated its cache
        // entry, so a newly-featured testimonial didn't appear live for up
        // to 5 minutes (the blanket TTL) with no way to force it.
        PublicCache::forgetHomepage();

        return ApiResponse::success(new TestimonialResource($testimonial->fresh()->load('course')), 201);
    }

    public function show(Testimonial $testimonial): JsonResponse
    {
        Gate::authorize('viewAny', Testimonial::class);

        return ApiResponse::success(new TestimonialResource($testimonial->load('course')));
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial): JsonResponse
    {
        Gate::authorize('update', Testimonial::class);

        $testimonial->update($request->safe()->except('media_id'));

        if ($request->has('media_id')) {
            $this->attachMedia($testimonial, $request->input('media_id'));
        }

        PublicCache::forgetHomepage();

        return ApiResponse::success(new TestimonialResource($testimonial->fresh()));
    }

    public function destroy(Testimonial $testimonial): Response
    {
        Gate::authorize('delete', Testimonial::class);

        $testimonial->delete();

        PublicCache::forgetHomepage();

        return response()->noContent();
    }

    /**
     * GET /api/v1/testimonials — public, unauthenticated. filter[course],
     * filter[featured]=1 supported per API Design, Section 7.4.
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->when($request->filled('course'), fn ($q) => $q->where('course_id', $request->integer('course')))
            ->when($request->boolean('featured'), fn ($q) => $q->where('is_featured', true))
            ->orderBy('order')
            ->get();

        return ApiResponse::success(TestimonialResource::collection($testimonials));
    }

    private function attachMedia(Testimonial $testimonial, ?int $mediaId): void
    {
        if ($mediaId === null) {
            $testimonial->clearMediaCollection('photo');

            return;
        }

        /** @var Media $media */
        $media = Media::findOrFail($mediaId);
        $media->moveKeepingCustomFields($testimonial, 'photo');
    }
}
