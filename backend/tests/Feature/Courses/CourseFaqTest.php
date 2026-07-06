<?php

namespace Tests\Feature\Courses;

use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseFaqTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    private function course(): Course
    {
        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);

        return Course::create([
            'faculty_id' => $faculty->id,
            'department_id' => $department->id,
            'level_id' => $level->id,
            'mode_id' => $mode->id,
            'course_name' => 'BSc Computer Science',
            'course_code' => 'CS-001',
            'slug' => 'bsc-computer-science',
            'duration_value' => 3,
            'duration_unit' => 'year',
            'overview' => 'Overview.',
            'description' => 'Description.',
        ]);
    }

    public function test_can_create_update_and_delete_a_course_faq(): void
    {
        $admin = $this->superAdmin();
        $course = $this->course();

        $faq = $this->actingAs($admin)->postJson("/api/v1/admin/courses/{$course->id}/faqs", [
            'question' => 'What are the fees?',
            'answer' => 'See the fee schedule.',
        ]);
        $faq->assertCreated();

        $faqId = $faq->json('data.id');
        $this->actingAs($admin)->putJson("/api/v1/admin/courses/{$course->id}/faqs/{$faqId}", [
            'question' => 'What are the tuition fees?',
        ])->assertOk();

        $this->actingAs($admin)->deleteJson("/api/v1/admin/courses/{$course->id}/faqs/{$faqId}")->assertNoContent();
    }

    public function test_faq_route_is_scoped_to_its_course(): void
    {
        $admin = $this->superAdmin();
        $courseA = $this->course();
        $courseB = Course::create([
            'faculty_id' => $courseA->faculty_id,
            'department_id' => $courseA->department_id,
            'level_id' => $courseA->level_id,
            'mode_id' => $courseA->mode_id,
            'course_name' => 'BSc Data Science',
            'course_code' => 'CS-002',
            'slug' => 'bsc-data-science',
            'duration_value' => 3,
            'duration_unit' => 'year',
            'overview' => 'Overview.',
            'description' => 'Description.',
        ]);

        $faqB = $courseB->faqs()->create(['question' => 'Q', 'answer' => 'A', 'order' => 0]);

        $this->actingAs($admin)->putJson("/api/v1/admin/courses/{$courseA->id}/faqs/{$faqB->id}", [
            'question' => 'Hijacked',
        ])->assertNotFound();
    }
}
