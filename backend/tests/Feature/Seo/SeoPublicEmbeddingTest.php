<?php

namespace Tests\Feature\Seo;

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Public Website milestone — before this, SEO was only reachable via
 * the admin-gated GET /admin/seo/{type}/{id} endpoint, so the public
 * site had no way to read a page's own meta title/description/OG/
 * canonical/schema data at all. Covers a representative sample
 * (Faculty, Course, BlogPost, Page) of the seven models that now embed
 * `seo` on their public *detail* response only, not list/index.
 */
class SeoPublicEmbeddingTest extends TestCase
{
    use RefreshDatabase;

    public function test_faculty_detail_embeds_seo_but_index_does_not(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business', 'status' => 'published']);
        $faculty->seoMeta()->create(['seo_title' => 'Faculty of Business | PNKC']);

        $show = $this->getJson('/api/v1/faculties/faculty-of-business');
        $show->assertOk();
        $this->assertSame('Faculty of Business | PNKC', $show->json('data.seo.seo_title'));

        $index = $this->getJson('/api/v1/faculties');
        $index->assertOk();
        $this->assertArrayNotHasKey('seo', $index->json('data.0'));
    }

    public function test_course_detail_embeds_seo(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing', 'status' => 'published']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs', 'status' => 'published']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);
        $course = Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'BSc Computer Science', 'course_code' => 'CS-001', 'slug' => 'bsc-computer-science',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'Overview', 'description' => 'Description',
            'status' => 'published',
        ]);
        $course->seoMeta()->create(['meta_description' => 'Study CS with us.']);

        $response = $this->getJson('/api/v1/courses/bsc-computer-science');

        $response->assertOk();
        $this->assertSame('Study CS with us.', $response->json('data.seo.meta_description'));
    }

    public function test_blog_post_detail_embeds_seo(): void
    {
        $author = User::factory()->create();
        $post = BlogPost::create([
            'title' => 'Welcome', 'slug' => 'welcome', 'body' => 'Body', 'author_id' => $author->id,
            'status' => 'published', 'published_at' => Carbon::now()->subDay(),
        ]);
        $post->seoMeta()->create(['og_title' => 'Welcome to PNKC']);

        $response = $this->getJson('/api/v1/blog/welcome');

        $response->assertOk();
        $this->assertSame('Welcome to PNKC', $response->json('data.seo.og_title'));
    }

    public function test_page_detail_embeds_seo(): void
    {
        $page = Page::create(['title' => 'About', 'slug' => 'about', 'status' => 'published', 'published_at' => Carbon::now()]);
        $page->seoMeta()->create(['canonical_url' => 'https://example.com/about']);

        $response = $this->getJson('/api/v1/pages/about');

        $response->assertOk();
        $this->assertSame('https://example.com/about', $response->json('data.seo.canonical_url'));
    }

    public function test_detail_without_any_seo_row_returns_null_not_missing(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Law', 'slug' => 'faculty-of-law', 'status' => 'published']);

        $response = $this->getJson('/api/v1/faculties/faculty-of-law');

        $response->assertOk();
        $this->assertArrayHasKey('seo', $response->json('data'));
        $this->assertNull($response->json('data.seo'));
    }
}
