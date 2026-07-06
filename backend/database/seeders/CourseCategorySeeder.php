<?php

namespace Database\Seeders;

use App\Models\CourseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $topLevel = [
            'Business & Management' => ['Accounting & Finance', 'Marketing & HR'],
            'Engineering & Technology' => [],
            'Computing & IT' => ['Software Development'],
        ];

        foreach (array_values(array_keys($topLevel)) as $order => $name) {
            $parent = CourseCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'order' => $order]);

            foreach (array_values($topLevel[$name]) as $childOrder => $childName) {
                CourseCategory::firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    ['name' => $childName, 'order' => $childOrder, 'parent_id' => $parent->id]
                );
            }
        }
    }
}
