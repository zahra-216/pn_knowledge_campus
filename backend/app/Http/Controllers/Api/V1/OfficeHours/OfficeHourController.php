<?php

namespace App\Http\Controllers\Api\V1\OfficeHours;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeHours\UpdateOfficeHoursRequest;
use App\Http\Resources\OfficeHourResource;
use App\Models\OfficeHour;
use App\Models\Setting;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Settings module extension. Gated by the same settings.view/edit
 * permissions as the rest of the Settings module (SettingPolicy) — Office
 * Hours is a Settings sub-feature, not a module of its own, same
 * reasoning already applied to Branches and Social Links in Milestone 1.
 */
class OfficeHourController extends Controller
{
    /**
     * GET /api/v1/admin/office-hours — all 7 days, in week order.
     */
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Setting::class);

        $hours = OfficeHour::orderBy('order')->get();

        return ApiResponse::success(OfficeHourResource::collection($hours));
    }

    /**
     * PUT /api/v1/admin/office-hours — bulk update by day.
     */
    public function update(UpdateOfficeHoursRequest $request): JsonResponse
    {
        Gate::authorize('update', Setting::class);

        foreach ($request->input('hours') as $day => $entry) {
            OfficeHour::where('day', $day)->update(array_intersect_key(
                $entry,
                array_flip(['is_open', 'opens_at', 'closes_at', 'note'])
            ));
        }

        PublicCache::forgetOfficeHours();

        $hours = OfficeHour::orderBy('order')->get();

        return ApiResponse::success(OfficeHourResource::collection($hours));
    }

    /**
     * GET /api/v1/office-hours — public, in week order. Cached (see
     * PublicCache's docblock).
     */
    public function publicIndex(): JsonResponse
    {
        $data = PublicCache::remember(
            'public.office_hours',
            fn () => OfficeHourResource::collection(OfficeHour::orderBy('order')->get())->resolve()
        );

        return ApiResponse::success($data);
    }
}
