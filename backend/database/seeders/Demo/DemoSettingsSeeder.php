<?php

namespace Database\Seeders\Demo;

use App\Models\MediaLibrary;
use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Database\Seeder;

/**
 * Content Population pass (demo data) — fills in the Settings registry,
 * Social Links, and global SEO defaults with realistic, clearly-marked
 * demo values. Every setting is only ever written if it's currently
 * empty (`setIfEmpty`), so re-running this seeder — or running it after
 * a real admin has already configured some of these — never clobbers
 * real values. Contact details use obviously-fictitious data
 * (+000 country code, a .test/.example-style address) so nobody mistakes
 * them for a real institution's real phone/email.
 */
class DemoSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $images = new DemoImageLibrary;

        $this->seedCampusInfo();
        $this->seedContact();
        $this->seedMaps();
        $this->seedBranding($images);
        $this->seedFooter();
        $this->seedSeoDefaults($images);
        $this->seedHomepageCopy($images);
        $this->seedSocialLinks();
    }

    private function setIfEmpty(string $key, ?string $value): void
    {
        if ($value === null) {
            return;
        }

        $setting = Setting::where('key', $key)->first();

        if ($setting && ($setting->value === null || $setting->value === '')) {
            $setting->update(['value' => $value]);
        }
    }

    private function seedCampusInfo(): void
    {
        $this->setIfEmpty('campus_name', 'PNK Global Campus');
        $this->setIfEmpty('campus_short_name', 'PNK Global Campus');
        $this->setIfEmpty('campus_tagline', 'Building futures through knowledge, character, and community.');
        $this->setIfEmpty('registration_number', 'DEMO-REG-2019-004821 (demo value)');
        $this->setIfEmpty('accreditation_number', 'DEMO-ACC-NAB-2021-0037 (demo value)');
    }

    private function seedContact(): void
    {
        $this->setIfEmpty('contact_email', 'info@demo.pnknowledgecampus.test');
        $this->setIfEmpty('contact_phone', '+000 11 234 5678 (demo number)');
        $this->setIfEmpty('contact_address', '124 University Avenue, Knowledge District, Demo City 10250 (demo address)');
        $this->setIfEmpty('admissions_email', 'admissions@demo.pnknowledgecampus.test');
        $this->setIfEmpty('admissions_phone', '+000 11 234 5679 (demo number)');
    }

    private function seedMaps(): void
    {
        // No-API-key embed form — safe for demo use, doesn't require a
        // real Google Cloud API key to render an iframe.
        $this->setIfEmpty(
            'google_maps_embed_url',
            'https://www.google.com/maps?q=University+Avenue&output=embed'
        );
    }

    /**
     * Uploads a fresh asset straight into the shared MediaLibrary
     * singleton's 'library' collection and returns its id — used for the
     * handful of Settings keys that store a bare media id (favicon,
     * default OG image, homepage welcome image). Deliberately does NOT
     * use SeedsMedia's attach*() helpers: those guard on "is this
     * collection already non-empty", which is the right question for an
     * exclusive per-model collection (a Course's featured_image) but the
     * wrong one here — 'library' is a shared dumping ground that already
     * holds an admin's real uploads (e.g. the site logo), so it is never
     * "empty". The actual idempotency guard for these settings lives one
     * level up, in each caller checking the *Setting's own* value first.
     */
    private function uploadToLibrary(DemoImageLibrary $images, string $imageKey, string $altText): int
    {
        $root = MediaLibrary::query()->findOrFail(1);

        $media = $root->addMedia($images->path($imageKey))
            ->preservingOriginal()
            ->usingName($images->label($imageKey))
            ->toMediaCollection('library');

        $media->forceFill(['alt_text' => $altText])->save();

        return $media->id;
    }

    private function uploadLogoToLibrary(DemoImageLibrary $images, string $slug, string $label, string $hex, string $altText): int
    {
        $root = MediaLibrary::query()->findOrFail(1);

        $media = $root->addMedia($images->logo($slug, $label, $hex))
            ->preservingOriginal()
            ->usingName("{$label} logo")
            ->toMediaCollection('library');

        $media->forceFill(['alt_text' => $altText])->save();

        return $media->id;
    }

    private function seedBranding(DemoImageLibrary $images): void
    {
        $favicon = Setting::where('key', 'favicon_media_id')->first();

        if ($favicon && ($favicon->value === null || $favicon->value === '')) {
            $mediaId = $this->uploadLogoToLibrary($images, 'favicon-mark', 'PN', '#1B2A4A', 'PNK Global Campus favicon mark');
            $favicon->update(['value' => (string) $mediaId]);
        }
    }

    private function seedFooter(): void
    {
        $this->setIfEmpty(
            'footer_text',
            'PNK Global Campus is a demo-content higher education institution used to evaluate this public website design.'
        );
        $this->setIfEmpty('footer_copyright', '© '.date('Y').' PNK Global Campus. All rights reserved. (demo content)');
    }

    private function seedSeoDefaults(DemoImageLibrary $images): void
    {
        $this->setIfEmpty('site_url', 'http://localhost:5173');
        $this->setIfEmpty('default_meta_title', 'PNK Global Campus — Higher Education, Reimagined');
        $this->setIfEmpty(
            'default_meta_description',
            'PNK Global Campus offers undergraduate and postgraduate programmes across Business, Engineering, Computing, and Health Sciences, backed by modern facilities and a supportive campus community.'
        );
        $this->setIfEmpty('default_keywords', 'university, higher education, undergraduate, postgraduate, courses, admissions');

        $ogSetting = Setting::where('key', 'default_og_image_media_id')->first();

        if ($ogSetting && ($ogSetting->value === null || $ogSetting->value === '')) {
            $mediaId = $this->uploadToLibrary($images, 'campus_aerial', 'Aerial view of PNK Global Campus');
            $ogSetting->update(['value' => (string) $mediaId]);
        }
    }

    private function seedHomepageCopy(DemoImageLibrary $images): void
    {
        $this->setIfEmpty('welcome_heading', 'A Campus Built Around You');
        $this->setIfEmpty(
            'welcome_body',
            "For over a decade, PNK Global Campus has combined rigorous academics with a genuinely supportive community. Small class sizes mean real mentorship, not just lectures — and our four faculties give students the breadth to explore before they specialise.\n\nWhether you're starting your first degree, returning to study part-time, or joining us from abroad, our Admissions and Student Life teams are built to help you find your footing fast."
        );

        $welcomeMediaSetting = Setting::where('key', 'welcome_media_id')->first();

        if ($welcomeMediaSetting && ($welcomeMediaSetting->value === null || $welcomeMediaSetting->value === '')) {
            $mediaId = $this->uploadToLibrary($images, 'students_studying_table', 'Students collaborating around a table in the library');
            $welcomeMediaSetting->update(['value' => (string) $mediaId]);
        }

        $this->setIfEmpty('why_choose_us_items', json_encode([
            ['icon' => '🎓', 'title' => 'Industry-Aligned Curriculum', 'text' => 'Every programme is reviewed annually with input from real employers, so what you learn stays current.'],
            ['icon' => '🧑‍🏫', 'title' => 'Small Class Sizes', 'text' => 'Average class sizes under 30 mean lecturers know your name — and your goals.'],
            ['icon' => '🌍', 'title' => 'A Global Student Body', 'text' => 'Students from over 20 countries study here, supported by a dedicated International Office.'],
            ['icon' => '💼', 'title' => 'Real Career Outcomes', 'text' => '87% of our graduates are employed or in further study within six months of graduating.'],
        ]));

        $this->setIfEmpty('statistics_items', json_encode([
            ['label' => 'Students Enrolled', 'value' => '6,200+'],
            ['label' => 'Faculties', 'value' => '4'],
            ['label' => 'Programmes Offered', 'value' => '15'],
            ['label' => 'Graduate Employment Rate', 'value' => '87%'],
        ]));

        $this->setIfEmpty('cta_heading', 'Your Next Chapter Starts Here');
        $this->setIfEmpty('cta_body', 'Applications for the next intake are open now. Talk to our Admissions team or start your application online today.');
        $this->setIfEmpty('cta_button_label', 'Start Your Application');
        $this->setIfEmpty('cta_button_url', '/apply');
    }

    private function seedSocialLinks(): void
    {
        if (SocialLink::count() > 0) {
            return;
        }

        $links = [
            ['platform' => 'facebook', 'url' => 'https://facebook.com/pnknowledgecampus.demo', 'order' => 0],
            ['platform' => 'instagram', 'url' => 'https://instagram.com/pnknowledgecampus.demo', 'order' => 1],
            ['platform' => 'linkedin', 'url' => 'https://linkedin.com/school/pnknowledgecampus-demo', 'order' => 2],
            ['platform' => 'youtube', 'url' => 'https://youtube.com/@pnknowledgecampus-demo', 'order' => 3],
        ];

        foreach ($links as $link) {
            SocialLink::firstOrCreate(
                ['platform' => $link['platform']],
                ['url' => $link['url'], 'order' => $link['order'], 'is_active' => true]
            );
        }
    }
}
