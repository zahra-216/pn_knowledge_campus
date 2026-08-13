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

/**
 * Audit fix (High remediation) — see SeoShellController/SeoShellResolver's
 * docblocks. A throwaway `dist_path` fixture (never the real sibling
 * frontend/dist) so these tests never depend on the frontend actually
 * being built, the same isolation approach GenerateSitemapTest already
 * uses for its own sibling-directory write.
 */
class SeoShellTest extends TestCase
{
    use RefreshDatabase;

    private string $distDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->distDir = sys_get_temp_dir().'/seo-shell-test-'.uniqid();
        File::makeDirectory($this->distDir, recursive: true);
        File::put($this->distDir.'/index.html', <<<'HTML'
            <!doctype html>
            <html lang="en">
              <head>
                <meta charset="UTF-8" />
                <title>Stale Build-Time Title</title>
                <script type="module" crossorigin src="/assets/index-abc123.js"></script>
              </head>
              <body>
                <div id="root"></div>
              </body>
            </html>
            HTML);
        config(['frontend.dist_path' => $this->distDir]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->distDir);
        parent::tearDown();
    }

    public function test_home_renders_the_compiled_shell_with_real_meta_tags(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('<title>Home | PNK Global Campus</title>', false);
        $response->assertSee('script type="module" crossorigin src="/assets/index-abc123.js"', false);
        $response->assertSee('<meta name="robots" content="index, follow">', false);
        $response->assertDontSee('Stale Build-Time Title', false);
    }

    public function test_a_course_detail_page_embeds_its_own_seo_meta(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing', 'status' => 'published']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs', 'status' => 'published']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);
        $course = Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'BSc Computer Science', 'course_code' => 'CS-001', 'slug' => 'bsc-computer-science',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'Study CS with us.', 'description' => 'Description',
            'status' => 'published',
        ]);
        $course->seoMeta()->create(['seo_title' => 'BSc CS — Custom SEO Title', 'canonical_url' => 'https://pnkc.test/courses/bsc-computer-science']);

        $response = $this->get('/courses/bsc-computer-science');

        $response->assertOk();
        $response->assertSee('<title>BSc CS — Custom SEO Title</title>', false);
        $response->assertSee('<link rel="canonical" href="https://pnkc.test/courses/bsc-computer-science">', false);
    }

    public function test_a_course_with_no_seo_row_falls_back_to_its_own_natural_title_and_overview(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing', 'status' => 'published']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs', 'status' => 'published']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);
        Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'BSc Computer Science', 'course_code' => 'CS-001', 'slug' => 'bsc-computer-science',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'Study CS with us.', 'description' => 'Description',
            'status' => 'published',
        ]);

        $response = $this->get('/courses/bsc-computer-science');

        $response->assertOk();
        $response->assertSee('<title>BSc Computer Science | PNK Global Campus</title>', false);
        $response->assertSee('<meta name="description" content="Study CS with us.">', false);
    }

    public function test_an_unpublished_course_slug_renders_as_not_found(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing', 'status' => 'published']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs', 'status' => 'published']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);
        Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'Draft Course', 'course_code' => 'CS-002', 'slug' => 'draft-course',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'Overview', 'description' => 'Description',
            'status' => 'draft',
        ]);

        $response = $this->get('/courses/draft-course');

        $response->assertNotFound();
        $response->assertSee('<title>Page Not Found | PNK Global Campus</title>', false);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_a_static_builder_page_embeds_its_own_seo_meta(): void
    {
        $page = Page::create(['title' => 'About', 'slug' => 'about', 'status' => 'published', 'published_at' => now()]);
        $page->seoMeta()->create(['meta_description' => 'About PNK Global Campus.']);

        $response = $this->get('/about');

        $response->assertOk();
        $response->assertSee('<title>About | PNK Global Campus</title>', false);
        $response->assertSee('<meta name="description" content="About PNK Global Campus.">', false);
    }

    public function test_a_completely_unknown_path_renders_as_not_found(): void
    {
        $response = $this->get('/this-page-does-not-exist-anywhere');

        $response->assertNotFound();
        $response->assertSee('<title>Page Not Found | PNK Global Campus</title>', false);
    }

    public function test_admin_paths_get_a_generic_noindex_shell_not_a_404(): void
    {
        $response = $this->get('/admin/settings');

        $response->assertOk();
        $response->assertSee('<title>Admin | PNK Global Campus</title>', false);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_api_routes_are_untouched_by_the_catch_all(): void
    {
        $response = $this->getJson('/api/v1/settings/public');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_canonical_url_prefers_the_site_url_setting_over_app_url(): void
    {
        Setting::create(['key' => 'site_url', 'value' => 'https://pnknowledgecampus.test', 'group' => 'seo_defaults', 'is_public' => true]);

        $response = $this->get('/contact');

        $response->assertOk();
        $response->assertSee('<link rel="canonical" href="https://pnknowledgecampus.test/contact">', false);
    }

    public function test_returns_a_service_unavailable_message_when_the_frontend_has_not_been_built(): void
    {
        File::deleteDirectory($this->distDir);

        $response = $this->get('/');

        $response->assertStatus(503);
    }
}
