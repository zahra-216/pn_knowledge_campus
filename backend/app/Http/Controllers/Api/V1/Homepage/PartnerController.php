<?php

namespace App\Http\Controllers\Api\V1\Homepage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Homepage\PartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Media;
use App\Models\Partner;
use App\Models\PartnerCategory;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.4. SRS Permission Matrix, "Partners" row: Super
 * Admin/Administrator = Full; Marketing = Create/Edit; Content
 * Editor/Admissions = no access.
 */
class PartnerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Partner::class);

        $partners = Partner::query()->with('category')->orderBy('order')->paginate($request->integer('per_page', 20));

        return ApiResponse::success(PartnerResource::collection($partners));
    }

    public function store(PartnerRequest $request): JsonResponse
    {
        Gate::authorize('create', Partner::class);

        $partner = Partner::create($request->safe()->except('media_id'));

        $this->attachMedia($partner, $request->input('media_id'));

        return ApiResponse::success(new PartnerResource($partner->fresh('category')), 201);
    }

    public function show(Partner $partner): JsonResponse
    {
        Gate::authorize('viewAny', Partner::class);

        return ApiResponse::success(new PartnerResource($partner->load('category')));
    }

    public function update(PartnerRequest $request, Partner $partner): JsonResponse
    {
        Gate::authorize('update', Partner::class);

        $partner->update($request->safe()->except('media_id'));

        if ($request->has('media_id')) {
            $this->attachMedia($partner, $request->input('media_id'));
        }

        return ApiResponse::success(new PartnerResource($partner->fresh('category')));
    }

    public function destroy(Partner $partner): Response
    {
        Gate::authorize('delete', Partner::class);

        $partner->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/partners — public, unauthenticated, ordered. Optional
     * ?category=slug filter (Milestone 16).
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $partners = Partner::where('is_active', true)
            ->with('category')
            ->when($request->filled('category'), function ($query) use ($request) {
                $category = PartnerCategory::where('slug', $request->string('category'))->first();
                $query->where('category_id', $category?->id ?? 0);
            })
            ->orderBy('order')
            ->get();

        return ApiResponse::success(PartnerResource::collection($partners));
    }

    private function attachMedia(Partner $partner, ?int $mediaId): void
    {
        if ($mediaId === null) {
            $partner->clearMediaCollection('logo');

            return;
        }

        /** @var Media $media */
        $media = Media::findOrFail($mediaId);
        $media->moveKeepingCustomFields($partner, 'logo');
    }
}
