<?php

namespace App\Http\Controllers\Api\V1\Courses;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseLookupResource;
use App\Models\Course;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Shared CRUD for the three Course lookup tables (CourseLevel,
 * CourseMode, CourseCategory) — all three are the identical {name, slug,
 * order} shape (see CourseLookupResource), so one base controller avoids
 * three near-duplicate copies of the same five methods. Gated by
 * courses.* — these are treated as Course Management sub-parts, the
 * same reasoning already applied to Branches/Social Links under
 * Settings.
 */
abstract class CourseLookupController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function tableName(): string;

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Course::class);

        $modelClass = $this->modelClass();
        $items = $modelClass::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 50));

        return ApiResponse::success(CourseLookupResource::collection($items));
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Course::class);

        $data = $this->validated($request);
        $modelClass = $this->modelClass();
        $item = $modelClass::create($data);

        return ApiResponse::success(new CourseLookupResource($item), 201);
    }

    public function show(int $id): JsonResponse
    {
        Gate::authorize('viewAny', Course::class);

        return ApiResponse::success(new CourseLookupResource($this->findOrFail($id)));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        Gate::authorize('update', Course::class);

        $item = $this->findOrFail($id);
        $data = $this->validated($request, $item->id);
        $item->update($data);

        return ApiResponse::success(new CourseLookupResource($item));
    }

    public function destroy(int $id): Response
    {
        Gate::authorize('delete', Course::class);

        $this->findOrFail($id)->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/course-levels, /course-modes, /course-categories —
     * public, unauthenticated. "List all course levels/modes (for filter
     * UI)" per the API Design, Section 7.1.
     */
    public function publicIndex(): JsonResponse
    {
        $modelClass = $this->modelClass();
        $items = $modelClass::query()->orderBy('order')->get();

        return ApiResponse::success(CourseLookupResource::collection($items));
    }

    private function findOrFail(int $id): Model
    {
        $modelClass = $this->modelClass();

        return $modelClass::findOrFail($id);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $isCreate = $ignoreId === null;

        $input = $request->all();
        if (empty($input['slug']) && ! empty($input['name'])) {
            $input['slug'] = Str::slug($input['name']);
        }

        return Validator::make($input, [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:60', Rule::unique($this->tableName(), 'name')->ignore($ignoreId)],
            'slug' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:70', Rule::unique($this->tableName(), 'slug')->ignore($ignoreId)],
            'order' => ['sometimes', 'integer'],
        ])->validate();
    }
}
