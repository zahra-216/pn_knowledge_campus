<?php

namespace Database\Seeders;

use App\Models\CourseMode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseModeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Full-Time', 'Part-Time', 'Online', 'Blended'] as $order => $name) {
            CourseMode::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'order' => $order]);
        }
    }
}
