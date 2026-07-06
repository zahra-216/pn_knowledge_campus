<?php

use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\PublishScheduledBlogPosts;
use App\Console\Commands\PublishScheduledCourses;
use App\Console\Commands\PublishScheduledEvents;
use App\Console\Commands\PublishScheduledNews;
use App\Console\Commands\PublishScheduledPages;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| The SRS (FR-37) requires scheduled publishing (a Course/Page/News/Blog/
| Event item set to "scheduled" goes live automatically at its
| published_at time). Page Builder was the first content model to ship
| this; Blog, News, Courses, and Events each register their own
| Schedule::command(...) call the same way (Courses/Events were a
| pre-existing gap — both already had/now have a published_at column and
| a selectable "scheduled" status with nothing to flip it, fixed here).
*/
Schedule::command(PublishScheduledPages::class)->everyMinute();
Schedule::command(PublishScheduledBlogPosts::class)->everyMinute();
Schedule::command(PublishScheduledNews::class)->everyMinute();
Schedule::command(PublishScheduledCourses::class)->everyMinute();
Schedule::command(PublishScheduledEvents::class)->everyMinute();

// Milestone 22 (SEO Module) — regenerates sitemap.xml/robots.txt daily
// so newly published/unpublished content is reflected without needing
// a manual `php artisan sitemap:generate` run after every change.
Schedule::command(GenerateSitemap::class)->daily();
