<?php

namespace App\Http\Controllers\Api\V1\Faculties;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faculties\FacultyGalleryRequest;
use App\Http\Requests\Faculties\FacultyRequest;
use App\Http\Resources\FacultyResource;
use App\Models\Faculty;
use App\Models\Media;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.2. SRS Permission Matrix, "Faculty Management"
 * row: Super Admin/Administrator = Full; Content Editor = Create/Edit;
 * Marketing/Admissions = View.
 */
class FacultyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Faculty::class);

        $faculties = Faculty::query()
            // 'media' (audit fix, Medium remediation) — FacultyResource
            // calls getFirstMedia() three times plus getMedia('gallery')
            // per row; this was the worst offender of the audit's N+1
            // finding since this index() had no eager-loading at all.
            ->with('media')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(FacultyResource::collection($faculties));
    }

    public function store(FacultyRequest $request): JsonResponse
    {
        Gate::authorize('create', Faculty::class);

        $faculty = Faculty::create($request->safe()->except(['icon_media_id', 'banner_media_id', 'dean_photo_media_id']));

        $this->syncSingleMedia($faculty, $request);

        // Audit fix (Medium remediation) — see TestimonialController's docblock.
        PublicCache::forgetHomepage();

        return ApiResponse::success(new FacultyResource($faculty->fresh()->load(['departments', 'courses'])), 201);
    }

    public function show(Faculty $faculty): JsonResponse
    {
        Gate::authorize('viewAny', Faculty::class);

        return ApiResponse::success(new FacultyResource($faculty->load(['departments', 'courses'])));
    }

    public function update(FacultyRequest $request, Faculty $faculty): JsonResponse
    {
        Gate::authorize('update', Faculty::class);

        $faculty->update($request->safe()->except(['icon_media_id', 'banner_media_id', 'dean_photo_media_id']));

        $this->syncSingleMedia($faculty, $request);

        PublicCache::forgetHomepage();

        return ApiResponse::success(new FacultyResource($faculty->fresh()->load(['departments', 'courses'])));
    }

    /**
     * Soft delete (Database Design, Section 2.1) — the row and its
     * uniqueness-enforcing slug are retained, recoverable, not purged.
     * Blocked with a 409 while the faculty still has departments
     * (API Design, Section 6.2's documented conflict response) — a soft
     * delete never touches the FK, so the DB-level ON DELETE RESTRICT on
     * departments.faculty_id wouldn't catch this on its own.
     */
    public function destroy(Faculty $faculty): Response|JsonResponse
    {
        Gate::authorize('delete', Faculty::class);

        $departmentCount = $faculty->departments()->count();

        if ($departmentCount > 0) {
            return ApiResponse::error(
                'This faculty cannot be deleted while it still has departments assigned to it.',
                409,
                ['conflict' => ['type' => 'has_dependent_records', 'related_resource' => 'departments', 'count' => $departmentCount]]
            );
        }

        $faculty->delete();

        PublicCache::forgetHomepage();

        return response()->noContent();
    }

    /**
     * POST /api/v1/admin/faculties/{faculty}/gallery — attach existing
     * Media Library items to the gallery collection (mirrors
     * gallery-albums' attach endpoint).
     */
    public function attachGallery(FacultyGalleryRequest $request, Faculty $faculty): JsonResponse
    {
        Gate::authorize('update', Faculty::class);

        foreach ($request->input('media_ids') as $mediaId) {
            /** @var Media $media */
            $media = Media::findOrFail($mediaId);
            $media->moveKeepingCustomFields($faculty, 'gallery');
        }

        return ApiResponse::success(new FacultyResource($faculty->fresh()));
    }

    /**
     * DELETE /api/v1/admin/faculties/{faculty}/gallery/{mediaId} —
     * remove one item from the gallery (mirrors gallery-albums' detach
     * endpoint). Scoped to this faculty's own gallery collection so one
     * faculty can't delete another's media via a guessed id.
     */
    public function detachGallery(Faculty $faculty, int $mediaId): Response
    {
        Gate::authorize('update', Faculty::class);

        $faculty->getMedia('gallery')->where('id', $mediaId)->each->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/faculties — public, unauthenticated. Published only,
     * ordered by 'order' (API Design, Section 7.1).
     */
    public function publicIndex(): JsonResponse
    {
        $faculties = Faculty::published()->with('media')->orderBy('order')->get();

        return ApiResponse::success(FacultyResource::collection($faculties));
    }

    /**
     * GET /api/v1/faculties/{slug} — public, unauthenticated. "Faculty
     * detail, including its departments" per the API Design — only
     * published departments/courses are shown publicly.
     */
    public function publicShow(string $slug): JsonResponse
    {
        $faculty = Faculty::published()->where('slug', $slug)->firstOrFail();
        $faculty->load([
            'departments' => fn ($query) => $query->published(),
            'courses' => fn ($query) => $query->published(),
            'seoMeta',
        ]);

        return ApiResponse::success(new FacultyResource($faculty));
    }

    private function syncSingleMedia(Faculty $faculty, FacultyRequest $request): void
    {
        foreach (['icon' => 'icon_media_id', 'banner' => 'banner_media_id', 'dean_photo' => 'dean_photo_media_id'] as $collection => $field) {
            if (! $request->has($field)) {
                continue;
            }

            $mediaId = $request->input($field);

            if ($mediaId === null) {
                $faculty->clearMediaCollection($collection);

                continue;
            }

            /** @var Media $media */
            $media = Media::findOrFail($mediaId);
            $media->moveKeepingCustomFields($faculty, $collection);
        }
    }
}
