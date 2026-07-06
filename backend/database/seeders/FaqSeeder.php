<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds a few global Site FAQ entries (Milestone 17) so the public FAQ
 * page has real content on first login. Depends on FaqCategorySeeder
 * having run first (see DatabaseSeeder's ordering). Rows are global —
 * faqable_type/faqable_id are left null (see Faq::scopeGlobal()).
 */
class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $admissions = FaqCategory::where('slug', 'admissions')->first();
        $fees = FaqCategory::where('slug', 'fees-scholarships')->first();
        $campusLife = FaqCategory::where('slug', 'campus-life')->first();

        Faq::firstOrCreate(['question' => 'How do I apply for admission?'], [
            'category_id' => $admissions?->id,
            'answer' => 'Visit the How to Apply page and submit the online application form along with the required documents.',
            'order' => 0,
        ]);

        Faq::firstOrCreate(['question' => 'What documents are required for admission?'], [
            'category_id' => $admissions?->id,
            'answer' => 'You will need your academic transcripts, a copy of your national ID or passport, and passport-sized photographs.',
            'order' => 1,
        ]);

        Faq::firstOrCreate(['question' => 'Are scholarships available?'], [
            'category_id' => $fees?->id,
            'answer' => 'Yes — merit-based and need-based scholarships are available. See the Scholarships page for eligibility criteria.',
            'order' => 0,
        ]);

        Faq::firstOrCreate(['question' => 'What facilities are available on campus?'], [
            'category_id' => $campusLife?->id,
            'answer' => 'The campus offers a library, computer labs, sports facilities, and student clubs covering a wide range of interests.',
            'order' => 0,
        ]);
    }
}
