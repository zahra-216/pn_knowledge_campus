<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;

/**
 * GET /api/v1/public/bootstrap — combines settings/public, menus/header,
 * menus/footer, and social-links into one response. These four were
 * previously fetched as separate requests by PublicLayout, SiteHeader,
 * and SiteFooter on every single page load — the single biggest
 * contributor to the request pile-up on `php artisan serve` (which
 * can't handle them in parallel). Same underlying data/shape as their
 * individual endpoints, just batched.
 *
 * Each piece is still cached independently via PublicCache (same as
 * MenuController::publicShow), so this doesn't duplicate cache entries —
 * it just avoids 4 separate HTTP round-trips to fetch data that was
 * already going to be cached anyway.
 */
class PublicBootstrapController extends Controller
{
    public function show(): JsonResponse
    {
        $settings = PublicCache::remember('public.settings', function () {
            return Setting::where('is_public', true)->pluck('value', 'key');
        });

        $headerMenu = PublicCache::remember('public.menu.header', function () {
            return $this->loadMenu('header');
        });

        $footerMenu = PublicCache::remember('public.menu.footer', function () {
            return $this->loadMenu('footer');
        });

        $socialLinks = PublicCache::remember('public.social-links', function () {
            return SocialLink::where('is_active', true)->orderBy('order')->get(['id', 'platform', 'url', 'order']);
        });

        return ApiResponse::success([
            'settings' => $settings,
            'header_menu' => $headerMenu,
            'footer_menu' => $footerMenu,
            'social_links' => $socialLinks,
        ]);
    }

    private function loadMenu(string $key): ?array
    {
        $menu = Menu::where('name', $key)->first();
        if (! $menu) {
            return null;
        }

        $menu->load(['topLevelItems' => function ($query) {
            $query->where('is_active', true)->currentlyScheduled();
        }, 'topLevelItems.children' => function ($query) {
            $query->where('is_active', true)->currentlyScheduled();
        }, 'topLevelItems.children.children' => function ($query) {
            $query->where('is_active', true)->currentlyScheduled();
        }, 'topLevelItems.children.children.children' => function ($query) {
            $query->where('is_active', true)->currentlyScheduled();
        }]);

        return (new MenuResource($menu))->resolve();
    }
}