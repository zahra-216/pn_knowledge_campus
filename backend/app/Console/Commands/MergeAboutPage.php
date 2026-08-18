<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off migration command: consolidates the standalone Vision, Mission,
 * and Chairman's Message pages (plus a newly drafted Manager's Message)
 * into a single About page, and archives the now-redundant standalone
 * pages so their old URLs 404 instead of showing duplicate content.
 *
 * Usage: php artisan about:merge {managerMediaId}
 */
class MergeAboutPage extends Command
{
    protected $signature = 'about:merge {managerMediaId : Media Library ID of the manager photo}';
    protected $description = "Merge Vision/Mission/Chairman's/Manager's Message into the About page";

    public function handle(): int
    {
        $managerMediaId = (int) $this->argument('managerMediaId');

        DB::transaction(function () use ($managerMediaId) {
            $about = Page::where('slug', 'about')->firstOrFail();

            // Wipe the About page's current blocks (hero + intro rich_text)
            // and rebuild the full ordered set from scratch — safer than
            // patching, since order values must be contiguous.
            $about->blocks()->delete();

            $blocks = [
                [
                    'block_type' => 'hero',
                    'data' => [
                        'heading' => 'About PNK Global Campus',
                        'subheading' => 'Building futures through knowledge, character, and community.',
                        'alignment' => 'center',
                    ],
                ],
                [
                    'block_type' => 'rich_text',
                    'data' => [
                        'body' => '<p>PNK Global Campus has served students and the wider community for years, combining rigorous academics with a supportive campus life.</p>',
                    ],
                ],
                [
                    'block_type' => 'text',
                    'data' => [
                        'body' => "Our Vision\n\nTo be a globally recognized center of academic excellence, innovation, and character development.",
                    ],
                ],
                [
                    'block_type' => 'text',
                    'data' => [
                        'body' => "Our Mission\n\nTo provide accessible, high-quality education that empowers students to lead and serve with integrity.",
                    ],
                ],
                [
                    'block_type' => 'chairman_message',
                    'data' => [
                        'heading' => 'A Message from Our Chairman',
                        'name' => 'A.G Prem Nawath',
                        'role' => 'Chairman, PNK Global Campus',
                        'message' => "Let me welcome you to our website!\n\nAs we welcome our future leaders and productive citizens of the world, I want to highlight our mission of shaping young minds and hearts as the foundation for a nation's progress.\n\nAs a well-established institution, we uphold a high academic standard paired with strong discipline, helping us achieve consistently excellent results. We believe education is the key to a nation's progress, and it is our duty to give every student the best possible academic training to succeed in whatever career path they choose.\n\nWe are committed to supporting you in achieving your goals and ensuring your time here is meaningful and rewarding, with personal attention and care for every member of our campus community.\n\nI wish you all the very best!",
                        'media_id' => 7,
                    ],
                ],
                [
                    'block_type' => 'chairman_message',
                    'data' => [
                        'heading' => 'A Message from Our Manager',
                        'name' => 'R. Rahupathy',
                        'role' => 'Manager, PNK Global Campus',
                        'message' => "Welcome to PNK Global Campus!\n\nAs the Manager of this campus, I take pride in ensuring that every student's daily experience here reflects our commitment to quality education and genuine care. Behind every lecture, every event, and every service on campus is a team working to make your time here smooth, supportive, and productive.\n\nWe believe that a well-run campus is the foundation for real learning — that means responsive administration, accessible faculty, and a safe, organized environment where students can focus on what matters most: growing academically and personally.\n\nMy door is always open to student concerns and ideas. We are continuously working to improve our facilities and services, and your feedback plays a real part in that process.\n\nI look forward to supporting you throughout your journey with us.",
                        'media_id' => $managerMediaId,
                    ],
                ],
            ];

            foreach ($blocks as $order => $block) {
                $about->blocks()->create([
                    'block_type' => $block['block_type'],
                    'data' => $block['data'],
                    'order' => $order,
                    'is_active' => true,
                ]);
            }

            // Archive the now-redundant standalone pages so their old URLs
            // (/vision, /mission, /chairmans-message, /student-life) 404
            // via StaticPage's existing error?.status === 404 handling,
            // instead of showing stale duplicate content.
            Page::whereIn('slug', ['vision', 'mission', 'chairmans-message', 'student-life'])
                ->update(['status' => 'archived']);

            $this->info('About page rebuilt with Vision, Mission, Chairman\'s Message, and Manager\'s Message.');
            $this->info('Vision, Mission, Chairman\'s Message, and Student Life pages archived.');
        });

        return self::SUCCESS;
    }
}