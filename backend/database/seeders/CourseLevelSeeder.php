<?php

namespace Database\Seeders;

use App\Models\CourseLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseLevelSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Certificate', 'Diploma', 'Undergraduate', 'Postgraduate'] as $order => $name) {
            CourseLevel::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'order' => $order]);
        }
    }
}
