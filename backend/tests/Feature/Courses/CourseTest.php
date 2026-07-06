<?php

namespace Tests\Feature\Courses;

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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseTest extends TestCase
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

    private function baseAttributes(): array
    {
        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);

        return [
            'faculty_id' => $faculty->id,
            'department_id' => $department->id,
            'level_id' => $level->id,
            'mode_id' => $mode->id,
            'course_name' => 'BSc Computer Science',
            'course_code' => 'CS-001',
            'duration_value' => 3,
            'duration_unit' => 'year',
            'overview' => 'A great course.',
            'description' => 'Full description.',
        ];
    }

    public function test_content_editor_can_create_and_edit_but_not_delete(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $response = $this->actingAs($editor)->postJson('/api/v1/admin/courses', $this->baseAttributes());
        $response->assertCreated();
        $this->assertSame('bsc-computer-science', $response->json('data.slug'));

        $id = $response->json('data.id');
        $this->actingAs($editor)->putJson("/api/v1/admin/courses/{$id}", ['course_name' => 'BSc CS Updated'])->assertOk();
        $this->actingAs($editor)->deleteJson("/api/v1/admin/courses/{$id}")->assertForbidden();
    }

    public function test_admissions_can_create_and_edit_but_not_delete(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $response = $this->actingAs($admissions)->postJson('/api/v1/admin/courses', $this->baseAttributes());
        $response->assertCreated();

        $id = $response->json('data.id');
        $this->actingAs($admissions)->deleteJson("/api/v1/admin/courses/{$id}")->assertForbidden();
    }

    public function test_marketing_has_view_only_access(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $this->actingAs($marketing)->getJson('/api/v1/admin/courses')->assertOk();
        $this->actingAs($marketing)->postJson('/api/v1/admin/courses', $this->baseAttributes())->assertForbidden();
    }

    public function test_department_must_belong_to_the_submitted_faculty(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $attributes = $this->baseAttributes();

        $otherFaculty = Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business']);
        $attributes['faculty_id'] = $otherFaculty->id;

        $this->actingAs($admin)->postJson('/api/v1/admin/courses', $attributes)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['department_id']);
    }

    public function test_course_code_and_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $attributes = $this->baseAttributes();

        Course::create([...$attributes, 'slug' => 'bsc-computer-science']);

        $this->actingAs($admin)->postJson('/api/v1/admin/courses', $attributes)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['course_code', 'slug']);
    }

    public function test_discount_price_must_be_less_than_price(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $attributes = $this->baseAttributes();
        $attributes['price'] = 100;
        $attributes['discount_price'] = 150;

        $this->actingAs($admin)->postJson('/api/v1/admin/courses', $attributes)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['discount_price']);
    }

    public function test_can_create_a_course_with_inline_curriculum_and_seo(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $attributes = $this->baseAttributes();
        $attributes['curriculum'] = [
            ['title' => 'Year 1', 'children' => [
                ['title' => 'Intro to Programming', 'duration' => '12 weeks'],
            ]],
        ];
        $attributes['seo'] = ['seo_title' => 'BSc CS | PN Campus'];

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/courses', $attributes);

        $response->assertCreated();
        $this->assertCount(1, $response->json('data.curriculum'));
        $this->assertCount(1, $response->json('data.curriculum.0.children'));

        $id = $response->json('data.id');
        $this->actingAs($admin)->getJson("/api/v1/admin/seo/course/{$id}")
            ->assertOk()
            ->assertJsonPath('data.seo_title', 'BSc CS | PN Campus');
    }

    public function test_media_attaches_to_the_correct_collections(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $attributes = $this->baseAttributes();

        $featured = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('featured.jpg'),
            'alt_text' => 'Featured image',
        ])->json('data');

        $gallery = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('gallery.jpg'),
            'alt_text' => 'Gallery image',
        ])->json('data');

        $attributes['featured_image_media_id'] = $featured['id'];
        $attributes['gallery_media_ids'] = [$gallery['id']];

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/courses', $attributes);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.featured_image_url'));
        $this->assertCount(1, $response->json('data.gallery'));
        $this->assertSame('course', Media::where('collection_name', 'featured_image')->first()->model_type);

        $courseId = $response->json('data.id');
        $galleryMediaId = $response->json('data.gallery.0.id');

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/courses/{$courseId}/media/{$galleryMediaId}?collection=gallery")
            ->assertNoContent();

        $this->assertCount(0, Course::find($courseId)->getMedia('gallery'));
    }

    public function test_administrator_can_publish_a_draft_course(): void
    {
        $admin = $this->userWithRole('Administrator');
        $course = Course::create($this->baseAttributes() + ['slug' => 'bsc-computer-science']);

        $response = $this->actingAs($admin)->patchJson("/api/v1/admin/courses/{$course->id}/publish");

        $response->assertOk();
        $this->assertSame('published', $response->json('data.status'));
        $this->assertNotNull($response->json('data.published_at'));
    }

    public function test_a_scheduled_course_in_the_past_is_flipped_to_published_by_the_command(): void
    {
        $course = Course::create($this->baseAttributes() + [
            'slug' => 'bsc-computer-science',
            'status' => 'scheduled',
            'published_at' => Carbon::now()->subMinute(),
        ]);

        $this->artisan('courses:publish-scheduled');

        $this->assertSame('published', $course->fresh()->status);
    }

    public function test_a_scheduled_course_in_the_future_is_not_flipped(): void
    {
        $course = Course::create($this->baseAttributes() + [
            'slug' => 'bsc-computer-science',
            'status' => 'scheduled',
            'published_at' => Carbon::now()->addDay(),
        ]);

        $this->artisan('courses:publish-scheduled');

        $this->assertSame('scheduled', $course->fresh()->status);
    }

    public function test_administrator_can_delete_and_it_is_a_soft_delete(): void
    {
        $admin = $this->userWithRole('Administrator');
        $course = Course::create($this->baseAttributes() + ['slug' => 'bsc-computer-science']);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/courses/{$course->id}")->assertNoContent();
        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    }

    public function test_public_index_filters_by_faculty_level_mode_and_featured(): void
    {
        $attributes = $this->baseAttributes();
        Course::create([...$attributes, 'slug' => 'course-a', 'course_code' => 'A-001', 'status' => 'published', 'is_featured' => true]);
        Course::create([...$attributes, 'slug' => 'course-b', 'course_code' => 'B-001', 'status' => 'published', 'is_featured' => false]);
        Course::create([...$attributes, 'slug' => 'course-draft', 'course_code' => 'D-001', 'status' => 'draft']);

        $response = $this->getJson('/api/v1/courses?filter[faculty]=faculty-of-computing&featured=1');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('slug');
        $this->assertSame(['course-a'], $names->all());
    }

    public function test_public_detail_includes_curriculum_and_faqs(): void
    {
        $attributes = $this->baseAttributes();
        $course = Course::create([...$attributes, 'slug' => 'bsc-computer-science', 'status' => 'published']);
        $course->curriculumItems()->create(['title' => 'Module 1', 'order' => 0]);
        $course->faqs()->create(['question' => 'Q1', 'answer' => 'A1', 'order' => 0]);

        $response = $this->getJson('/api/v1/courses/bsc-computer-science');

        $response->assertOk();
        $this->assertCount(1, $response->json('data.curriculum'));
        $this->assertCount(1, $response->json('data.faqs'));
    }

    public function test_draft_course_404s_on_public_detail(): void
    {
        $course = Course::create($this->baseAttributes() + ['slug' => 'bsc-computer-science', 'status' => 'draft']);

        $this->getJson("/api/v1/courses/{$course->slug}")->assertNotFound();
    }
}
