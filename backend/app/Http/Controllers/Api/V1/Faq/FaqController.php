<?php

namespace App\Http\Controllers\Api\V1\Faq;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\FaqRequest;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * Milestone 17 (FAQ) — the global Site FAQ (rows with faqable_type/id
 * both null; see Faq::scopeGlobal()). Distinct from CourseFaqController,
 * which manages the same `faqs` table's course-scoped rows.
 *
 * Authorization deliberately uses raw `faq.*` ability strings rather
 * than `Gate::authorize($action, Faq::class)` — Faq::class is already
 * bound to CoursePolicy (checking courses.*) for the course-scoped
 * sub-resource, and a model class can only be bound to one policy.
 * These are the same permissions FaqPolicy checks for FaqCategory, just
 * without the class-based indirection.
 */
class FaqController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('faq.view');

        $faqs = Faq::query()
            ->global()
            ->with('category')
            ->when($request->filled('search'), fn ($q) => $q->search($request->string('search')))
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($cq) => $cq->where('slug', $request->string('category')));
            })
            ->orderBy('order')
            ->paginate($request->integer('per_page', 50));

        return ApiResponse::success(FaqResource::collection($faqs));
    }

    public function store(FaqRequest $request): JsonResponse
    {
        Gate::authorize('faq.create');

        $faq = Faq::create($request->validated());

        return ApiResponse::success(new FaqResource($faq->fresh('category')), 201);
    }

    public function show(Faq $faq): JsonResponse
    {
        Gate::authorize('faq.view');
        $this->ensureGlobal($faq);

        return ApiResponse::success(new FaqResource($faq->load('category')));
    }

    public function update(FaqRequest $request, Faq $faq): JsonResponse
    {
        Gate::authorize('faq.edit');
        $this->ensureGlobal($faq);

        $faq->update($request->validated());

        return ApiResponse::success(new FaqResource($faq->fresh('category')));
    }

    public function destroy(Faq $faq): Response
    {
        Gate::authorize('faq.delete');
        $this->ensureGlobal($faq);

        $faq->delete();

        return response()->noContent();
    }

    /**
     * The `faqs` table is shared with CourseFaqController (Course
     * Management) — implicit route model binding on {faq} would
     * otherwise let this admin-wide endpoint reach into a course's
     * scoped FAQs by id. Only global rows (no owning entity) may be
     * read/written here.
     */
    private function ensureGlobal(Faq $faq): void
    {
        abort_if($faq->faqable_type !== null || $faq->faqable_id !== null, 404);
    }

    /**
     * GET /api/v1/faqs — public, unauthenticated. Active only, ordered,
     * optional ?search= (question/answer) and ?category=slug filters.
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $faqs = Faq::query()
            ->global()
            ->active()
            ->with('category')
            ->when($request->filled('search'), fn ($q) => $q->search($request->string('search')))
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($cq) => $cq->where('slug', $request->string('category')));
            })
            ->orderBy('order')
            ->get();

        return ApiResponse::success(FaqResource::collection($faqs));
    }
}
