<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use Illuminate\Database\Seeder;

/**
 * Seeds one row per HomepageSection::SECTIONS key, in the order the
 * client requested them, all enabled by default. Idempotent
 * (firstOrCreate by section_key).
 */
class HomepageSectionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_values(HomepageSection::SECTIONS) as $order => $sectionKey) {
            HomepageSection::firstOrCreate(
                ['section_key' => $sectionKey],
                ['order' => $order, 'is_enabled' => true]
            );
        }
    }
}
