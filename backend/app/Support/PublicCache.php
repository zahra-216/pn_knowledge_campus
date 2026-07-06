<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Milestone 25 (Performance Optimization) — a thin, explicit registry of
 * the cache keys used for the handful of public endpoints that are read
 * on nearly every page load (Settings/Branches/SocialLinks/OfficeHours/
 * Menus/Homepage) but only change through a small, known set of admin
 * actions. Centralizing the key names here means a controller that
 * writes to one of these models doesn't need to know the exact string
 * used by the controller that reads it — it just calls the matching
 * forget*() method.
 *
 * The 5 minute TTL on every key is a safety net, not the primary
 * invalidation mechanism — the primary mechanism is the explicit
 * forget() call made by each admin write action. The TTL only matters
 * for the one case that's impractical to hook explicitly: Homepage's
 * payload also depends on Faculty/Course/News/Event/Testimonial/
 * Partner/HeroSlide records, which are written from a dozen unrelated
 * controllers across earlier milestones — a 5 minute worst-case
 * staleness window there is a deliberate trade against threading cache
 * invalidation through all of them for content that already tolerates
 * a short publish delay (SRS's own scheduled-publish jobs run once a
 * minute).
 */
class PublicCache
{
    private const TTL_MINUTES = 5;

    public static function remember(string $key, Closure $callback): mixed
    {
        return Cache::remember($key, now()->addMinutes(self::TTL_MINUTES), $callback);
    }

    /**
     * Settings feed Homepage's welcome/why_choose_us/cta/footer_widgets
     * content too (see HomepageController::setting()), so a settings
     * write invalidates both.
     */
    public static function forgetSettings(): void
    {
        Cache::forget('public.settings');
        self::forgetHomepage();
    }

    public static function forgetBranches(): void
    {
        Cache::forget('public.branches');
    }

    public static function forgetSocialLinks(): void
    {
        Cache::forget('public.social_links');
    }

    public static function forgetOfficeHours(): void
    {
        Cache::forget('public.office_hours');
    }

    public static function forgetHomepage(): void
    {
        Cache::forget('public.homepage');
    }

    public static function forgetMenu(string $name): void
    {
        Cache::forget("public.menu.{$name}");
    }
}
