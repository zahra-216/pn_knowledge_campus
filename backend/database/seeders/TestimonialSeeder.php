<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Seeds a few featured testimonials so the homepage's Testimonials
 * section has real content on first login. No photo is attached — see
 * HeroSlideSeeder's docblock for why.
 */
class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        Testimonial::firstOrCreate(
            ['name' => 'Aisha Rahman'],
            ['role_title' => 'BSc Graduate, 2024', 'content' => 'The faculty here pushed me to think beyond the syllabus. I left more confident and more capable than I ever expected.', 'rating' => 5, 'is_featured' => true, 'order' => 0]
        );

        Testimonial::firstOrCreate(
            ['name' => 'Daniel Osei'],
            ['role_title' => 'MSc Graduate, 2023', 'content' => 'Small class sizes meant real mentorship, not just lectures. That made all the difference.', 'rating' => 5, 'is_featured' => true, 'order' => 1]
        );

        Testimonial::firstOrCreate(
            ['name' => 'Priya Nair'],
            ['role_title' => 'Current Student', 'content' => 'From day one, campus life felt like a community, not just a school.', 'rating' => 4, 'is_featured' => true, 'order' => 2]
        );
    }
}
