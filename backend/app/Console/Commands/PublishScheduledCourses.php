<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * FR-37 — a course set to "scheduled" goes live automatically once its
 * published_at time arrives. Audit fix: the Course model has always had
 * a `published_at` column and a `scheduled` status value the admin
 * editor could select, but no command ever flipped it to `published` —
 * routes/console.php now runs this every minute, same as
 * PublishScheduledPages/BlogPosts/News.
 */
class PublishScheduledCourses extends Command
{
    protected $signature = 'courses:publish-scheduled';

    protected $description = 'Flip scheduled courses to published once their published_at time has arrived (FR-37).';

    public function handle(): int
    {
        $count = Course::where('status', 'scheduled')
            ->where('published_at', '<=', Carbon::now())
            ->update(['status' => 'published']);

        if ($count > 0) {
            $this->info("Published {$count} scheduled course(s).");
        }

        return self::SUCCESS;
    }
}
