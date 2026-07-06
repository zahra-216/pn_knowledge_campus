<?php

namespace App\Http\Controllers\Api\V1\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\CourseCurriculumItemRequest;
use App\Http\Requests\Courses\CourseCurriculumReorderRequest;
use App\Http\Resources\CourseCurriculumItemResource;
use App\Models\Course;
use App\Models\CourseCurriculumItem;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.2 — /admin/courses/{course}/curriculum
 * sub-resource. Mirrors MenuItemController's reorder/scoping pattern.
 */
class CourseCurriculumController extends Controller
{
    public function index(Course $course): JsonResponse
    {
        Gate::authorize('viewAny', CourseCurriculumItem::class);

        $course->load('topLevelCurriculumItems.children');

        return ApiResponse::success(CourseCurriculumItemResource::collection($course->topLevelCurriculumItems));
    }

    public function store(CourseCurriculumItemRequest $request, Course $course): JsonResponse
    {
        Gate::authorize('update', CourseCurriculumItem::class);

        $item = $course->curriculumItems()->create($request->validated());

        return ApiResponse::success(new CourseCurriculumItemResource($item), 201);
    }

    public function update(CourseCurriculumItemRequest $request, Course $course, CourseCurriculumItem $curriculumItem): JsonResponse
    {
        Gate::authorize('update', CourseCurriculumItem::class);

        $curriculumItem->update($request->validated());

        return ApiResponse::success(new CourseCurriculumItemResource($curriculumItem));
    }

    public function destroy(Course $course, CourseCurriculumItem $curriculumItem): Response
    {
        Gate::authorize('update', CourseCurriculumItem::class);

        $curriculumItem->delete();

        return response()->noContent();
    }

    public function reorder(CourseCurriculumReorderRequest $request, Course $course): JsonResponse
    {
        Gate::authorize('update', CourseCurriculumItem::class);

        DB::transaction(function () use ($request, $course) {
            foreach ($request->input('items') as $entry) {
                $course->curriculumItems()->whereKey($entry['id'])->update([
                    'parent_id' => $entry['parent_id'] ?? null,
                    'order' => $entry['order'],
                ]);
            }
        });

        $course->load('topLevelCurriculumItems.children');

        return ApiResponse::success(CourseCurriculumItemResource::collection($course->topLevelCurriculumItems));
    }
}
