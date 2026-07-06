<?php

namespace Tests\Feature\Courses;

use App\Models\Course;
use App\Models\CourseCategory;
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

/**
 * Milestone 10 — Course Category graduated from the shared
 * CourseLookupController baseline (still covered by CourseLookupTest)
 * into its own entity: icon/image media, a parent/child tree, SEO, and
 * a dedicated reorder endpoint. This covers everything new.
 */
class CourseCategoryTest extends TestCase
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

    public function test_a_category_can_be_created_under_a_parent(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $parent = CourseCategory::create(['name' => 'Business & Management', 'slug' => 'business-management']);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/course-categories', [
            'name' => 'Accounting & Finance',
            'parent_id' => $parent->id,
        ]);

        $response->assertCreated();
        $this->assertSame($parent->id, $response->json('data.parent_id'));
    }

    public function test_a_category_cannot_be_its_own_parent(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = CourseCategory::create(['name' => 'Business & Management', 'slug' => 'business-management']);

        $this->actingAs($admin)->putJson("/api/v1/admin/course-categories/{$category->id}", [
            'parent_id' => $category->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['parent_id']);
    }

    public function test_a_category_cannot_be_moved_under_its_own_descendant(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $parent = CourseCategory::create(['name' => 'Computing & IT', 'slug' => 'computing-it']);
        $child = CourseCategory::create(['name' => 'Software Development', 'slug' => 'software-development', 'parent_id' => $parent->id]);

        $this->actingAs($admin)->putJson("/api/v1/admin/course-categories/{$parent->id}", [
            'parent_id' => $child->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['parent_id']);
    }

    public function test_show_returns_the_category_tree_with_courses_count(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $parent = CourseCategory::create(['name' => 'Computing & IT', 'slug' => 'computing-it']);
        CourseCategory::create(['name' => 'Software Development', 'slug' => 'software-development', 'parent_id' => $parent->id]);

        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing']);
        $department = Department::create(['name' => 'Software Engineering', 'slug' => 'software-engineering', 'faculty_id' => $faculty->id]);
        $level = CourseLevel::create(['name' => 'Diploma', 'slug' => 'diploma']);
        $mode = CourseMode::create(['name' => 'Full-time', 'slug' => 'full-time']);
        Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'category_id' => $parent->id, 'course_name' => 'BSc Computing', 'course_code' => 'BSC-100', 'slug' => 'bsc-computing',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'Overview', 'description' => 'Description',
        ]);

        $response = $this->actingAs($admin)->getJson("/api/v1/admin/course-categories/{$parent->id}");

        $response->assertOk();
        $this->assertSame(1, $response->json('data.courses_count'));
        $this->assertCount(1, $response->json('data.children'));
        $this->assertSame('Software Development', $response->json('data.children.0.name'));
    }

    public function test_icon_and_image_attach_via_media_move(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $iconUpload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('icon.jpg'),
            'alt_text' => 'Category icon',
        ])->json('data');

        $imageUpload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('image.jpg'),
            'alt_text' => 'Category image',
        ])->json('data');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/course-categories', [
            'name' => 'Business & Management',
            'icon_media_id' => $iconUpload['id'],
            'image_media_id' => $imageUpload['id'],
        ]);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.icon_url'));
        $this->assertNotNull($response->json('data.image_url'));
        $this->assertSame('course_category', Media::where('collection_name', 'icon')->first()->model_type);
        $this->assertSame('course_category', Media::where('collection_name', 'image')->first()->model_type);
    }

    public function test_reorder_updates_order_and_nesting_in_one_request(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $parent = CourseCategory::create(['name' => 'Business & Management', 'slug' => 'business-management', 'order' => 0]);
        $other = CourseCategory::create(['name' => 'Engineering & Technology', 'slug' => 'engineering-technology', 'order' => 1]);

        $response = $this->actingAs($admin)->patchJson('/api/v1/admin/course-categories/reorder', [
            'items' => [
                ['id' => $other->id, 'parent_id' => $parent->id, 'order' => 0],
                ['id' => $parent->id, 'parent_id' => null, 'order' => 0],
            ],
        ]);

        $response->assertOk();
        $this->assertSame($parent->id, $other->fresh()->parent_id);
    }

    public function test_deleting_a_parent_cascades_to_its_children(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $parent = CourseCategory::create(['name' => 'Business & Management', 'slug' => 'business-management']);
        $child = CourseCategory::create(['name' => 'Accounting & Finance', 'slug' => 'accounting-finance', 'parent_id' => $parent->id]);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/course-categories/{$parent->id}")->assertNoContent();

        $this->assertDatabaseMissing('course_categories', ['id' => $child->id]);
    }

    public function test_deleting_a_category_nulls_out_courses_under_it(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = CourseCategory::create(['name' => 'Business & Management', 'slug' => 'business-management']);

        $faculty = Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business']);
        $department = Department::create(['name' => 'Accounting', 'slug' => 'accounting', 'faculty_id' => $faculty->id]);
        $level = CourseLevel::create(['name' => 'Diploma', 'slug' => 'diploma']);
        $mode = CourseMode::create(['name' => 'Full-time', 'slug' => 'full-time']);
        $course = Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'category_id' => $category->id, 'course_name' => 'Diploma in Accounting', 'course_code' => 'DIP-100', 'slug' => 'diploma-in-accounting',
            'duration_value' => 1, 'duration_unit' => 'year', 'overview' => 'Overview', 'description' => 'Description',
        ]);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/course-categories/{$category->id}")->assertNoContent();

        $this->assertNull($course->fresh()->category_id);
    }

    public function test_public_index_returns_top_level_categories_with_nested_children(): void
    {
        $parent = CourseCategory::create(['name' => 'Computing & IT', 'slug' => 'computing-it']);
        CourseCategory::create(['name' => 'Software Development', 'slug' => 'software-development', 'parent_id' => $parent->id]);

        $response = $this->getJson('/api/v1/course-categories');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertCount(1, $response->json('data.0.children'));
    }

    public function test_seo_fields_can_be_set_on_a_category(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = CourseCategory::create(['name' => 'Business & Management', 'slug' => 'business-management']);

        $response = $this->actingAs($admin)->putJson("/api/v1/admin/seo/course-category/{$category->id}", [
            'seo_title' => 'Business & Management Courses',
            'meta_description' => 'Explore our Business & Management courses.',
        ]);

        $response->assertOk();
        $this->assertSame('Business & Management Courses', $response->json('data.seo_title'));
    }
}
