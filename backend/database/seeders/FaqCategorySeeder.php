<?php

namespace Database\Seeders;

use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds the starting Site FAQ Category topics (Milestone 17) so the FAQ
 * admin screen has real groups to assign on first login.
 */
class FaqCategorySeeder extends Seeder
{
    public function run(): void
    {
        FaqCategory::firstOrCreate(['name' => 'Admissions'], ['slug' => 'admissions', 'order' => 0]);
        FaqCategory::firstOrCreate(['name' => 'Fees & Scholarships'], ['slug' => 'fees-scholarships', 'order' => 1]);
        FaqCategory::firstOrCreate(['name' => 'Campus Life'], ['slug' => 'campus-life', 'order' => 2]);
    }
}
