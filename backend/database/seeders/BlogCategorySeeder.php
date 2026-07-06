<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Campus Life', 'Student Stories', 'Alumni Spotlight'] as $order => $name) {
            BlogCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'order' => $order]);
        }
    }
}
