<?php

namespace Database\Seeders\Demo;

use App\Models\HeroSlide;
use Database\Seeders\Demo\Concerns\SeedsMedia;
use Illuminate\Database\Seeder;

/**
 * Content Population pass (demo data) — attaches a real image to each
 * Hero Slide HeroSlideSeeder already created (shipped image-less by
 * design — no Media Library asset existed on a fresh install; see that
 * seeder's own docblock). This is the homepage's single most visible
 * placement, so each slide gets a distinct, high-impact photo.
 */
class DemoHomepageSeeder extends Seeder
{
    use SeedsMedia;

    public function run(): void
    {
        $images = new DemoImageLibrary;

        $this->slide($images, 'Welcome to PNK Global Campus', 'campus_aerial');
        $this->slide($images, 'Admissions Now Open', 'graduates_walking');
        $this->slide($images, 'A Campus Built for Student Life', 'cultural_festival');
    }

    private function slide(DemoImageLibrary $images, string $title, string $imageKey): void
    {
        $slide = HeroSlide::where('title', $title)->first();

        if ($slide) {
            $this->attachImage($slide, 'slide_image', $images, $imageKey, $title);
        }
    }
}
