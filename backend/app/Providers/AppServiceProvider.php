<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseCurriculumItem;
use App\Models\Department;
use App\Models\Download;
use App\Models\DownloadCategory;
use App\Models\Event;
use App\Models\EventSpeaker;
use App\Models\Faculty;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\GalleryAlbum;
use App\Models\HeroSlide;
use App\Models\HomepageSection;
use App\Models\Inquiry;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\MediaLibrary;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Partner;
use App\Models\PartnerCategory;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Models\User;
use App\Policies\ApplicationPolicy;
use App\Policies\BlogPolicy;
use App\Policies\CoursePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DownloadPolicy;
use App\Policies\EventPolicy;
use App\Policies\FacultyPolicy;
use App\Policies\FaqPolicy;
use App\Policies\GalleryAlbumPolicy;
use App\Policies\HeroSlidePolicy;
use App\Policies\HomepageSectionPolicy;
use App\Policies\InquiryPolicy;
use App\Policies\MediaPolicy;
use App\Policies\MenuPolicy;
use App\Policies\NewsPolicy;
use App\Policies\PagePolicy;
use App\Policies\PartnerPolicy;
use App\Policies\SettingPolicy;
use App\Policies\TestimonialPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Explicit morph map: every polymorphic relation added in later
        // milestones (media.model_type, seo_meta.seoable_type, etc. — see
        // Database Design, Section 2.4) resolves through short aliases
        // instead of raw class strings, so renaming an App\Models
        // namespace later never corrupts existing polymorphic rows.
        Relation::enforceMorphMap([
            'user' => User::class,
            'media_library' => MediaLibrary::class,
            'page' => Page::class,
            'hero_slide' => HeroSlide::class,
            'testimonial' => Testimonial::class,
            'partner' => Partner::class,
            'faculty' => Faculty::class,
            'department' => Department::class,
            'course' => Course::class,
            'course_category' => CourseCategory::class,
            'blog_post' => BlogPost::class,
            'news' => News::class,
            'event' => Event::class,
            'event_speaker' => EventSpeaker::class,
            'gallery_album' => GalleryAlbum::class,
            'download' => Download::class,
            'application' => Application::class,
        ]);

        // Milestone 1 policies (Development Roadmap — "Policies: 2").
        // MediaPolicy governs both Media and MediaFolder since the SRS
        // Permission Matrix has one "Media Library" row covering both.
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(MediaFolder::class, MediaPolicy::class);

        // Menu Builder — MenuPolicy governs both Menu and MenuItem (one
        // "Menu Builder" row in the SRS Permission Matrix).
        Gate::policy(Menu::class, MenuPolicy::class);
        Gate::policy(MenuItem::class, MenuPolicy::class);

        // Page Builder — PagePolicy governs both Page and PageBlock (one
        // "Page Builder" row in the SRS Permission Matrix).
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(PageBlock::class, PagePolicy::class);

        // Homepage Builder — separate policies per the SRS Permission
        // Matrix, which gives Hero Slider/Testimonials/Partners each
        // their own row with a different Content Editor/Marketing split
        // than Homepage Builder's section-composition row.
        Gate::policy(HomepageSection::class, HomepageSectionPolicy::class);
        Gate::policy(HeroSlide::class, HeroSlidePolicy::class);
        Gate::policy(Testimonial::class, TestimonialPolicy::class);
        // PartnerPolicy also governs PartnerCategory (Milestone 16) —
        // same reasoning as BlogPolicy also governing BlogCategory below.
        Gate::policy(Partner::class, PartnerPolicy::class);
        Gate::policy(PartnerCategory::class, PartnerPolicy::class);

        // Academic Structure — Faculty/Department/Course Management.
        // CoursePolicy also governs CourseCurriculumItem and Faq (the
        // course-scoped sub-resources) — one "Course Management" row in
        // the SRS Permission Matrix covers all three.
        Gate::policy(Faculty::class, FacultyPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(CourseCurriculumItem::class, CoursePolicy::class);
        Gate::policy(Faq::class, CoursePolicy::class);

        // Engagement Content — Blog. BlogPolicy also governs BlogCategory
        // and Tag (one "Blog" row in the SRS Permission Matrix); Tag is
        // shared infrastructure with a future News module, revisit this
        // gate if News needs to manage tags independently of blog.*.
        Gate::policy(BlogPost::class, BlogPolicy::class);
        Gate::policy(BlogCategory::class, BlogPolicy::class);
        Gate::policy(Tag::class, BlogPolicy::class);

        // Engagement Content — News. NewsPolicy also governs
        // NewsCategory (one "News" row in the SRS Permission Matrix).
        Gate::policy(News::class, NewsPolicy::class);
        Gate::policy(NewsCategory::class, NewsPolicy::class);

        // Engagement Content — Events. EventPolicy also governs
        // EventSpeaker (one "Events" row in the SRS Permission Matrix).
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(EventSpeaker::class, EventPolicy::class);

        // Engagement Content — Gallery. No SEO integration — Gallery
        // Albums is deliberately excluded from config('seo.seoable_types')
        // (see the migration's docblock).
        Gate::policy(GalleryAlbum::class, GalleryAlbumPolicy::class);

        // Engagement Content — FAQ (Milestone 17). Governs FaqCategory
        // only — the global Faq model's own actions are checked via raw
        // faq.* ability strings in FaqController, since Faq::class is
        // already bound to CoursePolicy above (see FaqPolicy's docblock).
        Gate::policy(FaqCategory::class, FaqPolicy::class);

        // Downloads (Milestone 18) — the standalone catalog module, not
        // Course's own `downloads` media collection. DownloadPolicy
        // governs both Download and DownloadCategory (one "Downloads"
        // row in the SRS Permission Matrix); no cross-model collision
        // like Faq's, since Download is a brand-new model.
        Gate::policy(Download::class, DownloadPolicy::class);
        Gate::policy(DownloadCategory::class, DownloadPolicy::class);

        // Online Applications (Milestone 20) — Admissions' own module,
        // gated by applications.* (see ApplicationPolicy's docblock).
        Gate::policy(Application::class, ApplicationPolicy::class);

        // Inquiry Management admin inbox — gated by inquiries.* (see
        // InquiryPolicy's docblock).
        Gate::policy(Inquiry::class, InquiryPolicy::class);

        // Users, Roles & Permissions — gated by users.* (see
        // UserPolicy's docblock). Role/Permission management itself
        // uses raw roles.* ability strings directly in RoleController
        // rather than a Gate::policy binding — see that controller's
        // own docblock for why.
        Gate::policy(User::class, UserPolicy::class);

        // bootstrap/app.php's throttleApi() routes the `api` middleware
        // group through a limiter named "api" — Laravel expects that
        // limiter registered here (it's not automatic).
        //
        // Audit fix (Critical remediation) — raised from 60/min. A single
        // public page load legitimately fires 8-10 distinct GET requests
        // (settings, header/footer menus, social links, the page's own
        // content, plus the header's live-synced mega-menu lookups on
        // first mount); at 60/min, browsing more than 5-6 pages inside a
        // minute exhausted the budget and every page after that showed a
        // raw "Too Many Attempts" error to a completely ordinary visitor,
        // reproduced live during the Phase 1 audit. 180/min still caps
        // genuine abuse while giving real headroom for real browsing —
        // paired with deduping the settings fetch (usePublicSettings is
        // now a single shared context instead of 4-5 independent calls).
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(180)->by($request->user()?->id ?: $request->ip());
        });

        // Milestone 25 (Security Review) — the general 60/minute API
        // limiter above is far too generous for a credential-guessing
        // attack against /auth/login: at 60/minute an attacker gets
        // 86,400 guesses/day per IP. This limiter is keyed by IP *and*
        // the submitted email together, so it throttles repeated
        // guesses against one account without also locking out every
        // other student sharing the same campus/NAT IP.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip().'|'.strtolower((string) $request->input('email')));
        });

        // Audit fix — the general 'api' limiter (60/min) is too generous
        // for unauthenticated write endpoints that each cost real money
        // (a queued notification email) or fill an admin inbox with junk
        // (inquiries, draft applications, page-view pings). 10/min/IP is
        // enough for a genuine visitor submitting a form (or even
        // resubmitting after a validation error) while meaningfully
        // capping automated abuse.
        RateLimiter::for('public-write', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Audit fix (High remediation) — analytics/pageview was previously
        // grouped under 'public-write' (10/min) alongside Inquiry/
        // Application submissions. It fires on every single page
        // navigation, so a visitor who'd merely browsed a few pages could
        // exhaust the same 10/min budget a genuine applicant needed for
        // their multi-step Apply flow, reproduced live during the Phase 1
        // audit (a document upload mid-application was rejected as
        // "Too Many Attempts"). A page-view ping carries no cost worth
        // protecting the way an emailed inquiry or a file upload does, so
        // it gets its own generous limiter instead of competing with them.
        RateLimiter::for('page-view', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        // Audit fix (Low remediation) — password rules previously only
        // checked length (min:10). One shared default policy means every
        // place a password gets set (user create/update, password reset)
        // enforces the same bar instead of drifting apart. `uncompromised()`
        // is deliberately omitted — it calls an external API (Have I Been
        // Pwned) during validation, which would make user creation depend
        // on outbound internet access and add flakiness to tests/CI.
        Password::defaults(fn () => Password::min(10)->mixedCase()->numbers()->symbols());

        // Production HTTPS enforcement — forces every URL this app
        // generates (password reset links, Storage::url() for the
        // public disk, etc.) to https:// even if the app itself sits
        // behind a TLS-terminating reverse proxy/load balancer that
        // talks plain HTTP to it internally (see bootstrap/app.php's
        // trustProxies() call, which is what makes $request->isSecure()
        // return true in that setup in the first place).
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
