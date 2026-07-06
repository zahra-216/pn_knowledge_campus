<?php

namespace App\Http\Controllers\Api\V1\Homepage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Homepage\UpdateHomepageContentRequest;
use App\Models\HomepageSection;
use App\Models\Setting;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * GET/PUT /api/v1/admin/homepage-content — the Welcome/Why Choose
 * Us/Statistics/CTA/Footer Widgets copy that has no dedicated content
 * table (see HomepageController's docblock). Gated by
 * HomepageSectionPolicy (homepage.view/homepage.edit), not
 * SettingPolicy — see UpdateHomepageContentRequest's docblock for why
 * this deliberately isn't just another call to /admin/settings.
 */
class HomepageContentController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', HomepageSection::class);

        return ApiResponse::success($this->currentContent());
    }

    public function update(UpdateHomepageContentRequest $request): JsonResponse
    {
        Gate::authorize('update', HomepageSection::class);

        foreach ($request->input('content') as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        PublicCache::forgetHomepage();

        return ApiResponse::success($this->currentContent());
    }

    private function currentContent(): array
    {
        $keys = array_keys(config('settings.homepage', []));

        return Setting::whereIn('key', $keys)->get()
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => Settings::cast($setting->key, $setting->value)])
            ->all();
    }
}
