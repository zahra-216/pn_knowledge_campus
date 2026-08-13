<?php

namespace Tests\Feature\Departments;

use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('public');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function faculty(): Faculty
    {
        return Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business']);
    }

    public function test_content_editor_can_create_and_edit_but_not_delete(): void
    {
        $editor = $this->userWithRole('Content Editor');
        $faculty = $this->faculty();

        $response = $this->actingAs($editor)->postJson('/api/v1/admin/departments', [
            'faculty_id' => $faculty->id,
            'name' => 'Department of Finance',
        ]);
        $response->assertCreated();
        $this->assertSame('department-of-finance', $response->json('data.slug'));
        $this->assertSame($faculty->id, $response->json('data.faculty.id'));

        $id = $response->json('data.id');
        $this->actingAs($editor)->putJson("/api/v1/admin/departments/{$id}", ['name' => 'Dept. of Finance'])->assertOk();
        $this->actingAs($editor)->deleteJson("/api/v1/admin/departments/{$id}")->assertForbidden();
    }

    public function test_marketing_and_admissions_have_view_only_access(): void
    {
        $faculty = $this->faculty();

        foreach (['Marketing', 'Admissions'] as $role) {
            $user = $this->userWithRole($role);

            $this->actingAs($user)->getJson('/api/v1/admin/departments')->assertOk();
            $this->actingAs($user)->postJson('/api/v1/admin/departments', [
                'faculty_id' => $faculty->id,
                'name' => 'Department of Law',
            ])->assertForbidden();
        }
    }

    public function test_faculty_id_is_required_and_must_exist(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/departments', ['name' => 'No Faculty'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['faculty_id']);

        $this->actingAs($admin)->postJson('/api/v1/admin/departments', ['name' => 'Bad Faculty', 'faculty_id' => 999999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['faculty_id']);
    }

    public function test_slug_must_be_unique(): void
    {
        $faculty = $this->faculty();
        Department::create(['faculty_id' => $faculty->id, 'name' => 'Department of Finance', 'slug' => 'department-of-finance']);
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/departments', [
            'faculty_id' => $faculty->id,
            'name' => 'Finance Again',
            'slug' => 'department-of-finance',
        ])->assertUnprocessable()->assertJsonValidationErrors(['slug']);
    }

    public function test_banner_attaches_via_media_move(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $faculty = $this->faculty();

        $upload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('banner.jpg'),
            'alt_text' => 'Department banner',
        ])->json('data');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/departments', [
            'faculty_id' => $faculty->id,
            'name' => 'Department of Finance',
            'banner_media_id' => $upload['id'],
        ]);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.banner_url'));
        $this->assertSame('department', Media::where('collection_name', 'banner')->first()->model_type);
    }

    public function test_seo_can_be_managed_through_the_generic_seo_endpoint(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $faculty = $this->faculty();
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Department of Finance', 'slug' => 'department-of-finance']);

        $response = $this->actingAs($admin)->putJson("/api/v1/admin/seo/department/{$department->id}", [
            'seo_title' => 'Department of Finance | PNK Global Campus',
        ]);

        $response->assertOk();
        $this->assertSame('Department of Finance | PNK Global Campus', $response->json('data.seo_title'));
    }

    public function test_public_endpoint_filters_by_faculty_and_only_returns_published(): void
    {
        $faculty = $this->faculty();
        $otherFaculty = Faculty::create(['name' => 'Faculty of Engineering', 'slug' => 'faculty-of-engineering']);

        Department::create(['faculty_id' => $faculty->id, 'name' => 'Finance', 'slug' => 'finance', 'status' => 'published']);
        Department::create(['faculty_id' => $faculty->id, 'name' => 'Draft Dept', 'slug' => 'draft-dept', 'status' => 'draft']);
        Department::create(['faculty_id' => $otherFaculty->id, 'name' => 'Civil', 'slug' => 'civil', 'status' => 'published']);

        $response = $this->getJson('/api/v1/departments?faculty=faculty-of-business');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertSame(['Finance'], $names->all());
    }

    public function test_public_detail_returns_only_published_courses(): void
    {
        $faculty = $this->faculty();
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Finance', 'slug' => 'finance', 'status' => 'published']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);

        Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'BSc Finance', 'course_code' => 'FIN-001', 'slug' => 'bsc-finance',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'O', 'description' => 'D', 'status' => 'published',
        ]);
        Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'Draft Course', 'course_code' => 'FIN-002', 'slug' => 'draft-course',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'O', 'description' => 'D', 'status' => 'draft',
        ]);

        $response = $this->getJson('/api/v1/departments/finance');

        $response->assertOk();
        $courseNames = collect($response->json('data.courses'))->pluck('course_name');
        $this->assertSame(['BSc Finance'], $courseNames->all());
    }

    public function test_deleting_a_department_with_courses_is_blocked_with_a_conflict(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $faculty = $this->faculty();
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Finance', 'slug' => 'finance']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);

        Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'BSc Finance', 'course_code' => 'FIN-001', 'slug' => 'bsc-finance',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'O', 'description' => 'D',
        ]);

        $response = $this->actingAs($admin)->deleteJson("/api/v1/admin/departments/{$department->id}");

        $response->assertStatus(409);
        $this->assertSame('courses', $response->json('conflict.related_resource'));
        $this->assertDatabaseHas('departments', ['id' => $department->id, 'deleted_at' => null]);
    }
}
