<?php

namespace App\Http\Controllers\Api\V1\Departments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Departments\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Media;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.2. SRS Permission Matrix, "Department
 * Management" row: Super Admin/Administrator = Full; Content Editor =
 * Create/Edit; Marketing/Admissions = View — identical split to Faculty
 * Management.
 */
class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Department::class);

        $departments = Department::query()
            ->with('faculty')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('faculty'), fn ($q) => $q->where('faculty_id', $request->integer('faculty')))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(DepartmentResource::collection($departments));
    }

    public function store(DepartmentRequest $request): JsonResponse
    {
        Gate::authorize('create', Department::class);

        $department = Department::create($request->safe()->except('banner_media_id'));

        $this->syncBanner($department, $request);

        return ApiResponse::success(new DepartmentResource($department->fresh()->load(['faculty', 'courses'])), 201);
    }

    public function show(Department $department): JsonResponse
    {
        Gate::authorize('viewAny', Department::class);

        return ApiResponse::success(new DepartmentResource($department->load(['faculty', 'courses'])));
    }

    public function update(DepartmentRequest $request, Department $department): JsonResponse
    {
        Gate::authorize('update', Department::class);

        $department->update($request->safe()->except('banner_media_id'));

        $this->syncBanner($department, $request);

        return ApiResponse::success(new DepartmentResource($department->fresh()->load(['faculty', 'courses'])));
    }

    /**
     * Soft delete (Database Design, Section 2.1). Blocked with a 409
     * while the department still has courses assigned to it — same
     * reasoning as FacultyController::destroy()'s dependent-records
     * check, now that Course Management makes this reachable.
     */
    public function destroy(Department $department): Response|JsonResponse
    {
        Gate::authorize('delete', Department::class);

        $courseCount = $department->courses()->count();

        if ($courseCount > 0) {
            return ApiResponse::error(
                'This department cannot be deleted while it still has courses assigned to it.',
                409,
                ['conflict' => ['type' => 'has_dependent_records', 'related_resource' => 'courses', 'count' => $courseCount]]
            );
        }

        $department->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/departments — public, unauthenticated. Published only,
     * ordered by 'order'; filter[faculty]={slug} supported (API Design,
     * Section 7.1).
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $departments = Department::published()
            // Audit fix (Medium remediation) — 'media' wasn't eager-loaded,
            // so DepartmentResource's banner-image lookup fired one query
            // per row (15 queries for 13 departments, measured). Faculty's
            // equivalent endpoint already does this correctly.
            ->with(['faculty', 'media'])
            ->when($request->filled('faculty'), function ($query) use ($request) {
                $faculty = Faculty::where('slug', $request->string('faculty'))->first();
                $query->where('faculty_id', $faculty?->id ?? 0);
            })
            ->orderBy('order')
            ->get();

        return ApiResponse::success(DepartmentResource::collection($departments));
    }

    /**
     * GET /api/v1/departments/{slug} — public, unauthenticated.
     * "Department detail, including its courses" — only published
     * courses are shown publicly.
     */
    public function publicShow(string $slug): JsonResponse
    {
        $department = Department::published()->where('slug', $slug)->firstOrFail();
        $department->load(['faculty', 'media', 'courses' => fn ($query) => $query->published(), 'seoMeta']);

        return ApiResponse::success(new DepartmentResource($department));
    }

    private function syncBanner(Department $department, DepartmentRequest $request): void
    {
        if (! $request->has('banner_media_id')) {
            return;
        }

        $mediaId = $request->input('banner_media_id');

        if ($mediaId === null) {
            $department->clearMediaCollection('banner');

            return;
        }

        /** @var Media $media */
        $media = Media::findOrFail($mediaId);
        $media->moveKeepingCustomFields($department, 'banner');
    }
}
