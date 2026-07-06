<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Database\Seeder;

/**
 * Seeds a couple of departments under each faculty seeded by
 * FacultySeeder, published, so the admin Department Management screen
 * and the public /api/v1/departments endpoint have real content on
 * first login. No banner is attached — no Media Library asset exists on
 * a fresh install (same reasoning as FacultySeeder).
 */
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->department('faculty-of-business', 'Department of Accounting & Finance', 'department-of-accounting-finance', 0);
        $this->department('faculty-of-business', 'Department of Marketing', 'department-of-marketing', 1);

        $this->department('faculty-of-engineering', 'Department of Civil Engineering', 'department-of-civil-engineering', 0);
        $this->department('faculty-of-engineering', 'Department of Electrical Engineering', 'department-of-electrical-engineering', 1);

        $this->department('faculty-of-computing', 'Department of Computer Science', 'department-of-computer-science', 0);
        $this->department('faculty-of-computing', 'Department of Software Engineering', 'department-of-software-engineering', 1);
    }

    private function department(string $facultySlug, string $name, string $slug, int $order): void
    {
        $faculty = Faculty::where('slug', $facultySlug)->first();

        if (! $faculty) {
            return;
        }

        Department::firstOrCreate(
            ['slug' => $slug],
            [
                'faculty_id' => $faculty->id,
                'name' => $name,
                'order' => $order,
                'status' => 'published',
            ]
        );
    }
}
