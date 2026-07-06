<?php

namespace App\Http\Controllers\Api\V1\Seo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seo\UpdateSeoMetaRequest;
use App\Http\Resources\SeoEntityResource;
use App\Http\Resources\SeoMetaResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.8 — GET/PUT (upsert) per-entity, per the
 * Development Roadmap's Milestone 1 scope ("SeoMetaController
 * (upsert-only for now)"). `index()`/`typeIndex()` (the SEO Manager
 * overview screen) were added later, once every content module had
 * shipped and `config('seo.seoable_types')` had its final 8 entries —
 * building an overview screen before that would have meant rebuilding
 * it after every module.
 *
 * `{type}` is validated against config('seo.seoable_types'). Reads/
 * writes go through each model's own `seoMeta()` relation (the
 * App\Support\Concerns\HasSeoMeta trait every registered model uses, as
 * of the Public Website milestone) rather than building the
 * (seoable_type, seoable_id) pair by hand — the relation resolves
 * `seoable_type` through the enforced morph map alias automatically,
 * which a hand-built `['seoable_type' => $modelClass::class]` array
 * does not; that mismatch would silently break the moment any entity's
 * own Resource started reading `$model->seoMeta` as a real relation
 * instead of only ever being queried this same hand-built way.
 */
class SeoMetaController extends Controller
{
    /**
     * The column each seoable model uses as its human-readable label —
     * intentionally hand-mapped rather than guessed, since 'name' vs
     * 'title' vs 'course_name' varies per model and guessing wrong
     * would silently show blank labels instead of erroring loudly.
     */
    private const LABEL_COLUMNS = [
        'faculty' => 'name',
        'department' => 'name',
        'course' => 'course_name',
        'course-category' => 'name',
        'blog' => 'title',
        'news' => 'title',
        'event' => 'title',
        'page' => 'title',
    ];

    /** GET /admin/seo — one row per registered type, for the overview cards. */
    public function index(): JsonResponse
    {
        Gate::authorize('seo.view');

        $summary = collect(config('seo.seoable_types'))->map(function (string $modelClass, string $type) {
            $total = $modelClass::count();
            $withSeo = $modelClass::has('seoMeta')->count();

            return [
                'type' => $type,
                'label' => $this->typeLabel($type),
                'total' => $total,
                'with_seo' => $withSeo,
                'missing' => $total - $withSeo,
            ];
        })->values();

        return ApiResponse::success($summary->all());
    }

    /** GET /admin/seo/{type} — paginated per-entity SEO status for one type, for the drill-down list. */
    public function typeIndex(Request $request, string $type): JsonResponse
    {
        Gate::authorize('seo.view');

        $modelClass = $this->resolveType($type);
        $labelColumn = self::LABEL_COLUMNS[$type];

        $entities = $modelClass::query()
            ->with('seoMeta')
            ->when($request->filled('search'), fn ($q) => $q->where($labelColumn, 'like', '%'.$request->string('search').'%'))
            ->orderBy($labelColumn)
            ->paginate($request->integer('per_page', 20));

        // Normalizes the varying label column (name/title/course_name)
        // to one in-memory attribute so SeoEntityResource can stay a
        // single class shared across every type, rather than one
        // Resource subclass per label-column name.
        $entities->getCollection()->each(fn ($model) => $model->seo_manager_label = $model->{$labelColumn});

        return ApiResponse::success(SeoEntityResource::collection($entities));
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'faculty' => 'Faculties',
            'department' => 'Departments',
            'course' => 'Courses',
            'course-category' => 'Course Categories',
            'blog' => 'Blog Posts',
            'news' => 'News Articles',
            'event' => 'Events',
            'page' => 'Pages',
            default => ucfirst($type),
        };
    }

    public function show(string $type, int $id): JsonResponse
    {
        Gate::authorize('seo.view');

        $model = $this->resolveType($type)::find($id);

        if (! $model || ! $model->seoMeta) {
            return ApiResponse::success(null);
        }

        return ApiResponse::success(new SeoMetaResource($model->seoMeta));
    }

    public function update(UpdateSeoMetaRequest $request, string $type, int $id): JsonResponse
    {
        Gate::authorize('seo.edit');

        $model = $this->resolveType($type)::find($id);

        if (! $model) {
            return ApiResponse::error(ucfirst($type).' not found.', 404);
        }

        $seoMeta = $model->seoMeta()->updateOrCreate([], $request->validated());

        return ApiResponse::success(new SeoMetaResource($seoMeta));
    }

    private function resolveType(string $type): string
    {
        $modelClass = config("seo.seoable_types.{$type}");

        abort_if($modelClass === null, 404, "\"{$type}\" is not a recognized SEO-enabled entity type.");

        return $modelClass;
    }
}
