<?php

namespace Database\Seeders;

use App\Models\DownloadCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds the starting Downloads catalog categories (Milestone 18) so the
 * Downloads admin screen has real groups to assign on first login.
 */
class DownloadCategorySeeder extends Seeder
{
    public function run(): void
    {
        DownloadCategory::firstOrCreate(['name' => 'Prospectus'], ['slug' => 'prospectus', 'order' => 0]);
        DownloadCategory::firstOrCreate(['name' => 'Forms'], ['slug' => 'forms', 'order' => 1]);
        DownloadCategory::firstOrCreate(['name' => 'Brochures'], ['slug' => 'brochures', 'order' => 2]);
        DownloadCategory::firstOrCreate(['name' => 'PDFs'], ['slug' => 'pdfs', 'order' => 3]);
    }
}
