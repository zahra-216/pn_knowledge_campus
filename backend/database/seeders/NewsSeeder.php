<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'superadmin@pnknowledgecampus.edu')->first();

        $this->createArticle('Campus Wins National Innovation Award', 'Announcements', $author, true);
        $this->createArticle('New Partnership With Regional Hospitals Announced', 'Press Releases', $author);
        $this->createArticle('Faculty of Engineering Students Place First at Robotics Competition', 'Achievements', $author);
    }

    private function createArticle(string $title, string $categoryName, ?User $author, bool $isFeatured = false): void
    {
        $slug = Str::slug($title);

        if (News::where('slug', $slug)->exists()) {
            return;
        }

        $category = NewsCategory::where('name', $categoryName)->first();

        News::create([
            'category_id' => $category?->id,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => "A short introduction to \"{$title}\".",
            'body' => "<p>This is the full body of <strong>{$title}</strong>.</p>",
            'author_id' => $author?->id,
            'status' => 'published',
            'published_at' => Carbon::now()->subDays(random_int(1, 30)),
            'is_featured' => $isFeatured,
        ]);
    }
}
