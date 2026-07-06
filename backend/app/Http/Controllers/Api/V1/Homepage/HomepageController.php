<?php

namespace App\Http\Controllers\Api\V1\Homepage;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\FacultyResource;
use App\Http\Resources\HeroSlideResource;
use App\Http\Resources\NewsResource;
use App\Http\Resources\PartnerResource;
use App\Http\Resources\TestimonialResource;
use App\Models\Course;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\HeroSlide;
use App\Models\HomepageSection;
use App\Models\News;
use App\Models\Partner;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;

/**
 * GET /api/v1/homepage (API Design, Section 7.2) — "Composed homepage
 * payload: enabled sections in order, each pre-populated with its
 * content." Only enabled homepage_sections rows appear, in their
 * configured order; content per section comes from whichever real
 * source exists for that section_key:
 *   - hero, testimonials, partners, featured_courses, faculties,
 *     latest_news, upcoming_events: dedicated tables, all now built
 *     (latest_news/upcoming_events were wired up as part of the Public
 *     Website milestone — News and Events didn't exist yet when the
 *     Homepage Builder itself shipped).
 *   - welcome, why_choose_us, statistics, cta, footer_widgets:
 *     config('settings')'s 'homepage' group (flat marketing copy, no
 *     dedicated table needed).
 *
 * Milestone 25 (Performance Optimization) — this is the single most
 * expensive public endpoint (up to 7 separate queries per request), so
 * it's cached via PublicCache. HomepageSectionController's reorder
 * action and Settings writes invalidate it explicitly; the remaining
 * dependency on Faculty/Course/News/Event/Testimonial/Partner/HeroSlide
 * writes (each its own controller from an earlier milestone) relies on
 * the 5 minute TTL instead — the same worst-case staleness those
 * models' own scheduled-publish jobs already tolerate.
 */
class HomepageController extends Controller
{
    public function show(): JsonResponse
    {
        $payload = PublicCache::remember('public.homepage', function () {
            $sections = HomepageSection::where('is_enabled', true)->orderBy('order')->get();

            return $sections->map(fn (HomepageSection $section) => [
                'section_key' => $section->section_key,
                ...$this->contentFor($section->section_key),
            ])->all();
        });

        return ApiResponse::success($payload);
    }

    /**
     * Resources are resolved to plain arrays (not ::collection()) since
     * they're embedded inside a hand-built array structure rather than
     * returned as the top-level response payload — the same reasoning as
     * PageBlockController's single-resource responses (see its docblock).
     */
    private function contentFor(string $sectionKey): array
    {
        return match ($sectionKey) {
            'hero' => ['items' => HeroSlide::where('is_active', true)->currentlyScheduled()->orderBy('order')->get()
                ->map(fn (HeroSlide $slide) => (new HeroSlideResource($slide))->resolve())->all()],
            'testimonials' => ['items' => Testimonial::where('is_active', true)->where('is_featured', true)->orderBy('order')->get()
                ->map(fn (Testimonial $t) => (new TestimonialResource($t))->resolve())->all()],
            'partners' => ['items' => Partner::where('is_active', true)->orderBy('order')->get()
                ->map(fn (Partner $p) => (new PartnerResource($p))->resolve())->all()],
            'faculties' => ['items' => Faculty::published()->orderBy('order')->get()
                ->map(fn (Faculty $f) => (new FacultyResource($f))->resolve())->all()],
            'featured_courses' => ['items' => Course::published()->where('is_featured', true)
                ->with(['faculty', 'department', 'level', 'mode', 'category'])->orderBy('order')->get()
                ->map(fn (Course $c) => (new CourseResource($c))->resolve())->all()],
            'latest_news' => ['items' => News::published()->orderByDesc('published_at')->limit(4)->get()
                ->map(fn (News $n) => (new NewsResource($n))->resolve())->all()],
            'upcoming_events' => ['items' => Event::published()->upcoming()->orderBy('starts_at')->limit(4)->get()
                ->map(fn (Event $e) => (new EventResource($e))->resolve())->all()],
            'welcome' => ['content' => [
                'heading' => $this->setting('welcome_heading'),
                'body' => $this->setting('welcome_body'),
                'media_id' => $this->setting('welcome_media_id'),
            ]],
            'why_choose_us' => ['items' => $this->setting('why_choose_us_items') ?? []],
            'statistics' => ['items' => $this->setting('statistics_items') ?? []],
            'cta' => ['content' => [
                'heading' => $this->setting('cta_heading'),
                'body' => $this->setting('cta_body'),
                'button_label' => $this->setting('cta_button_label'),
                'button_url' => $this->setting('cta_button_url'),
            ]],
            'footer_widgets' => ['items' => $this->setting('footer_widgets') ?? []],
            default => ['items' => []],
        };
    }

    private function setting(string $key): string|int|bool|array|null
    {
        return Settings::cast($key, Setting::where('key', $key)->value('value'));
    }
}
