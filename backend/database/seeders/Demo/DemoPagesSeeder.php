<?php

namespace Database\Seeders\Demo;

use App\Models\MediaLibrary;
use App\Models\Page;
use App\Models\PageBlock;
use Database\Seeders\Demo\Concerns\SeedsMedia;
use Illuminate\Database\Seeder;

/**
 * Content Population pass (demo data) — gives every Page Builder page
 * PageSeeder already created (About, Vision, Mission, Admissions, ...) a
 * real hero image, and adds a supporting gallery block to the two most
 * visual pages (Student Life, International Students). PageSeeder
 * deliberately shipped these blocks image-less — "no Media Library asset
 * exists on a fresh install" (see its own docblock) — this is that gap,
 * filled in. A hero block's `media_id` is only ever set if it's
 * currently null, so a real edit an admin already made is never touched.
 */
class DemoPagesSeeder extends Seeder
{
    use SeedsMedia;

    private DemoImageLibrary $images;

    public function run(): void
    {
        $this->images = new DemoImageLibrary;

        $this->heroImage('about', 'campus_building');
        $this->heroImage('vision', 'library_study');
        $this->heroImage('mission', 'campus_courtyard');
        $this->heroImage('chairmans-message', 'professor_portrait_1');
        $this->heroImage('admissions', 'student_presentation');
        $this->heroImage('how-to-apply', 'students_studying_table');
        $this->heroImage('scholarships', 'graduates_walking');
        $this->heroImage('international-students', 'international_flags');
        $this->heroImage('student-life', 'sports_field');
        $this->heroImage('career', 'job_interview');
        $this->heroImage('privacy-policy', 'campus_building');
        $this->heroImage('terms', 'campus_building');
        $this->heroImage('refund-policy', 'campus_building');

        $this->galleryBlock('student-life', ['sports_field', 'cultural_festival', 'dorm_room', 'library_study']);
        $this->galleryBlock('international-students', ['diverse_students_group', 'dorm_room', 'students_studying_table', 'job_interview']);
    }

    private function heroImage(string $pageSlug, string $imageKey): void
    {
        $page = Page::where('slug', $pageSlug)->first();

        if (! $page) {
            return;
        }

        $heroBlock = $page->blocks()->where('block_type', 'hero')->orderBy('order')->first();

        if (! $heroBlock) {
            return;
        }

        $data = $heroBlock->data ?? [];

        if (! empty($data['media_id'])) {
            return;
        }

        $mediaId = $this->uploadToLibrary($imageKey, "{$page->title} hero image");
        $heroBlock->update(['data' => [...$data, 'media_id' => $mediaId]]);
    }

    private function galleryBlock(string $pageSlug, array $imageKeys): void
    {
        $page = Page::where('slug', $pageSlug)->first();

        if (! $page) {
            return;
        }

        if ($page->blocks()->where('block_type', 'gallery')->exists()) {
            return;
        }

        $mediaIds = array_map(fn (string $key) => $this->uploadToLibrary($key, $page->title.' gallery image'), $imageKeys);

        $nextOrder = (int) $page->blocks()->max('order') + 1;

        PageBlock::create([
            'page_id' => $page->id,
            'block_type' => 'gallery',
            'data' => ['media_ids' => $mediaIds],
            'order' => $nextOrder,
            'is_active' => true,
        ]);
    }

    private function uploadToLibrary(string $imageKey, string $altText): int
    {
        $root = MediaLibrary::query()->findOrFail(1);

        $media = $root->addMedia($this->images->path($imageKey))
            ->preservingOriginal()
            ->usingName($this->images->label($imageKey))
            ->toMediaCollection('library');

        $media->forceFill(['alt_text' => $altText])->save();

        return $media->id;
    }
}
