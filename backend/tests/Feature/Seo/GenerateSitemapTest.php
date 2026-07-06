<?php

namespace Tests\Feature\Seo;

use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GenerateSitemapTest extends TestCase
{
    use RefreshDatabase;

    private string $outputDir;

    protected function setUp(): void
    {
        parent::setUp();
        // A throwaway directory, never the real sibling frontend/public —
        // see GenerateSitemap's --path option, added specifically so
        // tests never risk writing into the actual frontend build output.
        $this->outputDir = sys_get_temp_dir().'/sitemap-test-'.uniqid();
        File::makeDirectory($this->outputDir, recursive: true);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->outputDir);
        parent::tearDown();
    }

    private function course(): Course
    {
        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);

        return Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'BSc Computer Science', 'course_code' => 'CS-001', 'slug' => 'bsc-computer-science',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'Overview', 'description' => 'Description',
            'status' => 'published', 'published_at' => now(),
        ]);
    }

    public function test_generates_a_sitemap_with_static_and_published_entity_urls(): void
    {
        Setting::create(['key' => 'site_url', 'value' => 'https://pnkc.example.com', 'group' => 'seo_defaults', 'is_public' => true]);
        $course = $this->course();

        $this->artisan('sitemap:generate', ['--path' => $this->outputDir])->assertSuccessful();

        $xml = File::get($this->outputDir.'/sitemap.xml');
        $this->assertStringContainsString('https://pnkc.example.com/', $xml);
        $this->assertStringContainsString("https://pnkc.example.com/courses/{$course->slug}", $xml);
    }

    public function test_excludes_courses_with_robots_index_disabled(): void
    {
        Setting::create(['key' => 'site_url', 'value' => 'https://pnkc.example.com', 'group' => 'seo_defaults', 'is_public' => true]);
        $course = $this->course();
        $course->seoMeta()->create(['robots_index' => false]);

        $this->artisan('sitemap:generate', ['--path' => $this->outputDir])->assertSuccessful();

        $xml = File::get($this->outputDir.'/sitemap.xml');
        $this->assertStringNotContainsString("/courses/{$course->slug}", $xml);
    }

    public function test_excludes_unpublished_content(): void
    {
        Setting::create(['key' => 'site_url', 'value' => 'https://pnkc.example.com', 'group' => 'seo_defaults', 'is_public' => true]);
        Page::create(['title' => 'Draft Page', 'slug' => 'draft-page', 'status' => 'draft']);

        $this->artisan('sitemap:generate', ['--path' => $this->outputDir])->assertSuccessful();

        $xml = File::get($this->outputDir.'/sitemap.xml');
        $this->assertStringNotContainsString('draft-page', $xml);
    }

    public function test_generates_a_robots_txt_referencing_the_sitemap(): void
    {
        Setting::create(['key' => 'site_url', 'value' => 'https://pnkc.example.com', 'group' => 'seo_defaults', 'is_public' => true]);

        $this->artisan('sitemap:generate', ['--path' => $this->outputDir])->assertSuccessful();

        $robots = File::get($this->outputDir.'/robots.txt');
        $this->assertStringContainsString('Disallow: /admin', $robots);
        $this->assertStringContainsString('Sitemap: https://pnkc.example.com/sitemap.xml', $robots);
    }

    public function test_falls_back_to_app_url_when_site_url_is_not_configured(): void
    {
        $this->artisan('sitemap:generate', ['--path' => $this->outputDir])->assertSuccessful();

        $xml = File::get($this->outputDir.'/sitemap.xml');
        $this->assertStringContainsString(rtrim(config('app.url'), '/'), $xml);
    }
}
