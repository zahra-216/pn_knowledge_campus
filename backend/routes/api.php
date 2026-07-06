<?php

use App\Http\Controllers\Api\V1\Analytics\AnalyticsController;
use App\Http\Controllers\Api\V1\Applications\ApplicationController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Blog\BlogCategoryController;
use App\Http\Controllers\Api\V1\Blog\BlogPostController;
use App\Http\Controllers\Api\V1\Blog\TagController;
use App\Http\Controllers\Api\V1\Branches\BranchController;
use App\Http\Controllers\Api\V1\Courses\CourseCategoryController;
use App\Http\Controllers\Api\V1\Courses\CourseController;
use App\Http\Controllers\Api\V1\Courses\CourseCurriculumController;
use App\Http\Controllers\Api\V1\Courses\CourseFaqController;
use App\Http\Controllers\Api\V1\Courses\CourseLevelController;
use App\Http\Controllers\Api\V1\Courses\CourseModeController;
use App\Http\Controllers\Api\V1\Departments\DepartmentController;
use App\Http\Controllers\Api\V1\Downloads\DownloadCategoryController;
use App\Http\Controllers\Api\V1\Downloads\DownloadController;
use App\Http\Controllers\Api\V1\Events\EventController;
use App\Http\Controllers\Api\V1\Events\EventSpeakerController;
use App\Http\Controllers\Api\V1\Faculties\FacultyController;
use App\Http\Controllers\Api\V1\Faq\FaqCategoryController;
use App\Http\Controllers\Api\V1\Faq\FaqController;
use App\Http\Controllers\Api\V1\Gallery\GalleryAlbumController;
use App\Http\Controllers\Api\V1\Homepage\HeroSlideController;
use App\Http\Controllers\Api\V1\Homepage\HomepageContentController;
use App\Http\Controllers\Api\V1\Homepage\HomepageController;
use App\Http\Controllers\Api\V1\Homepage\HomepageSectionController;
use App\Http\Controllers\Api\V1\Homepage\PartnerCategoryController;
use App\Http\Controllers\Api\V1\Homepage\PartnerController;
use App\Http\Controllers\Api\V1\Homepage\TestimonialController;
use App\Http\Controllers\Api\V1\Inquiries\InquiryController;
use App\Http\Controllers\Api\V1\Media\MediaController;
use App\Http\Controllers\Api\V1\Media\MediaFolderController;
use App\Http\Controllers\Api\V1\Menus\MenuController;
use App\Http\Controllers\Api\V1\Menus\MenuItemController;
use App\Http\Controllers\Api\V1\News\NewsCategoryController;
use App\Http\Controllers\Api\V1\News\NewsController;
use App\Http\Controllers\Api\V1\Notifications\NotificationController;
use App\Http\Controllers\Api\V1\OfficeHours\OfficeHourController;
use App\Http\Controllers\Api\V1\Pages\PageBlockController;
use App\Http\Controllers\Api\V1\Pages\PageController;
use App\Http\Controllers\Api\V1\Search\SearchController;
use App\Http\Controllers\Api\V1\Seo\SeoMetaController;
use App\Http\Controllers\Api\V1\Settings\SettingController;
use App\Http\Controllers\Api\V1\SocialLinks\SocialLinkController;
use App\Http\Controllers\Api\V1\Users\RoleController;
use App\Http\Controllers\Api\V1\Users\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — /api/v1
|--------------------------------------------------------------------------
|
| Every route in this file matches an entry in the API Design document.
| Milestone 0 wired the Authentication group (Section 2.2). Milestone 1
| adds Settings, Branches, Social Links, Media Library, and the
| infrastructure-only SEO Manager endpoints (Sections 7.5, 8.6, 8.8, 8.9).
| A subsequent Settings-module extension adds Office Hours (not in the
| original API Design document — a client-directed addition, gated by
| the same settings.* permissions as the rest of the module). A later
| pass adds the Menu Builder (Section 8.3) — header/footer navigation,
| nested to arbitrary depth, gated by menus.* permissions. The Page
| Builder (Section 8.3) follows, gated by pages.* permissions. The
| Homepage Builder (Section 8.3) plus Hero Slider/Testimonials/Partners
| (Sections 8.3-8.4) follow, each gated by their own permission set per
| the SRS Permission Matrix. Faculty Management (Section 8.2) is the
| first Academic Structure module, followed by Department Management
| and Course Management (Section 8.2) — the latter also brings the
| course_levels/course_modes/course_categories lookup tables and the
| course-scoped curriculum/FAQ sub-resources. Blog (Section 8.4) is the
| first Engagement Content module, bringing blog-categories and the
| shared tags lookup alongside it. News (Section 8.4) follows the same
| shape, with its own news-categories. Events (Section 8.4) follows too,
| with an event-scoped speakers sub-resource (not in the document — a
| client-requested addition) and no publish action (also not documented
| for Events, unlike News/Blog/Page/Course). Gallery (Section 8.4) closes
| out Engagement Content, with no SEO integration (deliberately excluded
| by the document, unlike every other module here).
|
*/

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        // Milestone 25 (Security Review) — 'login' is a dedicated,
        // stricter limiter (5/min per IP+email) than the general 'api'
        // one every other route uses (60/min); see its registration in
        // AppServiceProvider for why. forgot-password shares it since
        // it's an unauthenticated, similarly abusable/enumerable action.
        Route::middleware('throttle:login')->group(function () {
            Route::post('login', [AuthController::class, 'login']);
            Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        });
        Route::post('reset-password', [AuthController::class, 'resetPassword']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    // ------------------------------------------------------------------
    // Admin API — every route below requires a valid Sanctum token;
    // permission enforcement happens per-action inside each controller
    // (Policy or direct permission-string check), per API Design Section
    // 8's "Auth" column convention.
    // ------------------------------------------------------------------
    Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
        Route::get('settings', [SettingController::class, 'index']);
        Route::put('settings', [SettingController::class, 'update']);

        Route::apiResource('branches', BranchController::class);
        Route::apiResource('social-links', SocialLinkController::class);

        Route::get('media-folders', [MediaFolderController::class, 'index']);
        Route::post('media-folders', [MediaFolderController::class, 'store']);
        Route::put('media-folders/{media_folder}', [MediaFolderController::class, 'update']);
        Route::delete('media-folders/{media_folder}', [MediaFolderController::class, 'destroy']);

        Route::get('media', [MediaController::class, 'index']);
        Route::post('media', [MediaController::class, 'store']);
        Route::put('media/{medium}', [MediaController::class, 'update']);
        Route::delete('media/{medium}', [MediaController::class, 'destroy']);
        Route::post('media/{medium}/replace', [MediaController::class, 'replace']);

        // SEO Manager overview — must be declared before {type}/{id} so
        // a bare 'seo' or one-segment 'seo/{type}' isn't swallowed by
        // the two-segment per-entity route below.
        Route::get('seo', [SeoMetaController::class, 'index']);
        Route::get('seo/{type}', [SeoMetaController::class, 'typeIndex']);
        Route::get('seo/{type}/{id}', [SeoMetaController::class, 'show']);
        Route::put('seo/{type}/{id}', [SeoMetaController::class, 'update']);

        Route::get('office-hours', [OfficeHourController::class, 'index']);
        Route::put('office-hours', [OfficeHourController::class, 'update']);

        Route::apiResource('menus', MenuController::class)->except(['show'])->parameters(['menus' => 'menu']);
        Route::get('menus/{menu}', [MenuController::class, 'show']);

        // scopeBindings() ensures {item} must actually belong to {menu} —
        // otherwise implicit binding would resolve any MenuItem id
        // regardless of which menu's URL it was requested under.
        Route::prefix('menus/{menu}')->scopeBindings()->group(function () {
            Route::patch('items/reorder', [MenuItemController::class, 'reorder']);
            Route::get('items', [MenuItemController::class, 'index']);
            Route::post('items', [MenuItemController::class, 'store']);
            Route::put('items/{item}', [MenuItemController::class, 'update']);
            Route::delete('items/{item}', [MenuItemController::class, 'destroy']);
        });

        Route::apiResource('pages', PageController::class)->parameters(['pages' => 'page']);
        Route::patch('pages/{page}/publish', [PageController::class, 'publish']);

        // scopeBindings() ensures {block} must actually belong to {page} —
        // same IDOR protection as menus/{menu}/items/{item}.
        Route::prefix('pages/{page}')->scopeBindings()->group(function () {
            Route::patch('blocks/reorder', [PageBlockController::class, 'reorder']);
            Route::get('blocks', [PageBlockController::class, 'index']);
            Route::post('blocks', [PageBlockController::class, 'store']);
            Route::put('blocks/{block}', [PageBlockController::class, 'update']);
            Route::delete('blocks/{block}', [PageBlockController::class, 'destroy']);
        });

        Route::get('homepage-sections', [HomepageSectionController::class, 'index']);
        Route::patch('homepage-sections/reorder', [HomepageSectionController::class, 'reorder']);

        // Welcome/Why Choose Us/Statistics/CTA/Footer Widgets copy —
        // gated by homepage.* (not settings.*), see
        // UpdateHomepageContentRequest's docblock for why this isn't
        // just another call to /admin/settings.
        Route::get('homepage-content', [HomepageContentController::class, 'index']);
        Route::put('homepage-content', [HomepageContentController::class, 'update']);

        Route::apiResource('hero-slides', HeroSlideController::class)->parameters(['hero-slides' => 'heroSlide']);
        Route::apiResource('testimonials', TestimonialController::class);
        Route::apiResource('partner-categories', PartnerCategoryController::class)->parameters(['partner-categories' => 'partnerCategory']);
        Route::apiResource('partners', PartnerController::class);

        Route::apiResource('faculties', FacultyController::class)->parameters(['faculties' => 'faculty']);
        Route::post('faculties/{faculty}/gallery', [FacultyController::class, 'attachGallery']);
        Route::delete('faculties/{faculty}/gallery/{mediaId}', [FacultyController::class, 'detachGallery']);

        Route::apiResource('departments', DepartmentController::class);

        Route::apiResource('course-levels', CourseLevelController::class);
        Route::apiResource('course-modes', CourseModeController::class);

        // Course Category is its own full resource (icon/image media, a
        // parent/child tree, SEO) — no longer a CourseLookupController
        // subclass like Level/Mode. The reorder route must be declared
        // before the apiResource's {courseCategory} routes so "reorder"
        // isn't swallowed as an id.
        Route::patch('course-categories/reorder', [CourseCategoryController::class, 'reorder']);
        Route::apiResource('course-categories', CourseCategoryController::class)->parameters(['course-categories' => 'courseCategory']);

        Route::apiResource('courses', CourseController::class)->parameters(['courses' => 'course']);
        Route::patch('courses/{course}/publish', [CourseController::class, 'publish']);
        Route::post('courses/{course}/media', [CourseController::class, 'attachMedia']);
        Route::delete('courses/{course}/media/{mediaId}', [CourseController::class, 'detachMedia']);

        // scopeBindings() ensures {curriculumItem}/{faq} must actually
        // belong to {course} — same IDOR protection as
        // menus/{menu}/items/{item}. The parameter name "curriculumItem"
        // (not "item") is deliberate: scopeBindings() resolves the
        // relation by pluralizing the parameter name, and Course's
        // relation is curriculumItems(), not items().
        Route::prefix('courses/{course}')->scopeBindings()->group(function () {
            Route::patch('curriculum/reorder', [CourseCurriculumController::class, 'reorder']);
            Route::get('curriculum', [CourseCurriculumController::class, 'index']);
            Route::post('curriculum', [CourseCurriculumController::class, 'store']);
            Route::put('curriculum/{curriculumItem}', [CourseCurriculumController::class, 'update']);
            Route::delete('curriculum/{curriculumItem}', [CourseCurriculumController::class, 'destroy']);

            Route::get('faqs', [CourseFaqController::class, 'index']);
            Route::post('faqs', [CourseFaqController::class, 'store']);
            Route::put('faqs/{faq}', [CourseFaqController::class, 'update']);
            Route::delete('faqs/{faq}', [CourseFaqController::class, 'destroy']);
        });

        Route::apiResource('blog-categories', BlogCategoryController::class)->parameters(['blog-categories' => 'blogCategory']);
        Route::apiResource('tags', TagController::class);

        Route::apiResource('blog', BlogPostController::class)->parameters(['blog' => 'blogPost']);
        Route::patch('blog/{blogPost}/publish', [BlogPostController::class, 'publish']);
        Route::post('blog/{blogPost}/media', [BlogPostController::class, 'attachMedia']);
        Route::delete('blog/{blogPost}/media/{mediaId}', [BlogPostController::class, 'detachMedia']);

        Route::apiResource('news-categories', NewsCategoryController::class)->parameters(['news-categories' => 'newsCategory']);

        Route::apiResource('news', NewsController::class);
        Route::patch('news/{news}/publish', [NewsController::class, 'publish']);
        Route::post('news/{news}/media', [NewsController::class, 'attachMedia']);
        Route::delete('news/{news}/media/{mediaId}', [NewsController::class, 'detachMedia']);

        Route::apiResource('events', EventController::class);
        Route::post('events/{event}/media', [EventController::class, 'attachMedia']);
        Route::delete('events/{event}/media/{mediaId}', [EventController::class, 'detachMedia']);

        // scopeBindings() ensures {speaker} must actually belong to
        // {event} — same IDOR protection as courses/{course}/faqs/{faq}.
        Route::prefix('events/{event}')->scopeBindings()->group(function () {
            Route::get('speakers', [EventSpeakerController::class, 'index']);
            Route::post('speakers', [EventSpeakerController::class, 'store']);
            Route::put('speakers/{speaker}', [EventSpeakerController::class, 'update']);
            Route::delete('speakers/{speaker}', [EventSpeakerController::class, 'destroy']);
        });

        Route::apiResource('gallery-albums', GalleryAlbumController::class)->parameters(['gallery-albums' => 'galleryAlbum']);
        Route::post('gallery-albums/{galleryAlbum}/media', [GalleryAlbumController::class, 'attachMedia']);
        Route::put('gallery-albums/{galleryAlbum}/media/{mediaId}', [GalleryAlbumController::class, 'updateMediaCaption']);
        Route::delete('gallery-albums/{galleryAlbum}/media/{mediaId}', [GalleryAlbumController::class, 'detachMedia']);

        // Milestone 17 — the global Site FAQ (distinct from
        // courses/{course}/faqs above, which manages the same table's
        // course-scoped rows; see FaqController's docblock).
        Route::apiResource('faq-categories', FaqCategoryController::class)->parameters(['faq-categories' => 'faqCategory']);
        Route::apiResource('faqs', FaqController::class);

        // Milestone 18 — the standalone Downloads catalog (Prospectus,
        // Application Forms, Brochures), not Course's own `downloads`
        // media collection.
        Route::apiResource('download-categories', DownloadCategoryController::class)->parameters(['download-categories' => 'downloadCategory']);
        Route::apiResource('downloads', DownloadController::class);

        // Milestone 20 — Online Applications review queue. 'export' must
        // be declared before the {application} show route so it isn't
        // swallowed as an id. No admin create/delete — applications are
        // authored by visitors (see the public group below), not staff;
        // staff only review/decide (see ApplicationController's docblock).
        Route::get('applications/export', [ApplicationController::class, 'export']);
        Route::get('applications', [ApplicationController::class, 'index']);
        Route::get('applications/{application}', [ApplicationController::class, 'show']);
        Route::patch('applications/{application}/under-review', [ApplicationController::class, 'markUnderReview']);
        Route::patch('applications/{application}/approve', [ApplicationController::class, 'approve']);
        Route::patch('applications/{application}/reject', [ApplicationController::class, 'reject']);

        // Milestone 23 — in-app notification bell. Scoped to the
        // authenticated user's own notifications, no extra permission
        // gate (see NotificationController's docblock).
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead']);

        // Milestone 24 — Dashboard Analytics.
        Route::get('analytics/dashboard', [AnalyticsController::class, 'dashboard']);

        // Inquiry Management admin inbox. 'export' declared before the
        // {inquiry} routes for the same reason as Applications' own.
        Route::get('inquiries/export', [InquiryController::class, 'export']);
        Route::get('inquiries', [InquiryController::class, 'index']);
        Route::get('inquiries/{inquiry}', [InquiryController::class, 'show']);
        Route::patch('inquiries/{inquiry}/status', [InquiryController::class, 'updateStatus']);
        Route::delete('inquiries/{inquiry}', [InquiryController::class, 'destroy']);

        // Users, Roles & Permissions (SRS FR-29). 'permissions' is
        // declared before apiResource('roles', ...) purely for
        // readability — no actual path overlap.
        Route::get('permissions', [RoleController::class, 'permissions']);
        Route::apiResource('roles', RoleController::class)->parameters(['roles' => 'role']);
        Route::apiResource('users', UserController::class)->parameters(['users' => 'user']);
    });

    // ------------------------------------------------------------------
    // Public content API — unauthenticated, active/published records
    // only. Pages/Menus/Homepage/Courses/News etc. arrive in their own
    // later Development Roadmap milestones.
    //
    // Milestone 25 (Performance Optimization) wraps every one of these
    // GET endpoints in Laravel's built-in `cache.headers` middleware
    // (Illuminate\Http\Middleware\SetCacheHeaders — a default alias, no
    // registration needed), so a browser or any CDN in front of this API
    // can serve repeat requests without round-tripping here at all. This
    // is on top of, not instead of, the server-side Cache::remember
    // layer added to Settings/Branches/SocialLinks/OfficeHours/Menus/
    // Homepage below — those still need to be fast for the requests
    // that *do* reach PHP (a cold CDN, a non-caching client, etc).
    // ------------------------------------------------------------------
    Route::middleware('cache.headers:public;max_age=60')->group(function () {
        Route::get('settings/public', [SettingController::class, 'publicIndex']);
        Route::get('branches', [BranchController::class, 'publicIndex']);
        Route::get('social-links', [SocialLinkController::class, 'publicIndex']);
        Route::get('office-hours', [OfficeHourController::class, 'publicIndex']);
        Route::get('menus/{key}', [MenuController::class, 'publicShow']);
        Route::get('pages/{slug}', [PageController::class, 'publicShow']);
        Route::get('homepage', [HomepageController::class, 'show']);
        Route::get('hero-slides', [HeroSlideController::class, 'publicIndex']);
        Route::get('testimonials', [TestimonialController::class, 'publicIndex']);
        Route::get('partner-categories', [PartnerCategoryController::class, 'publicIndex']);
        Route::get('partners', [PartnerController::class, 'publicIndex']);
        Route::get('faculties', [FacultyController::class, 'publicIndex']);
        Route::get('faculties/{slug}', [FacultyController::class, 'publicShow']);
        Route::get('departments', [DepartmentController::class, 'publicIndex']);
        Route::get('departments/{slug}', [DepartmentController::class, 'publicShow']);
        Route::get('course-levels', [CourseLevelController::class, 'publicIndex']);
        Route::get('course-modes', [CourseModeController::class, 'publicIndex']);
        Route::get('course-categories', [CourseCategoryController::class, 'publicIndex']);
        Route::get('courses', [CourseController::class, 'publicIndex']);
        Route::get('courses/{slug}', [CourseController::class, 'publicShow']);
        Route::get('blog-categories', [BlogCategoryController::class, 'publicIndex']);
        Route::get('blog', [BlogPostController::class, 'publicIndex']);
        Route::get('blog/{slug}', [BlogPostController::class, 'publicShow']);
        Route::get('news-categories', [NewsCategoryController::class, 'publicIndex']);
        Route::get('news', [NewsController::class, 'publicIndex']);
        Route::get('news/{slug}', [NewsController::class, 'publicShow']);
        Route::get('events', [EventController::class, 'publicIndex']);
        Route::get('events/{slug}', [EventController::class, 'publicShow']);
        Route::get('gallery-albums', [GalleryAlbumController::class, 'publicIndex']);
        Route::get('gallery-albums/{slug}', [GalleryAlbumController::class, 'publicShow']);
        Route::get('faq-categories', [FaqCategoryController::class, 'publicIndex']);
        Route::get('faqs', [FaqController::class, 'publicIndex']);
        Route::get('download-categories', [DownloadCategoryController::class, 'publicIndex']);
        Route::get('downloads', [DownloadController::class, 'publicIndex']);
        Route::get('media/resolve', [MediaController::class, 'publicResolve']);
        Route::get('search/autocomplete', [SearchController::class, 'autocomplete']);
        Route::get('search', [SearchController::class, 'index']);
    });

    // Audit fix (Critical/High remediation) — every public, unauthenticated
    // write endpoint below previously relied only on the blanket 60/min
    // 'api' limiter, cheap enough to spam the Inquiry inbox, create junk
    // draft Applications (each with a file upload), or flood the page-view
    // tracker — each of the first two also dispatches a queued email. A
    // dedicated, stricter 'public-write' limiter (see AppServiceProvider)
    // mirrors the same per-IP approach already used for 'login'.
    Route::middleware('throttle:public-write')->group(function () {
        Route::post('inquiries', [InquiryController::class, 'store']);

        // Milestone 20 — Online Applications, visitor-facing half. Bound via
        // application_number (never the internal id) — see Application's
        // migration docblock and ApplicationController's class docblock for
        // the email-match "ownership" check every mutating action re-checks.
        Route::post('applications', [ApplicationController::class, 'store']);
        Route::post('applications/lookup', [ApplicationController::class, 'lookup']);
        Route::put('applications/{application:application_number}', [ApplicationController::class, 'update']);
        Route::post('applications/{application:application_number}/documents', [ApplicationController::class, 'uploadDocument']);
        Route::delete('applications/{application:application_number}/documents/{mediaId}', [ApplicationController::class, 'deleteDocument']);
        Route::post('applications/{application:application_number}/submit', [ApplicationController::class, 'submit']);

        // Milestone 24 — Dashboard Analytics' page-view ping (see PageView's
        // migration docblock).
        Route::post('analytics/pageview', [AnalyticsController::class, 'trackPageView']);
    });

    // Audit fix (Critical remediation) — deliberately outside the
    // 'public-write' group above: this is a read (GET), and staff
    // reviewing an application with several documents need more than
    // 10/min. Serves both the applicant (?email= ownership check) and
    // staff (Bearer token) — see ApplicationController::downloadDocument()'s
    // docblock.
    Route::get('applications/{application:application_number}/documents/{mediaId}/download', [ApplicationController::class, 'downloadDocument']);

});
