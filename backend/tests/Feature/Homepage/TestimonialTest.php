<?php

namespace Tests\Feature\Homepage;

use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialTest extends TestCase
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

    public function test_content_editor_and_marketing_can_create_and_edit_but_not_delete(): void
    {
        foreach (['Content Editor', 'Marketing'] as $role) {
            $user = $this->userWithRole($role);

            $response = $this->actingAs($user)->postJson('/api/v1/admin/testimonials', [
                'name' => 'A Student',
                'content' => 'Great campus.',
            ]);
            $response->assertCreated();

            $id = $response->json('data.id');
            $this->actingAs($user)->deleteJson("/api/v1/admin/testimonials/{$id}")->assertForbidden();
        }
    }

    public function test_admissions_has_no_access_to_testimonials(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $this->actingAs($admissions)->getJson('/api/v1/admin/testimonials')->assertForbidden();
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/testimonials', [
            'name' => 'A Student',
            'content' => 'Great campus.',
            'rating' => 6,
        ])->assertUnprocessable()->assertJsonValidationErrors(['rating']);
    }

    public function test_course_id_must_reference_a_real_course(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/testimonials', [
            'name' => 'A Student',
            'content' => 'Great campus.',
            'course_id' => 999999,
        ])->assertUnprocessable()->assertJsonValidationErrors(['course_id']);
    }

    public function test_can_link_a_testimonial_to_a_real_course(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);
        $course = Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'BSc CS', 'course_code' => 'CS-001', 'slug' => 'bsc-cs',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'O', 'description' => 'D',
        ]);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/testimonials', [
            'name' => 'A Student',
            'content' => 'Great campus.',
            'course_id' => $course->id,
        ]);

        $response->assertCreated();
        $this->assertSame('BSc CS', $response->json('data.course.course_name'));
    }

    public function test_public_endpoint_filters_by_featured(): void
    {
        Testimonial::create(['name' => 'Featured', 'content' => 'x', 'is_featured' => true, 'is_active' => true]);
        Testimonial::create(['name' => 'Not Featured', 'content' => 'y', 'is_featured' => false, 'is_active' => true]);

        $response = $this->getJson('/api/v1/testimonials?featured=1');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertSame(['Featured'], $names->all());
    }
}
