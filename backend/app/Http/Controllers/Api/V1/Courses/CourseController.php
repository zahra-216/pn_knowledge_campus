<?php

namespace App\Http\Controllers\Api\V1\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\CourseMediaRequest;
use App\Http\Requests\Courses\CourseRequest;
use App\Http\Resources\CourseListResource;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\Media;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.2. SRS Permission Matrix, "Course Management"
 * row: Super Admin/Administrator = Full; Content Editor = Create/Edit;
 * Marketing = View; Admissions = Create/Edit.
 */
class CourseController extends Controller
{
    // 'media' (audit fix, Medium remediation) — CourseResource calls
    // getFirstMedia()/getMedia() for featured_image/gallery/downloads;
    // eager-loading the whole 'media' relation here means Spatie's own
    // getMedia() reads from it instead of firing one query per row
    // (see InteractsWithMedia::loadMedia(), which prefers $this->media
    // when the relation is already loaded).
    private const WITH = ['faculty', 'department', 'level', 'mode', 'category', 'media'];

    private const WITH_DETAIL = [...self::WITH, 'topLevelCurriculumItems.children', 'faqs'];

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Course::class);

        $courses = Course::query()
            ->with(self::WITH)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('faculty'), fn ($q) => $q->where('faculty_id', $request->integer('faculty')))
            ->when($request->filled('department'), fn ($q) => $q->where('department_id', $request->integer('department')))
            ->when($request->filled('level'), fn ($q) => $q->where('level_id', $request->integer('level')))
            ->when($request->filled('mode'), fn ($q) => $q->where('mode_id', $request->integer('mode')))
            ->when($request->boolean('featured'), fn ($q) => $q->where('is_featured', true))
            ->when($request->filled('search'), fn ($q) => $q->where('course_name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(CourseResource::collection($courses));
    }

    public function store(CourseRequest $request): JsonResponse
    {
        Gate::authorize('create', Course::class);

        $course = DB::transaction(function () use ($request) {
            $course = Course::create($request->safe()->except([
                'featured_image_media_id', 'gallery_media_ids', 'downloads_media_ids', 'curriculum', 'seo',
            ]));

            $this->syncMedia($course, $request);
            $this->syncCurriculum($course, $request);
            $this->syncSeo($course, $request);

            return $course;
        });

        // Audit fix (Medium remediation) — see TestimonialController's docblock.
        PublicCache::forgetHomepage();

        return ApiResponse::success(new CourseResource($course->fresh()->load(self::WITH_DETAIL)), 201);
    }

    public function show(Course $course): JsonResponse
    {
        Gate::authorize('viewAny', Course::class);

        return ApiResponse::success(new CourseResource($course->load(self::WITH_DETAIL)));
    }

    public function update(CourseRequest $request, Course $course): JsonResponse
    {
        Gate::authorize('update', Course::class);

        DB::transaction(function () use ($request, $course) {
            $course->update($request->safe()->except([
                'featured_image_media_id', 'gallery_media_ids', 'downloads_media_ids', 'curriculum', 'seo',
            ]));

            $this->syncMedia($course, $request);
            $this->syncCurriculum($course, $request);
            $this->syncSeo($course, $request);
        });

        PublicCache::forgetHomepage();

        return ApiResponse::success(new CourseResource($course->fresh()->load(self::WITH_DETAIL)));
    }

    /**
     * Soft delete (Database Design, Section 2.1).
     */
    public function destroy(Course $course): Response
    {
        Gate::authorize('delete', Course::class);

        $course->delete();

        PublicCache::forgetHomepage();

        return response()->noContent();
    }

    /**
     * PATCH /api/v1/admin/courses/{id}/publish — "Publish a
     * draft/scheduled course immediately."
     */
    public function publish(Course $course): JsonResponse
    {
        Gate::authorize('publish', Course::class);

        $course->update([
            'status' => 'published',
            'published_at' => $course->published_at ?? Carbon::now(),
        ]);

        PublicCache::forgetHomepage();

        return ApiResponse::success(new CourseResource($course->fresh()->load(self::WITH_DETAIL)));
    }

    /**
     * POST /api/v1/admin/courses/{id}/media — attach existing Media
     * Library items to this course's gallery or downloads collection.
     */
    public function attachMedia(CourseMediaRequest $request, Course $course): JsonResponse
    {
        Gate::authorize('update', Course::class);

        foreach ($request->input('media_ids') as $mediaId) {
            /** @var Media $media */
            $media = Media::findOrFail($mediaId);
            $media->moveKeepingCustomFields($course, $request->input('collection'));
        }

        return ApiResponse::success(new CourseResource($course->fresh()->load(self::WITH_DETAIL)));
    }

    /**
     * DELETE /api/v1/admin/courses/{id}/media/{mediaId} — remove one
     * item from the course's gallery or downloads collection (not in
     * the API Design's own endpoint list, but a gallery/downloads
     * manager needs a way to remove items — mirrors
     * FacultyController::detachGallery's same reasoning). Scoped to this
     * course's own media so one course can't remove another's item via
     * a guessed id.
     */
    public function detachMedia(Request $request, Course $course, int $mediaId): Response
    {
        Gate::authorize('update', Course::class);

        $collection = $request->query('collection', 'gallery');
        $course->getMedia($collection)->where('id', $mediaId)->each->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/courses — public, unauthenticated. Published only;
     * filter[faculty]/[department]/[level]/[mode]/[category] by slug/id,
     * featured=1, search (API Design, Section 7.1).
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $filters = $request->input('filter', []);

        $courses = Course::published()
            ->with(self::WITH)
            ->when(! empty($filters['faculty']), fn ($q) => $q->whereHas('faculty', fn ($fq) => $fq->where('slug', $filters['faculty'])))
            ->when(! empty($filters['department']), fn ($q) => $q->whereHas('department', fn ($dq) => $dq->where('slug', $filters['department'])))
            ->when(! empty($filters['level']), fn ($q) => $q->whereHas('level', fn ($lq) => $lq->where('slug', $filters['level'])))
            ->when(! empty($filters['mode']), fn ($q) => $q->whereHas('mode', fn ($mq) => $mq->where('slug', $filters['mode'])))
            ->when(! empty($filters['category']), fn ($q) => $q->whereHas('category', fn ($cq) => $cq->where('slug', $filters['category'])))
            ->when($request->boolean('featured') || ($filters['featured'] ?? null) == 1, fn ($q) => $q->where('is_featured', true))
            ->when($request->filled('search'), fn ($q) => $q->where('course_name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 12));

        return ApiResponse::success(CourseListResource::collection($courses));
    }

    /**
     * GET /api/v1/courses/{slug} — public, unauthenticated. "Full course
     * detail: overview, curriculum, entry requirements, career outcomes,
     * gallery, downloads, FAQs, SEO."
     */
    public function publicShow(string $slug): JsonResponse
    {
        $course = Course::published()->with([...self::WITH_DETAIL, 'seoMeta'])->where('slug', $slug)->firstOrFail();

        return ApiResponse::success(new CourseResource($course));
    }

    private function syncMedia(Course $course, CourseRequest $request): void
    {
        if ($request->has('featured_image_media_id')) {
            $mediaId = $request->input('featured_image_media_id');

            if ($mediaId === null) {
                $course->clearMediaCollection('featured_image');
            } else {
                /** @var Media $media */
                $media = Media::findOrFail($mediaId);
                $media->moveKeepingCustomFields($course, 'featured_image');
            }
        }

        foreach (['gallery_media_ids' => 'gallery', 'downloads_media_ids' => 'downloads'] as $field => $collection) {
            foreach ($request->input($field, []) as $mediaId) {
                /** @var Media $media */
                $media = Media::findOrFail($mediaId);
                $media->moveKeepingCustomFields($course, $collection);
            }
        }
    }

    /**
     * Replaces the course's entire curriculum tree when `curriculum` is
     * present in the request — the create-time nested payload the API
     * Design documents. Granular later edits go through the dedicated
     * /curriculum sub-resource endpoints instead of resending this array.
     */
    private function syncCurriculum(Course $course, CourseRequest $request): void
    {
        if (! $request->has('curriculum')) {
            return;
        }

        $course->curriculumItems()->delete();

        foreach ($request->input('curriculum', []) as $index => $item) {
            $module = $course->curriculumItems()->create([
                'title' => $item['title'],
                'description' => $item['description'] ?? null,
                'duration' => $item['duration'] ?? null,
                'order' => $item['order'] ?? $index,
            ]);

            foreach ($item['children'] ?? [] as $childIndex => $child) {
                $course->curriculumItems()->create([
                    'parent_id' => $module->id,
                    'title' => $child['title'],
                    'description' => $child['description'] ?? null,
                    'duration' => $child['duration'] ?? null,
                    'order' => $child['order'] ?? $childIndex,
                ]);
            }
        }
    }

    private function syncSeo(Course $course, CourseRequest $request): void
    {
        if (! $request->has('seo')) {
            return;
        }

        // Goes through the seoMeta() relation (App\Support\Concerns\HasSeoMeta)
        // rather than a hand-built ['seoable_type' => Course::class] array —
        // see SeoMetaController's docblock for why that would silently
        // mismatch the morph map alias the relation itself resolves to.
        $course->seoMeta()->updateOrCreate([], $request->input('seo'));
    }
}
