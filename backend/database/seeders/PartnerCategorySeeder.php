<?php

namespace Database\Seeders;

use App\Models\PartnerCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds the starting Partner Category types (Milestone 16) so the
 * Partners admin screen has real groups to assign on first login.
 */
class PartnerCategorySeeder extends Seeder
{
    public function run(): void
    {
        PartnerCategory::firstOrCreate(['name' => 'Academic Partner'], ['slug' => 'academic-partner', 'order' => 0]);
        PartnerCategory::firstOrCreate(['name' => 'Industry Partner'], ['slug' => 'industry-partner', 'order' => 1]);
        PartnerCategory::firstOrCreate(['name' => 'Accreditation Body'], ['slug' => 'accreditation-body', 'order' => 2]);
    }
}
