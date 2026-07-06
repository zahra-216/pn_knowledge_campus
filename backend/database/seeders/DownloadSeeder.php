<?php

namespace Database\Seeders;

use App\Models\Download;
use App\Models\DownloadCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds a few Downloads catalog entries (Milestone 18) so the admin
 * screen has real rows to categorize on first login. No file is
 * attached — see HeroSlideSeeder's docblock for why (no Media Library
 * asset exists on a fresh install). Depends on DownloadCategorySeeder
 * having run first (see DatabaseSeeder's ordering).
 */
class DownloadSeeder extends Seeder
{
    public function run(): void
    {
        $prospectus = DownloadCategory::where('slug', 'prospectus')->first();
        $forms = DownloadCategory::where('slug', 'forms')->first();
        $brochures = DownloadCategory::where('slug', 'brochures')->first();

        Download::firstOrCreate(['title' => 'Undergraduate Prospectus 2026'], [
            'category_id' => $prospectus?->id,
            'description' => 'A complete guide to our undergraduate programmes, faculties, and campus life.',
            'order' => 0,
        ]);

        Download::firstOrCreate(['title' => 'Application Form'], [
            'category_id' => $forms?->id,
            'description' => 'The standard application form for all undergraduate and postgraduate programmes.',
            'order' => 0,
        ]);

        Download::firstOrCreate(['title' => 'Scholarship Application Form'], [
            'category_id' => $forms?->id,
            'description' => 'Required for students applying for merit-based or need-based scholarships.',
            'order' => 1,
        ]);

        Download::firstOrCreate(['title' => 'Faculty of Computing Brochure'], [
            'category_id' => $brochures?->id,
            'description' => 'An overview of courses, facilities, and career outcomes in the Faculty of Computing.',
            'order' => 0,
        ]);
    }
}
