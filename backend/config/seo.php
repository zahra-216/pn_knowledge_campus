<?php

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Department;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\News;
use App\Models\Page;

/**
 * Allow-list of valid `{type}` values for SeoMetaController's
 * /admin/seo/{type}/{id} routes (API Design, Section 8.8), mapping a
 * short URL-safe key to the model class it resolves to.
 *
 * 'faculty', 'department', 'course', 'course-category', 'blog', 'news',
 * 'event', and 'page' are real entries now (Page Builder's own milestone
 * deliberately deferred registering 'page' since SEO wasn't part of that
 * request — the Public Website milestone needs it, since static pages
 * are public content like everything else here). 'course-category' is
 * the only other Resource that doesn't embed `seo` publicly (it has no
 * public detail page of its own). Gallery Albums is permanently excluded
 * — the Database Design document explicitly does not list it as a
 * seo_meta consumer — so SeoMetaTest uses 'gallery-album' as its example
 * of a deliberately-unregistered type.
 */
return [
    'seoable_types' => [
        'faculty' => Faculty::class,
        'department' => Department::class,
        'course' => Course::class,
        'course-category' => CourseCategory::class,
        'blog' => BlogPost::class,
        'news' => News::class,
        'event' => Event::class,
        'page' => Page::class,
    ],
];
