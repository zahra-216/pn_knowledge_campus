<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds the five baseline roles from the SRS Permission Matrix (Section
 * 10 of the SRS) and the permission-key set for every module shipped so
 * far (auth/users from Milestone 0; settings/media/seo from Milestone 1
 * — see the Development Roadmap's M1 objectives). Module-specific
 * permission keys for later modules (courses.*, news.*, etc.) are added
 * by each later milestone's own edit to this seeder as that module
 * ships, per the Roadmap's "finish one module before the next" principle.
 *
 * Permission naming convention: {module}.{action}
 * Actions used throughout the project: view, create, edit, delete, publish.
 *
 * Milestone 1 additions, per the SRS Permission Matrix (Section 10):
 *   - Settings: Super Admin only — even Administrator gets nothing. This
 *     also gates Branches and Social Links, which the SRS (Section 7.4)
 *     treats as sub-parts of the Settings module rather than modules of
 *     their own.
 *   - Media Library: Super Admin/Administrator = Full; Content
 *     Editor/Marketing = Create/Edit (no delete); Admissions = View only.
 *   - SEO Manager: Super Admin/Administrator = Full; Content
 *     Editor/Marketing = Create/Edit; Admissions = no access.
 *   - Menu Builder: Super Admin/Administrator = Full; every other role =
 *     no access at all (unlike Media/SEO, Content Editor and Marketing
 *     get nothing here either).
 *
 * Page Builder addition, per the SRS Permission Matrix (Section 10):
 *   - Super Admin/Administrator = Full (including delete/publish, since
 *     the matrix restricts delete to those two roles across every
 *     content module, and Phase 1's publish workflow keeps final publish
 *     as an Administrator/Super Admin action per Section 5).
 *   - Content Editor = Create/Edit only.
 *   - Marketing = View only.
 *   - Admissions = no access, even though Section 5's narrative mentions
 *     Admissions owning "Scholarships/How-to-Apply page content" — the
 *     Permission Matrix is the binding spec where the two disagree (the
 *     SRS says so explicitly), same precedent already applied to Menu
 *     Builder's SEO/API Design conflicts.
 *
 * Homepage Builder addition, per the SRS Permission Matrix (Section 10)
 * — four separate rows, each with a different split:
 *   - Homepage Builder (section enable/reorder): Super Admin/Administrator
 *     = Full; Marketing = Create/Edit (mapped to homepage.edit — sections
 *     are a fixed seeded set, nothing to create); Content Editor/
 *     Admissions = no access.
 *   - Hero Slider: Super Admin/Administrator = Full; Marketing =
 *     Create/Edit (no delete); Content Editor/Admissions = no access.
 *   - Testimonials: Super Admin/Administrator = Full; Content
 *     Editor/Marketing = Create/Edit (no delete); Admissions = no access.
 *   - Partners: Super Admin/Administrator = Full; Marketing = Create/Edit
 *     (no delete); Content Editor/Admissions = no access.
 *
 * Faculty Management addition, per the SRS Permission Matrix (Section
 * 10): Super Admin/Administrator = Full (incl. delete); Content Editor =
 * Create/Edit; Marketing = View; Admissions = View (Faculty is the one
 * module besides Course/Department where Admissions gets read access,
 * matching its enrollment-funnel focus).
 *
 * Department Management addition, per the SRS Permission Matrix (Section
 * 10): identical split to Faculty Management — Super Admin/Administrator
 * = Full; Content Editor = Create/Edit; Marketing/Admissions = View.
 *
 * Course Management addition, per the SRS Permission Matrix (Section
 * 10): Super Admin/Administrator = Full (incl. delete/publish); Content
 * Editor = Create/Edit; Marketing = View; Admissions = Create/Edit — the
 * one module besides Faculty/Department where Admissions gets more than
 * read access, matching its enrollment-funnel focus (Section 5's
 * narrative). Covers courses.*, and also gates the course-scoped
 * curriculum/FAQ sub-resources and the course_levels/course_modes/
 * course_categories lookup tables (treated as Course Management
 * sub-parts, same reasoning already applied to Branches/Social Links
 * under Settings).
 *
 * Blog addition, per the SRS Permission Matrix (Section 10): Super
 * Admin/Administrator = Full (incl. delete/publish); Content Editor =
 * Create/Edit; Marketing = Create/Edit (Blog is one of Marketing's
 * outward-facing/promotional modules per Section 5's narrative, unlike
 * Faculty/Course where Marketing is view-only); Admissions = no access
 * at all. Covers blog.*, and also gates the blog_categories and shared
 * tags lookup tables (Blog Management sub-parts, same reasoning as
 * course_levels/course_modes/course_categories under courses.*).
 *
 * News addition, per the SRS Permission Matrix (Section 10): identical
 * split to Blog's own row — Super Admin/Administrator = Full (incl.
 * delete/publish); Content Editor = Create/Edit; Marketing = Create/Edit;
 * Admissions = no access. Covers news.*, and also gates news_categories
 * (a News Management sub-part, same reasoning as blog_categories under
 * blog.*).
 *
 * Events addition, per the SRS Permission Matrix (Section 10): same
 * Content Editor/Marketing = Create/Edit, Admissions = no access split
 * as Blog/News, but no events.publish permission — the API Design
 * doesn't document a separate publish endpoint for Events, so there's no
 * separate "final sign-off" ability to gate (see EventPolicy's
 * docblock). Covers events.*, and also gates the event-scoped speakers
 * sub-resource.
 *
 * Gallery addition, per the SRS Permission Matrix (Section 10): identical
 * split to Events' own row — Content Editor/Marketing = Create/Edit,
 * Admissions = no access, no gallery.publish permission (no publish
 * endpoint documented for Gallery Albums either; visibility is just the
 * is_active flag via the regular update endpoint).
 *
 * FAQ addition (Milestone 17): identical split to Testimonials/Gallery —
 * Content Editor/Marketing = Create/Edit, Admissions = no access. Covers
 * both the global Site FAQ (FaqController) and FaqCategory; the
 * pre-existing course-scoped FAQ sub-resource keeps using courses.*
 * (CoursePolicy), unchanged.
 *
 * Downloads addition (Milestone 18): identical split to FAQ/Testimonials/
 * Gallery — Content Editor/Marketing = Create/Edit, Admissions = no
 * access. Covers both Download and DownloadCategory. Distinct from
 * Course's own `downloads` media collection, which has no permission of
 * its own — it's just part of courses.edit.
 *
 * Applications addition (Milestone 20): unlike every module above,
 * Content Editor/Marketing get NOTHING here (applications carry
 * applicant PII, not editorial content) while Admissions gets full
 * access (view/review/export) — its first meaningfully elevated module
 * beyond Course Management (see CoursePolicy's own docblock on why
 * Admissions is already a special case there).
 *
 * Inquiry Management addition (per the SRS Permission Matrix, Section
 * 10's "Inquiry Management" row): Super Admin/Administrator/Admissions
 * = Full (view/manage/export — "manage" covers status changes and
 * deleting spam, mirroring Application's "review" action); Marketing =
 * View only; Content Editor = no access.
 *
 * Users, Roles & Permissions addition (SRS Permission Matrix, Section
 * 10's "Users, Roles & Permissions" row): Super Admin only — every
 * other role gets nothing at all, stricter even than Settings (which is
 * at least Super-Admin-viewable-only-by-Super-Admin the same way).
 * `users.*`/`roles.*` were already declared in the permission list from
 * Milestone 0's RBAC scaffold but never assigned to any role until this
 * module's own controllers existed to check them — Super Admin's
 * `syncPermissions(Permission::all())` call already grants them, so no
 * change is needed there.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'dashboard.view',
            'settings.view', 'settings.edit',
            'media.view', 'media.create', 'media.edit', 'media.delete',
            'seo.view', 'seo.edit',
            'menus.view', 'menus.edit',
            'pages.view', 'pages.create', 'pages.edit', 'pages.delete', 'pages.publish',
            'homepage.view', 'homepage.edit',
            'hero_slides.view', 'hero_slides.create', 'hero_slides.edit', 'hero_slides.delete',
            'testimonials.view', 'testimonials.create', 'testimonials.edit', 'testimonials.delete',
            'partners.view', 'partners.create', 'partners.edit', 'partners.delete',
            'faculties.view', 'faculties.create', 'faculties.edit', 'faculties.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'courses.view', 'courses.create', 'courses.edit', 'courses.delete', 'courses.publish',
            'blog.view', 'blog.create', 'blog.edit', 'blog.delete', 'blog.publish',
            'news.view', 'news.create', 'news.edit', 'news.delete', 'news.publish',
            'events.view', 'events.create', 'events.edit', 'events.delete',
            'gallery.view', 'gallery.create', 'gallery.edit', 'gallery.delete',
            'faq.view', 'faq.create', 'faq.edit', 'faq.delete',
            'downloads.view', 'downloads.create', 'downloads.edit', 'downloads.delete',
            'applications.view', 'applications.review', 'applications.export',
            'inquiries.view', 'inquiries.manage', 'inquiries.export',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'sanctum');
        }

        $superAdmin = Role::findOrCreate('Super Admin', 'sanctum');
        $superAdmin->syncPermissions(Permission::all());

        $administrator = Role::findOrCreate('Administrator', 'sanctum');
        $administrator->syncPermissions([
            'dashboard.view',
            'media.view', 'media.create', 'media.edit', 'media.delete',
            'seo.view', 'seo.edit',
            'menus.view', 'menus.edit',
            'pages.view', 'pages.create', 'pages.edit', 'pages.delete', 'pages.publish',
            'homepage.view', 'homepage.edit',
            'hero_slides.view', 'hero_slides.create', 'hero_slides.edit', 'hero_slides.delete',
            'testimonials.view', 'testimonials.create', 'testimonials.edit', 'testimonials.delete',
            'partners.view', 'partners.create', 'partners.edit', 'partners.delete',
            'faculties.view', 'faculties.create', 'faculties.edit', 'faculties.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'courses.view', 'courses.create', 'courses.edit', 'courses.delete', 'courses.publish',
            'blog.view', 'blog.create', 'blog.edit', 'blog.delete', 'blog.publish',
            'news.view', 'news.create', 'news.edit', 'news.delete', 'news.publish',
            'events.view', 'events.create', 'events.edit', 'events.delete',
            'gallery.view', 'gallery.create', 'gallery.edit', 'gallery.delete',
            'faq.view', 'faq.create', 'faq.edit', 'faq.delete',
            'downloads.view', 'downloads.create', 'downloads.edit', 'downloads.delete',
            'applications.view', 'applications.review', 'applications.export',
            'inquiries.view', 'inquiries.manage', 'inquiries.export',
        ]);

        $contentEditor = Role::findOrCreate('Content Editor', 'sanctum');
        $contentEditor->syncPermissions([
            'dashboard.view',
            'media.view', 'media.create', 'media.edit',
            'seo.view', 'seo.edit',
            'pages.view', 'pages.create', 'pages.edit',
            'testimonials.view', 'testimonials.create', 'testimonials.edit',
            'faculties.view', 'faculties.create', 'faculties.edit',
            'departments.view', 'departments.create', 'departments.edit',
            'courses.view', 'courses.create', 'courses.edit',
            'blog.view', 'blog.create', 'blog.edit',
            'news.view', 'news.create', 'news.edit',
            'events.view', 'events.create', 'events.edit',
            'gallery.view', 'gallery.create', 'gallery.edit',
            'faq.view', 'faq.create', 'faq.edit',
            'downloads.view', 'downloads.create', 'downloads.edit',
        ]);

        $marketing = Role::findOrCreate('Marketing', 'sanctum');
        $marketing->syncPermissions([
            'dashboard.view',
            'media.view', 'media.create', 'media.edit',
            'seo.view', 'seo.edit',
            'pages.view',
            'homepage.view', 'homepage.edit',
            'hero_slides.view', 'hero_slides.create', 'hero_slides.edit',
            'testimonials.view', 'testimonials.create', 'testimonials.edit',
            'partners.view', 'partners.create', 'partners.edit',
            'faculties.view',
            'departments.view',
            'courses.view',
            'blog.view', 'blog.create', 'blog.edit',
            'news.view', 'news.create', 'news.edit',
            'events.view', 'events.create', 'events.edit',
            'gallery.view', 'gallery.create', 'gallery.edit',
            // FAQ is View-only for Marketing per the SRS Permission Matrix
            // (Section 10's "FAQ" row) — Marketing was previously granted
            // faq.create/faq.edit, which was over-permissioned relative to
            // the binding matrix. See Admissions below for the role that
            // actually gets Create/Edit here.
            'faq.view',
            'downloads.view', 'downloads.create', 'downloads.edit',
            'inquiries.view',
        ]);

        $admissions = Role::findOrCreate('Admissions', 'sanctum');
        $admissions->syncPermissions([
            'dashboard.view',
            'media.view',
            'faculties.view',
            'departments.view',
            'courses.view', 'courses.create', 'courses.edit',
            // FAQ and Downloads: Create/Edit per the SRS Permission Matrix
            // ("FAQ" and "Downloads" rows) — both were previously missing
            // entirely for Admissions despite the matrix explicitly giving
            // it Create/Edit access to each (part of its enrollment-funnel
            // focus alongside Course Management and Applications).
            'faq.view', 'faq.create', 'faq.edit',
            'downloads.view', 'downloads.create', 'downloads.edit',
            'applications.view', 'applications.review', 'applications.export',
            'inquiries.view', 'inquiries.manage', 'inquiries.export',
        ]);
    }
}
