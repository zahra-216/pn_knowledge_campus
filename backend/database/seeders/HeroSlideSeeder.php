<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

/**
 * Seeds a few starting hero slides so the homepage's Hero section has
 * real content on first login. No image is attached — no Media Library
 * asset exists on a fresh install, and fabricating a fake media_id
 * would violate this project's "everything from database, nothing
 * hardcoded" principle the same way Page Builder's seed data already
 * skips media-dependent blocks.
 */
class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        HeroSlide::firstOrCreate(
            ['title' => 'Welcome to PNK Global Campus'],
            ['subtitle' => 'Building futures through knowledge, character, and community.', 'cta_text' => 'Explore Courses', 'cta_url' => '/courses', 'order' => 0]
        );

        HeroSlide::firstOrCreate(
            ['title' => 'Admissions Now Open'],
            ['subtitle' => 'Start your journey with us today.', 'cta_text' => 'Apply Now', 'cta_url' => '/admissions', 'order' => 1]
        );

        HeroSlide::firstOrCreate(
            ['title' => 'A Campus Built for Student Life'],
            ['subtitle' => 'Clubs, sports, and events beyond the classroom.', 'cta_text' => 'Learn More', 'cta_url' => '/student-life', 'order' => 2]
        );
    }
}
