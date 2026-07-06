<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;

/**
 * Seeds a few starting faculties, published, so the admin Faculty
 * Management screen and the public /api/v1/faculties endpoint have real
 * content on first login. No banner/icon/gallery/dean photo is attached
 * — no Media Library asset exists on a fresh install (same reasoning as
 * HeroSlideSeeder/TestimonialSeeder/PartnerSeeder).
 */
class FacultySeeder extends Seeder
{
    public function run(): void
    {
        Faculty::firstOrCreate(
            ['slug' => 'faculty-of-business'],
            [
                'name' => 'Faculty of Business',
                'short_description' => 'Building the next generation of business leaders and entrepreneurs.',
                'description' => 'The Faculty of Business offers a range of undergraduate and postgraduate programmes spanning management, finance, marketing, and entrepreneurship.',
                'dean_name' => 'Dr. Amara Chukwu',
                'dean_title' => 'Dean, Faculty of Business',
                'dean_message' => 'Our faculty is committed to producing graduates who are ready to lead in a fast-changing global economy.',
                'order' => 0,
                'status' => 'published',
            ]
        );

        Faculty::firstOrCreate(
            ['slug' => 'faculty-of-engineering'],
            [
                'name' => 'Faculty of Engineering',
                'short_description' => 'Practical, hands-on engineering education across multiple disciplines.',
                'description' => 'The Faculty of Engineering delivers rigorous programmes in civil, mechanical, electrical, and computer engineering, backed by modern labs and industry partnerships.',
                'dean_name' => 'Prof. Samuel Owusu',
                'dean_title' => 'Dean, Faculty of Engineering',
                'dean_message' => 'We prepare engineers who can solve real problems from day one.',
                'order' => 1,
                'status' => 'published',
            ]
        );

        Faculty::firstOrCreate(
            ['slug' => 'faculty-of-computing'],
            [
                'name' => 'Faculty of Computing',
                'short_description' => 'Computer science, software engineering, and data science programmes.',
                'description' => 'The Faculty of Computing combines strong theoretical foundations with practical software development, data science, and cybersecurity training.',
                'dean_name' => 'Dr. Grace Mensah',
                'dean_title' => 'Dean, Faculty of Computing',
                'dean_message' => 'Technology moves fast, and so do we — our curriculum evolves every year to keep pace with industry.',
                'order' => 2,
                'status' => 'published',
            ]
        );
    }
}
