<?php

namespace Database\Seeders;

use App\Models\GalleryAlbum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * No media items attached — a fresh install has no Media Library
 * entries to attach yet, same reasoning as FacultySeeder/CourseSeeder.
 */
class GalleryAlbumSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Open Day 2026', 'Campus Life', 'Graduation Ceremony 2025'] as $order => $title) {
            GalleryAlbum::firstOrCreate(['slug' => Str::slug($title)], ['title' => $title, 'order' => $order]);
        }
    }
}
