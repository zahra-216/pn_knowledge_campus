<?php

namespace Database\Seeders;

use App\Models\NewsCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Press Releases', 'Announcements', 'Achievements'] as $order => $name) {
            NewsCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'order' => $order]);
        }
    }
}
