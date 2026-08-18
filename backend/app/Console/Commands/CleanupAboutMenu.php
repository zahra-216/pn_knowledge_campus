<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Console\Command;

/**
 * One-off migration command: removes Vision, Mission, Chairman's Message,
 * and Student Life as children of the header's "About" menu item, now
 * that their content lives on the single About page. "About" becomes a
 * plain link with no dropdown/chevron.
 *
 * Usage: php artisan about:cleanup-menu
 */
class CleanupAboutMenu extends Command
{
    protected $signature = 'about:cleanup-menu';
    protected $description = "Remove Vision/Mission/Chairman's Message/Student Life from the About dropdown";

    public function handle(): int
    {
        $header = Menu::where('name', 'header')->firstOrFail();

        $about = MenuItem::where('menu_id', $header->id)
            ->whereNull('parent_id')
            ->where('label', 'About')
            ->firstOrFail();

        $removed = MenuItem::where('parent_id', $about->id)->delete();

        $this->info("Removed {$removed} child menu item(s) from About.");

        return self::SUCCESS;
    }
}