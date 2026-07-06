<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * FR-37 — an event set to "scheduled" goes live automatically once its
 * published_at time arrives. Audit fix: Event previously had no
 * published_at column at all (see the column's own migration docblock
 * for why one now exists, distinct from `starts_at`) — routes/console.php
 * now runs this every minute, same as PublishScheduledPages/BlogPosts/
 * News/Courses.
 */
class PublishScheduledEvents extends Command
{
    protected $signature = 'events:publish-scheduled';

    protected $description = 'Flip scheduled events to published once their published_at time has arrived (FR-37).';

    public function handle(): int
    {
        $count = Event::where('status', 'scheduled')
            ->where('published_at', '<=', Carbon::now())
            ->update(['status' => 'published']);

        if ($count > 0) {
            $this->info("Published {$count} scheduled event(s).");
        }

        return self::SUCCESS;
    }
}
