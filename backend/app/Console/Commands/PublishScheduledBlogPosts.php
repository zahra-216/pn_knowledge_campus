<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * FR-37 — a blog post set to "scheduled" goes live automatically once
 * its published_at time arrives, without an admin manually clicking
 * Publish. routes/console.php runs this every minute, same as
 * PublishScheduledPages.
 */
class PublishScheduledBlogPosts extends Command
{
    protected $signature = 'blog:publish-scheduled';

    protected $description = 'Flip scheduled blog posts to published once their published_at time has arrived (FR-37).';

    public function handle(): int
    {
        $count = BlogPost::where('status', 'scheduled')
            ->where('published_at', '<=', Carbon::now())
            ->update(['status' => 'published']);

        if ($count > 0) {
            $this->info("Published {$count} scheduled blog post(s).");
        }

        return self::SUCCESS;
    }
}
