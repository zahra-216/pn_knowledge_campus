<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\PartnerCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds a few accreditation/partner entries so the homepage's Partners
 * section has real content on first login. No logo is attached — see
 * HeroSlideSeeder's docblock for why. Depends on PartnerCategorySeeder
 * having run first (see DatabaseSeeder's ordering).
 */
class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $accreditationBody = PartnerCategory::where('slug', 'accreditation-body')->first();
        $academicPartner = PartnerCategory::where('slug', 'academic-partner')->first();
        $industryPartner = PartnerCategory::where('slug', 'industry-partner')->first();

        Partner::firstOrCreate(['name' => 'National Accreditation Board'], ['category_id' => $accreditationBody?->id, 'order' => 0]);
        Partner::firstOrCreate(['name' => 'Ministry of Education'], ['category_id' => $accreditationBody?->id, 'order' => 1]);
        Partner::firstOrCreate(['name' => 'International Education Alliance'], ['category_id' => $academicPartner?->id, 'url' => 'https://example.org', 'order' => 2]);
        Partner::firstOrCreate(['name' => 'TechCorp Solutions'], ['category_id' => $industryPartner?->id, 'url' => 'https://example.org', 'order' => 3]);
    }
}
