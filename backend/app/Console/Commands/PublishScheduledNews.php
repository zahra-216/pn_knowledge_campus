<?php

namespace App\Console\Commands;

use App\Models\News;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * FR-37 — a news article set to "scheduled" goes live automatically once
 * its published_at time arrives. routes/console.php runs this every
 * minute, same as PublishScheduledBlogPosts/PublishScheduledPages.
 */
class PublishScheduledNews extends Command
{
    protected $signature = 'news:publish-scheduled';

    protected $description = 'Flip scheduled news articles to published once their published_at time has arrived (FR-37).';

    public function handle(): int
    {
        $count = News::where('status', 'scheduled')
            ->where('published_at', '<=', Carbon::now())
            ->update(['status' => 'published']);

        if ($count > 0) {
            $this->info("Published {$count} scheduled news article(s).");
        }

        return self::SUCCESS;
    }
}
