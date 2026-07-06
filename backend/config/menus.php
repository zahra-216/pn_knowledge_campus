<?php

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Department;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\Page;

/**
 * Allow-list of valid `linkable_type` values for menu items linking to
 * internal content (API Design's polymorphic linkable_type/linkable_id
 * pattern for menu_items). Same approach as config('seo.seoable_types').
 *
 * Each entry needs two things once a real content model ships:
 *   1. A `resolve_url` closure here, turning a linkable_id into a real
 *      public path (e.g. fn ($id) => '/courses/'.Course::find($id)->slug).
 *   2. A matching Relation::morphMap() entry in AppServiceProvider —
 *      without it, MenuItem::linkable() throws ClassMorphViolationException
 *      the same way Media::move() does.
 *
 * 'page' was the only entry through the CMS-only milestones — every
 * other content type existed but was never wired here since nothing
 * consumed a menu item's resolved URL yet. The Public Website milestone
 * is the first real consumer, so every model that now has a public
 * detail page gets registered. Paths match the public route table
 * exactly (see PublicRoutes.tsx).
 */
return [
    'linkable_types' => [
        'page' => [
            'resolve_url' => fn (int $id) => '/'.(Page::find($id)?->slug ?? ''),
        ],
        'faculty' => [
            'resolve_url' => fn (int $id) => '/faculties/'.(Faculty::find($id)?->slug ?? ''),
        ],
        'department' => [
            'resolve_url' => fn (int $id) => '/departments/'.(Department::find($id)?->slug ?? ''),
        ],
        'course' => [
            'resolve_url' => fn (int $id) => '/courses/'.(Course::find($id)?->slug ?? ''),
        ],
        'blog_post' => [
            'resolve_url' => fn (int $id) => '/blog/'.(BlogPost::find($id)?->slug ?? ''),
        ],
        'news' => [
            'resolve_url' => fn (int $id) => '/news/'.(News::find($id)?->slug ?? ''),
        ],
        'event' => [
            'resolve_url' => fn (int $id) => '/events/'.(Event::find($id)?->slug ?? ''),
        ],
        'gallery_album' => [
            'resolve_url' => fn (int $id) => '/gallery/'.(GalleryAlbum::find($id)?->slug ?? ''),
        ],
    ],
];
