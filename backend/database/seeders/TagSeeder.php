<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Admissions', 'Scholarships', 'Events', 'Research'] as $name) {
            Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }
}
