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

// Audit fix (Critical remediation) — the SRS explicitly gates go-live on
// "automated daily database backups and a documented restore procedure"
// (Roadmap Stage 10); this project had neither. `backup:run` dumps the
// database plus storage/app/public and storage/app/private (see
// config/backup.php's docblock for why not the whole codebase — git
// covers that now); `backup:clean` prunes old ones per the retention
// policy in config/backup.php's 'cleanup' section; `backup:monitor`
// fails loudly (and emails, per config/backup.php's 'notifications')
// if a backup goes missing or the destination disk fills up, so a
// silently-broken backup pipeline doesn't go unnoticed for months. See
// DEPLOYMENT.md's "Backups & Restore" section for the restore steps.
Schedule::command('backup:clean')->daily()->at('01:30');
Schedule::command('backup:run')->daily()->at('02:00');
Schedule::command('backup:monitor')->daily()->at('03:00');
