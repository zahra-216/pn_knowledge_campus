<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'superadmin@pnknowledgecampus.edu')->first();

        $this->createPost(
            title: 'Welcome to the New Academic Year',
            categoryName: 'Campus Life',
            tagNames: ['Events'],
            author: $author,
            isFeatured: true,
        );

        $this->createPost(
            title: 'Five Scholarships Every New Student Should Know About',
            categoryName: 'Student Stories',
            tagNames: ['Admissions', 'Scholarships'],
            author: $author,
        );

        $this->createPost(
            title: 'Where Our Graduates Are Now',
            categoryName: 'Alumni Spotlight',
            tagNames: ['Research'],
            author: $author,
        );
    }

    private function createPost(string $title, string $categoryName, array $tagNames, ?User $author, bool $isFeatured = false): void
    {
        $slug = Str::slug($title);

        if (BlogPost::where('slug', $slug)->exists()) {
            return;
        }

        $category = BlogCategory::where('name', $categoryName)->first();

        $post = BlogPost::create([
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

        $tagIds = Tag::whereIn('name', $tagNames)->pluck('id');
        $post->tags()->sync($tagIds);
    }
}
