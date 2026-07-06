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

class CourseCurriculumTest extends TestCase
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

    public function test_can_create_nested_curriculum_items(): void
    {
        $admin = $this->superAdmin();
        $course = $this->course();

        $module = $this->actingAs($admin)->postJson("/api/v1/admin/courses/{$course->id}/curriculum", [
            'title' => 'Year 1',
        ])->json('data');

        $lesson = $this->actingAs($admin)->postJson("/api/v1/admin/courses/{$course->id}/curriculum", [
            'title' => 'Programming Fundamentals',
            'parent_id' => $module['id'],
            'duration' => '12 weeks',
        ])->json('data');

        $this->assertSame($module['id'], $lesson['parent_id']);

        $tree = $this->actingAs($admin)->getJson("/api/v1/admin/courses/{$course->id}/curriculum")->json('data');
        $this->assertCount(1, $tree);
        $this->assertCount(1, $tree[0]['children']);
    }

    public function test_reorder_updates_order_and_nesting(): void
    {
        $admin = $this->superAdmin();
        $course = $this->course();

        $a = $course->curriculumItems()->create(['title' => 'A', 'order' => 0]);
        $b = $course->curriculumItems()->create(['title' => 'B', 'order' => 1]);

        $response = $this->actingAs($admin)->patchJson("/api/v1/admin/courses/{$course->id}/curriculum/reorder", [
            'items' => [
                ['id' => $b->id, 'parent_id' => null, 'order' => 0],
                ['id' => $a->id, 'parent_id' => $b->id, 'order' => 0],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('course_curriculum_items', ['id' => $a->id, 'parent_id' => $b->id, 'order' => 0]);
        $this->assertDatabaseHas('course_curriculum_items', ['id' => $b->id, 'parent_id' => null, 'order' => 0]);
    }

    public function test_curriculum_item_route_is_scoped_to_its_course(): void
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

        $itemB = $courseB->curriculumItems()->create(['title' => 'Module', 'order' => 0]);

        $this->actingAs($admin)->putJson("/api/v1/admin/courses/{$courseA->id}/curriculum/{$itemB->id}", [
            'title' => 'Hijacked',
        ])->assertNotFound();
    }

    public function test_content_editor_can_manage_curriculum(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('Content Editor');
        $course = $this->course();

        $this->actingAs($editor)->postJson("/api/v1/admin/courses/{$course->id}/curriculum", [
            'title' => 'Year 1',
        ])->assertCreated();
    }
}
