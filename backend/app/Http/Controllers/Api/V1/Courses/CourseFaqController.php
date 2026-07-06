<?php

namespace App\Http\Controllers\Api\V1\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\CourseFaqRequest;
use App\Http\Resources\FaqResource;
use App\Models\Course;
use App\Models\Faq;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.2 — /admin/courses/{course}/faqs sub-resource.
 * Course-scoped only (Database Design, Section 4.8's shared `faqs` table
 * also serves Events and a global Site FAQ — both separate, later
 * Development Roadmap stages, not wired up here).
 */
class CourseFaqController extends Controller
{
    public function index(Course $course): JsonResponse
    {
        Gate::authorize('viewAny', Faq::class);

        return ApiResponse::success(FaqResource::collection($course->faqs));
    }

    public function store(CourseFaqRequest $request, Course $course): JsonResponse
    {
        Gate::authorize('update', Faq::class);

        $faq = $course->faqs()->create($request->validated());

        return ApiResponse::success(new FaqResource($faq), 201);
    }

    public function update(CourseFaqRequest $request, Course $course, Faq $faq): JsonResponse
    {
        Gate::authorize('update', Faq::class);

        $faq->update($request->validated());

        return ApiResponse::success(new FaqResource($faq));
    }

    public function destroy(Course $course, Faq $faq): Response
    {
        Gate::authorize('update', Faq::class);

        $faq->delete();

        return response()->noContent();
    }
}
