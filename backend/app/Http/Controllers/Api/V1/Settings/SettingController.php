<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Implements the Settings endpoints from API Design, Section 8.9 and
 * the public settings endpoint from Section 7.5. Development Roadmap,
 * Milestone 1.
 */
class SettingController extends Controller
{
    /**
     * GET /api/v1/admin/settings — every setting, grouped, Super Admin only.
     */
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Setting::class);

        $settings = Setting::orderBy('group')->orderBy('key')->get();

        return ApiResponse::success(SettingResource::collection($settings));
    }

    /**
     * PUT /api/v1/admin/settings — bulk update by key. Only the `value`
     * column is ever written; group/is_public stay fixed from the
     * config/settings.php registry regardless of what the client sends.
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        Gate::authorize('update', Setting::class);

        foreach ($request->input('settings') as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        PublicCache::forgetSettings();

        $settings = Setting::orderBy('group')->orderBy('key')->get();

        return ApiResponse::success(SettingResource::collection($settings));
    }

    /**
     * GET /api/v1/settings/public — publicly safe settings only. Never
     * exposes a key with is_public=false (e.g. every smtp_* key), per
     * SRS Section 7.4. Cached (see PublicCache's docblock) since this
     * is fetched on nearly every public page load.
     */
    public function publicIndex(): JsonResponse
    {
        $data = PublicCache::remember(
            'public.settings',
            fn () => SettingResource::collection(Setting::where('is_public', true)->orderBy('group')->orderBy('key')->get())->resolve()
        );

        return ApiResponse::success($data);
    }
}
