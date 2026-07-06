<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * FR-37 — a page set to "scheduled" goes live automatically once its
 * published_at time arrives, without an admin manually clicking Publish.
 * routes/console.php runs this every minute.
 */
class PublishScheduledPages extends Command
{
    protected $signature = 'pages:publish-scheduled';

    protected $description = 'Flip scheduled pages to published once their published_at time has arrived (FR-37).';

    public function handle(): int
    {
        $count = Page::where('status', 'scheduled')
            ->where('published_at', '<=', Carbon::now())
            ->update(['status' => 'published']);

        if ($count > 0) {
            $this->info("Published {$count} scheduled page(s).");
        }

        return self::SUCCESS;
    }
}
