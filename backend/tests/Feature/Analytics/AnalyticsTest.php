<?php

namespace Tests\Feature\Analytics;

use App\Models\Application;
use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Inquiry;
use App\Models\PageView;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
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
        ]);
    }

    public function test_a_pageview_can_be_tracked_without_authentication(): void
    {
        $response = $this->postJson('/api/v1/analytics/pageview', ['path' => '/courses', 'visitor_id' => 'abc123']);

        $response->assertCreated();
        $this->assertDatabaseHas('page_views', ['path' => '/courses', 'visitor_id' => 'abc123']);
    }

    public function test_pageview_requires_path_and_visitor_id(): void
    {
        $this->postJson('/api/v1/analytics/pageview', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['path', 'visitor_id']);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/analytics/dashboard')->assertUnauthorized();
    }

    public function test_dashboard_returns_visitor_and_pageview_counts(): void
    {
        $user = $this->userWithRole('Marketing');
        PageView::create(['path' => '/', 'visitor_id' => 'v1']);
        PageView::create(['path' => '/', 'visitor_id' => 'v2']);
        PageView::create(['path' => '/courses', 'visitor_id' => 'v1']);

        $response = $this->actingAs($user)->getJson('/api/v1/admin/analytics/dashboard?days=7');

        $response->assertOk();
        $today = now()->toDateString();
        $data = $response->json('data');
        $todayIndex = array_search($today, $data['visitors']['labels']);
        $this->assertSame(2, $data['visitors']['data'][$todayIndex]);
        $this->assertSame(3, $data['page_views']['data'][$todayIndex]);
        $this->assertSame('/', $data['page_views']['top_pages'][0]['path']);
        $this->assertSame(2, $data['page_views']['top_pages'][0]['count']);
    }

    public function test_dashboard_returns_application_and_inquiry_stats(): void
    {
        $user = $this->userWithRole('Admissions');
        Application::create(['first_name' => 'A', 'last_name' => 'B', 'email' => 'a@example.com', 'status' => 'submitted', 'submitted_at' => now()]);
        Application::create(['first_name' => 'C', 'last_name' => 'D', 'email' => 'c@example.com', 'status' => 'draft']);
        Inquiry::create(['name' => 'Jane', 'email' => 'jane@example.com', 'message' => 'Hi', 'status' => 'new']);

        $response = $this->actingAs($user)->getJson('/api/v1/admin/analytics/dashboard?days=7');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertSame(1, $data['applications']['by_status']['submitted']);
        $this->assertArrayNotHasKey('draft', $data['applications']['by_status']);
        $this->assertSame(1, $data['inquiries']['by_status']['new']);
    }

    public function test_popular_courses_are_ranked_by_inquiries_and_applications(): void
    {
        $user = $this->userWithRole('Super Admin');
        $popularCourse = $this->course();
        Inquiry::create(['name' => 'A', 'email' => 'a@example.com', 'message' => 'Hi', 'course_id' => $popularCourse->id]);
        Inquiry::create(['name' => 'B', 'email' => 'b@example.com', 'message' => 'Hi', 'course_id' => $popularCourse->id]);
        Application::create(['first_name' => 'C', 'last_name' => 'D', 'email' => 'c@example.com', 'status' => 'submitted', 'submitted_at' => now(), 'course_id' => $popularCourse->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/admin/analytics/dashboard');

        $response->assertOk();
        $popular = $response->json('data.popular_courses');
        $this->assertSame('BSc Computer Science', $popular[0]['course_name']);
        $this->assertSame(3, $popular[0]['count']);
    }

    public function test_days_parameter_is_clamped_between_1_and_90(): void
    {
        $user = $this->userWithRole('Super Admin');

        $response = $this->actingAs($user)->getJson('/api/v1/admin/analytics/dashboard?days=9999');

        $response->assertOk();
        $this->assertCount(90, $response->json('data.visitors.labels'));
    }

    /**
     * Audit fix (High remediation) — FR-18 asks for "published content
     * counts" and "recent activity", neither of which the Dashboard
     * exposed before this.
     */
    public function test_dashboard_returns_published_content_counts_and_recent_activity(): void
    {
        $user = $this->userWithRole('Super Admin');
        $this->actingAs($user);
        $course = $this->course();
        $course->update(['status' => 'published']);

        $response = $this->getJson('/api/v1/admin/analytics/dashboard');

        $response->assertOk();
        $this->assertSame(1, $response->json('data.published_content_counts.courses'));
        $this->assertSame(0, $response->json('data.published_content_counts.news'));

        $activity = collect($response->json('data.recent_activity'));
        $courseActivity = $activity->firstWhere('type', 'course');
        $this->assertNotNull($courseActivity);
        $this->assertSame('BSc Computer Science', $courseActivity['title']);
        $this->assertSame($user->name, $courseActivity['updated_by']);
    }
}
