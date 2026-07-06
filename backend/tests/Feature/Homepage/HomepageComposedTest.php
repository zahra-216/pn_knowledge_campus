<?php

namespace Tests\Feature\Homepage;

use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\HeroSlide;
use App\Models\HomepageSection;
use App\Models\News;
use App\Models\Partner;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\HomepageSectionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HomepageComposedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HomepageSectionSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    public function test_disabled_sections_are_excluded_and_order_is_respected(): void
    {
        HomepageSection::where('section_key', 'hero')->update(['is_enabled' => false]);
        HomepageSection::where('section_key', 'welcome')->update(['order' => -1]);

        $response = $this->getJson('/api/v1/homepage');

        $response->assertOk();
        $keys = collect($response->json('data'))->pluck('section_key');

        $this->assertNotContains('hero', $keys->all());
        $this->assertSame('welcome', $keys->first());
    }

    public function test_hero_testimonials_and_partners_sections_return_real_content(): void
    {
        HeroSlide::create(['title' => 'Slide', 'is_active' => true]);
        Testimonial::create(['name' => 'Someone', 'content' => 'x', 'is_featured' => true, 'is_active' => true]);
        Partner::create(['name' => 'A Partner', 'is_active' => true]);

        $response = $this->getJson('/api/v1/homepage');

        $sections = collect($response->json('data'))->keyBy('section_key');
        $this->assertCount(1, $sections['hero']['items']);
        $this->assertCount(1, $sections['testimonials']['items']);
        $this->assertCount(1, $sections['partners']['items']);
    }

    public function test_latest_news_and_upcoming_events_sections_return_real_content(): void
    {
        $author = User::factory()->create();
        News::create([
            'title' => 'Campus Wins Award', 'slug' => 'campus-wins-award', 'body' => 'Body',
            'author_id' => $author->id, 'status' => 'published', 'published_at' => Carbon::now()->subDay(),
        ]);
        Event::create([
            'title' => 'Open Day', 'slug' => 'open-day', 'starts_at' => Carbon::now()->addWeek(),
            'description' => 'Body', 'status' => 'published',
        ]);

        $response = $this->getJson('/api/v1/homepage');

        $sections = collect($response->json('data'))->keyBy('section_key');
        $this->assertCount(1, $sections['latest_news']['items']);
        $this->assertCount(1, $sections['upcoming_events']['items']);
    }

    public function test_faculties_and_featured_courses_sections_return_real_content(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing', 'status' => 'published']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs', 'status' => 'published']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);

        Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'BSc CS', 'course_code' => 'CS-001', 'slug' => 'bsc-cs',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'O', 'description' => 'D',
            'status' => 'published', 'is_featured' => true,
        ]);

        $response = $this->getJson('/api/v1/homepage');

        $sections = collect($response->json('data'))->keyBy('section_key');
        $this->assertCount(1, $sections['faculties']['items']);
        $this->assertCount(1, $sections['featured_courses']['items']);
    }

    public function test_welcome_and_cta_sections_pull_from_settings(): void
    {
        Setting::where('key', 'welcome_heading')->update(['value' => 'About Us']);
        Setting::where('key', 'cta_button_label')->update(['value' => 'Apply Now']);

        $response = $this->getJson('/api/v1/homepage');

        $sections = collect($response->json('data'))->keyBy('section_key');
        $this->assertSame('About Us', $sections['welcome']['content']['heading']);
        $this->assertSame('Apply Now', $sections['cta']['content']['button_label']);
    }
}
