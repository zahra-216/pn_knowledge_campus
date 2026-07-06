<?php

namespace App\Http\Controllers\Api\V1\Homepage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Homepage\HomepageSectionReorderRequest;
use App\Http\Resources\HomepageSectionResource;
use App\Models\HomepageSection;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.3. Sections are a fixed seeded set (always
 * exactly HomepageSection::SECTIONS, one row each) — this controller
 * only lists and bulk reorders/toggles them in place, the same
 * fixed-set-of-rows pattern as OfficeHourController; there's no
 * store/show/destroy because nothing is ever created or deleted here.
 */
class HomepageSectionController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', HomepageSection::class);

        $sections = HomepageSection::orderBy('order')->get();

        return ApiResponse::success(HomepageSectionResource::collection($sections));
    }

    /**
     * PATCH /api/v1/admin/homepage-sections/reorder — bulk order/enabled
     * update, applied in one transaction (mirrors MenuItemController's
     * reorder).
     */
    public function reorder(HomepageSectionReorderRequest $request): JsonResponse
    {
        Gate::authorize('update', HomepageSection::class);

        DB::transaction(function () use ($request) {
            foreach ($request->input('sections') as $entry) {
                HomepageSection::where('section_key', $entry['section_key'])->update([
                    'order' => $entry['order'],
                    'is_enabled' => $entry['is_enabled'],
                ]);
            }
        });

        PublicCache::forgetHomepage();

        $sections = HomepageSection::orderBy('order')->get();

        return ApiResponse::success(HomepageSectionResource::collection($sections));
    }
}
