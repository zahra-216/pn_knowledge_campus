<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds a few published, featured-ready courses under the faculties/
 * departments already seeded by FacultySeeder/DepartmentSeeder, so the
 * admin Course Management screen, the public /api/v1/courses endpoint,
 * and the homepage's Featured Courses section all have real content on
 * first login. No featured_image/gallery/downloads media is attached —
 * no Media Library asset exists on a fresh install (same reasoning as
 * every other seeder in this project).
 */
class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $this->course(
            facultySlug: 'faculty-of-computing',
            departmentSlug: 'department-of-computer-science',
            levelName: 'Undergraduate',
            modeName: 'Full-Time',
            categoryName: 'Computing & IT',
            name: 'BSc (Hons) Computer Science',
            code: 'CS-BSC-001',
            durationValue: 3,
            price: 4500.00,
            isFeatured: true,
            overview: 'A comprehensive undergraduate programme in modern software development, algorithms, and systems design.',
            curriculum: [
                ['title' => 'Year 1: Foundations', 'children' => [
                    ['title' => 'Programming Fundamentals', 'duration' => '12 weeks'],
                    ['title' => 'Mathematics for Computing', 'duration' => '12 weeks'],
                ]],
                ['title' => 'Year 2: Core Systems', 'children' => [
                    ['title' => 'Data Structures & Algorithms', 'duration' => '12 weeks'],
                    ['title' => 'Databases', 'duration' => '12 weeks'],
                ]],
            ],
            faqs: [
                ['question' => 'What are the entry requirements?', 'answer' => '3 passes at GCE A/L including Mathematics.'],
                ['question' => 'Is there a placement year?', 'answer' => 'Yes, an optional industry placement is available after Year 2.'],
            ]
        );

        $this->course(
            facultySlug: 'faculty-of-computing',
            departmentSlug: 'department-of-software-engineering',
            levelName: 'Undergraduate',
            modeName: 'Full-Time',
            categoryName: 'Computing & IT',
            name: 'BSc (Hons) Data Science',
            code: 'CS-BSC-002',
            durationValue: 3,
            price: 4800.00,
            isFeatured: true,
            overview: 'An in-demand programme covering statistics, machine learning, and data engineering.',
            curriculum: [
                ['title' => 'Year 1: Foundations', 'children' => [
                    ['title' => 'Programming Fundamentals', 'duration' => '12 weeks'],
                    ['title' => 'Statistics I', 'duration' => '12 weeks'],
                ]],
            ],
            faqs: [
                ['question' => 'Do I need a programming background?', 'answer' => 'No prior experience is required — the first year covers the fundamentals.'],
            ]
        );

        $this->course(
            facultySlug: 'faculty-of-business',
            departmentSlug: 'department-of-accounting-finance',
            levelName: 'Diploma',
            modeName: 'Part-Time',
            categoryName: 'Business & Management',
            name: 'Diploma in Accounting & Finance',
            code: 'BUS-DIP-001',
            durationValue: 18,
            durationUnit: 'month',
            price: 2200.00,
            discountPrice: 1900.00,
            isFeatured: false,
            overview: 'A part-time diploma for working professionals looking to build a career in accounting and finance.',
            curriculum: [],
            faqs: []
        );
    }

    private function course(
        string $facultySlug,
        string $departmentSlug,
        string $levelName,
        string $modeName,
        string $categoryName,
        string $name,
        string $code,
        int $durationValue,
        float $price,
        bool $isFeatured,
        string $overview,
        array $curriculum,
        array $faqs,
        string $durationUnit = 'year',
        ?float $discountPrice = null,
    ): void {
        $faculty = Faculty::where('slug', $facultySlug)->first();
        $department = Department::where('slug', $departmentSlug)->first();
        $level = CourseLevel::where('name', $levelName)->first();
        $mode = CourseMode::where('name', $modeName)->first();
        $category = CourseCategory::where('name', $categoryName)->first();

        if (! $faculty || ! $department || ! $level || ! $mode) {
            return;
        }

        $course = Course::firstOrCreate(
            ['course_code' => $code],
            [
                'faculty_id' => $faculty->id,
                'department_id' => $department->id,
                'level_id' => $level->id,
                'mode_id' => $mode->id,
                'category_id' => $category?->id,
                'course_name' => $name,
                'slug' => Str::slug($name),
                'duration_value' => $durationValue,
                'duration_unit' => $durationUnit,
                'price' => $price,
                'discount_price' => $discountPrice,
                'overview' => $overview,
                'description' => "<p>{$overview}</p>",
                'status' => 'published',
                'published_at' => now(),
                'is_featured' => $isFeatured,
            ]
        );

        if (! $course->wasRecentlyCreated) {
            return;
        }

        foreach ($curriculum as $index => $module) {
            $created = $course->curriculumItems()->create([
                'title' => $module['title'],
                'order' => $index,
            ]);

            foreach ($module['children'] ?? [] as $childIndex => $child) {
                $course->curriculumItems()->create([
                    'parent_id' => $created->id,
                    'title' => $child['title'],
                    'duration' => $child['duration'] ?? null,
                    'order' => $childIndex,
                ]);
            }
        }

        foreach ($faqs as $index => $faq) {
            $course->faqs()->create([
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'order' => $index,
            ]);
        }
    }
}
