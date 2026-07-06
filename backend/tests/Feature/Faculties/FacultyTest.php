<?php

namespace Tests\Feature\Faculties;

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

class FacultyTest extends TestCase
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

    public function test_content_editor_can_create_and_edit_but_not_delete(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $response = $this->actingAs($editor)->postJson('/api/v1/admin/faculties', ['name' => 'Faculty of Arts']);
        $response->assertCreated();
        $this->assertSame('faculty-of-arts', $response->json('data.slug'));

        $id = $response->json('data.id');
        $this->actingAs($editor)->putJson("/api/v1/admin/faculties/{$id}", ['name' => 'Faculty of Fine Arts'])->assertOk();
        $this->actingAs($editor)->deleteJson("/api/v1/admin/faculties/{$id}")->assertForbidden();
    }

    public function test_marketing_and_admissions_have_view_only_access(): void
    {
        foreach (['Marketing', 'Admissions'] as $role) {
            $user = $this->userWithRole($role);

            $this->actingAs($user)->getJson('/api/v1/admin/faculties')->assertOk();
            $this->actingAs($user)->postJson('/api/v1/admin/faculties', ['name' => 'Faculty of Law'])->assertForbidden();
        }
    }

    public function test_slug_must_be_unique(): void
    {
        Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business']);
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/faculties', ['name' => 'Business Again', 'slug' => 'faculty-of-business'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_administrator_can_delete_and_it_is_a_soft_delete(): void
    {
        $admin = $this->userWithRole('Administrator');
        $faculty = Faculty::create(['name' => 'Faculty of Law', 'slug' => 'faculty-of-law']);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/faculties/{$faculty->id}")->assertNoContent();

        $this->assertSoftDeleted('faculties', ['id' => $faculty->id]);
    }

    public function test_banner_and_dean_photo_attach_via_media_move(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $bannerUpload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('banner.jpg'),
            'alt_text' => 'Faculty banner',
        ])->json('data');

        $deanUpload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('dean.jpg'),
            'alt_text' => 'Dean photo',
        ])->json('data');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/faculties', [
            'name' => 'Faculty of Business',
            'banner_media_id' => $bannerUpload['id'],
            'dean_photo_media_id' => $deanUpload['id'],
        ]);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.banner_url'));
        $this->assertNotNull($response->json('data.dean_photo_url'));
        $this->assertSame('faculty', Media::where('collection_name', 'banner')->first()->model_type);
        $this->assertSame('faculty', Media::where('collection_name', 'dean_photo')->first()->model_type);
    }

    public function test_gallery_items_can_be_attached_and_detached(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $faculty = Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business']);

        $upload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('gallery-1.jpg'),
            'alt_text' => 'Gallery image',
        ])->json('data');

        $attach = $this->actingAs($admin)->postJson("/api/v1/admin/faculties/{$faculty->id}/gallery", [
            'media_ids' => [$upload['id']],
        ]);
        $attach->assertOk();
        $this->assertCount(1, $attach->json('data.gallery'));

        $newMediaId = $attach->json('data.gallery.0.id');

        $this->actingAs($admin)->deleteJson("/api/v1/admin/faculties/{$faculty->id}/gallery/{$newMediaId}")->assertNoContent();
        $this->assertCount(0, $faculty->fresh()->getMedia('gallery'));
    }

    public function test_seo_can_be_managed_through_the_generic_seo_endpoint(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $faculty = Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business']);

        $response = $this->actingAs($admin)->putJson("/api/v1/admin/seo/faculty/{$faculty->id}", [
            'seo_title' => 'Faculty of Business | PN Knowledge Campus',
            'meta_description' => 'Learn more about our Faculty of Business.',
        ]);

        $response->assertOk();
        $this->assertSame('Faculty of Business | PN Knowledge Campus', $response->json('data.seo_title'));

        $this->actingAs($admin)->getJson("/api/v1/admin/seo/faculty/{$faculty->id}")
            ->assertOk()
            ->assertJsonPath('data.seo_title', 'Faculty of Business | PN Knowledge Campus');
    }

    public function test_public_endpoint_only_returns_published_faculties_ordered(): void
    {
        Faculty::create(['name' => 'Draft Faculty', 'slug' => 'draft-faculty', 'status' => 'draft']);
        Faculty::create(['name' => 'Second', 'slug' => 'second', 'status' => 'published', 'order' => 1]);
        Faculty::create(['name' => 'First', 'slug' => 'first', 'status' => 'published', 'order' => 0]);

        $response = $this->getJson('/api/v1/faculties');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertSame(['First', 'Second'], $names->all());
    }

    private function courseUnder(Faculty $faculty, Department $department, string $status = 'published'): Course
    {
        $level = CourseLevel::firstOrCreate(['slug' => 'undergraduate'], ['name' => 'Undergraduate']);
        $mode = CourseMode::firstOrCreate(['slug' => 'full-time'], ['name' => 'Full-Time']);

        return Course::create([
            'faculty_id' => $faculty->id,
            'department_id' => $department->id,
            'level_id' => $level->id,
            'mode_id' => $mode->id,
            'course_name' => 'BSc Finance',
            'course_code' => 'FIN-'.$faculty->id.'-'.$department->id.'-'.$status,
            'slug' => 'bsc-finance-'.$faculty->id.'-'.$department->id.'-'.$status,
            'duration_value' => 3,
            'duration_unit' => 'year',
            'overview' => 'Overview.',
            'description' => 'Description.',
            'status' => $status,
        ]);
    }

    public function test_public_detail_returns_real_published_departments_and_courses(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business', 'status' => 'published']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Finance', 'slug' => 'finance', 'status' => 'published']);
        Department::create(['faculty_id' => $faculty->id, 'name' => 'Draft Dept', 'slug' => 'draft-dept', 'status' => 'draft']);
        $this->courseUnder($faculty, $department, 'published');
        $this->courseUnder($faculty, $department, 'draft');

        $response = $this->getJson('/api/v1/faculties/faculty-of-business');

        $response->assertOk();
        $departmentNames = collect($response->json('data.departments'))->pluck('name');
        $this->assertSame(['Finance'], $departmentNames->all());
        $this->assertCount(1, $response->json('data.courses'));
    }

    public function test_admin_show_returns_all_departments_and_courses_regardless_of_status(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $faculty = Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Finance', 'slug' => 'finance', 'status' => 'published']);
        Department::create(['faculty_id' => $faculty->id, 'name' => 'Draft Dept', 'slug' => 'draft-dept', 'status' => 'draft']);
        $this->courseUnder($faculty, $department, 'published');
        $this->courseUnder($faculty, $department, 'draft');

        $response = $this->actingAs($admin)->getJson("/api/v1/admin/faculties/{$faculty->id}");

        $response->assertOk();
        $this->assertCount(2, $response->json('data.departments'));
        $this->assertCount(2, $response->json('data.courses'));
    }

    public function test_deleting_a_faculty_with_departments_is_blocked_with_a_conflict(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $faculty = Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business']);
        Department::create(['faculty_id' => $faculty->id, 'name' => 'Finance', 'slug' => 'finance']);

        $response = $this->actingAs($admin)->deleteJson("/api/v1/admin/faculties/{$faculty->id}");

        $response->assertStatus(409);
        $this->assertSame('departments', $response->json('conflict.related_resource'));
        $this->assertSame(1, $response->json('conflict.count'));
        $this->assertDatabaseHas('faculties', ['id' => $faculty->id, 'deleted_at' => null]);
    }

    public function test_deleting_a_faculty_without_departments_succeeds(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $faculty = Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business']);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/faculties/{$faculty->id}")->assertNoContent();
        $this->assertSoftDeleted('faculties', ['id' => $faculty->id]);
    }

    public function test_draft_faculty_404s_on_public_detail(): void
    {
        Faculty::create(['name' => 'Draft Faculty', 'slug' => 'draft-faculty', 'status' => 'draft']);

        $this->getJson('/api/v1/faculties/draft-faculty')->assertNotFound();
    }
}
