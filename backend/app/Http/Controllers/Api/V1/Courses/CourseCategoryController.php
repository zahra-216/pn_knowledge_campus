<?php

namespace App\Http\Controllers\Api\V1\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\CourseCategoryReorderRequest;
use App\Http\Requests\Courses\CourseCategoryRequest;
use App\Http\Resources\CourseCategoryResource;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Media;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Course Category CRUD — promoted beyond the shared CourseLookupController
 * (see that class's docblock) since Category now carries its own media
 * (icon/image), a parent/child tree, and SEO, none of which Level/Mode
 * have. Still gated by courses.* — this remains a Course Management
 * sub-part, not its own SRS Permission Matrix row.
 */
class CourseCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Course::class);

        $categories = CourseCategory::query()
            ->withCount('courses')
            ->with('parent')
            ->orderBy('parent_id')
            ->orderBy('order')
            ->get();

        return ApiResponse::success(CourseCategoryResource::collection($categories));
    }

    public function store(CourseCategoryRequest $request): JsonResponse
    {
        Gate::authorize('create', Course::class);

        $category = DB::transaction(function () use ($request) {
            $category = CourseCategory::create($request->safe()->except(['icon_media_id', 'image_media_id']));
            $this->syncMedia($category, $request);

            return $category;
        });

        return ApiResponse::success(new CourseCategoryResource($category), 201);
    }

    public function show(CourseCategory $courseCategory): JsonResponse
    {
        Gate::authorize('viewAny', Course::class);

        $courseCategory->loadCount('courses')->load(['parent', 'children']);

        return ApiResponse::success(new CourseCategoryResource($courseCategory));
    }

    public function update(CourseCategoryRequest $request, CourseCategory $courseCategory): JsonResponse
    {
        Gate::authorize('update', Course::class);

        DB::transaction(function () use ($request, $courseCategory) {
            $courseCategory->update($request->safe()->except(['icon_media_id', 'image_media_id']));
            $this->syncMedia($courseCategory, $request);
        });

        return ApiResponse::success(new CourseCategoryResource($courseCategory->fresh()));
    }

    public function destroy(CourseCategory $courseCategory): Response
    {
        Gate::authorize('delete', Course::class);

        $courseCategory->delete();

        return response()->noContent();
    }

    /**
     * PATCH /admin/course-categories/reorder — bulk order/nesting
     * update, applied in one transaction so a partial failure never
     * leaves the tree half-reordered. Mirrors MenuItemController::reorder.
     */
    public function reorder(CourseCategoryReorderRequest $request): JsonResponse
    {
        Gate::authorize('update', Course::class);

        DB::transaction(function () use ($request) {
            foreach ($request->input('items') as $entry) {
                CourseCategory::whereKey($entry['id'])->update([
                    'parent_id' => $entry['parent_id'] ?? null,
                    'order' => $entry['order'],
                ]);
            }
        });

        $categories = CourseCategory::query()->withCount('courses')->with('parent')->orderBy('parent_id')->orderBy('order')->get();

        return ApiResponse::success(CourseCategoryResource::collection($categories));
    }

    /**
     * GET /api/v1/course-categories — public, unauthenticated. Returns
     * the full tree (top-level categories with nested children) for a
     * course filter/browse UI.
     */
    public function publicIndex(): JsonResponse
    {
        $categories = CourseCategory::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        return ApiResponse::success(CourseCategoryResource::collection($categories));
    }

    private function syncMedia(CourseCategory $category, CourseCategoryRequest $request): void
    {
        foreach (['icon' => 'icon_media_id', 'image' => 'image_media_id'] as $collection => $field) {
            if (! $request->has($field)) {
                continue;
            }

            $mediaId = $request->input($field);

            if ($mediaId === null) {
                $category->clearMediaCollection($collection);

                continue;
            }

            /** @var Media $media */
            $media = Media::findOrFail($mediaId);
            $media->moveKeepingCustomFields($category, $collection);
        }
    }
}
