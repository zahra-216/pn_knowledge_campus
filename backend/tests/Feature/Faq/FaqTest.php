<?php

namespace Tests\Feature\Faq;

use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTest extends TestCase
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

    private function courseScopedFaq(): Faq
    {
        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);

        $course = Course::create([
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

        return $course->faqs()->create(['question' => 'Course-scoped question', 'answer' => 'Course-scoped answer']);
    }

    public function test_marketing_has_view_only_access(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $this->actingAs($marketing)->getJson('/api/v1/admin/faqs')->assertOk();
        $this->actingAs($marketing)->postJson('/api/v1/admin/faqs', [
            'question' => 'How do I apply?',
            'answer' => 'Visit the How to Apply page.',
        ])->assertForbidden();
    }

    public function test_admissions_can_create_and_edit_but_not_delete(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $response = $this->actingAs($admissions)->postJson('/api/v1/admin/faqs', [
            'question' => 'How do I apply?',
            'answer' => 'Visit the How to Apply page.',
        ]);
        $response->assertCreated();

        $id = $response->json('data.id');
        $this->actingAs($admissions)->putJson("/api/v1/admin/faqs/{$id}", ['question' => 'Updated question?'])->assertOk();
        $this->actingAs($admissions)->deleteJson("/api/v1/admin/faqs/{$id}")->assertForbidden();
    }

    public function test_faq_can_be_assigned_a_category(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = FaqCategory::create(['name' => 'Admissions', 'slug' => 'admissions']);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/faqs', [
            'question' => 'How do I apply?',
            'answer' => 'Visit the How to Apply page.',
            'category_id' => $category->id,
        ]);

        $response->assertCreated();
        $this->assertSame($category->id, $response->json('data.category.id'));
    }

    public function test_global_admin_endpoint_cannot_reach_a_course_scoped_faq(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $courseFaq = $this->courseScopedFaq();

        $this->actingAs($admin)->getJson("/api/v1/admin/faqs/{$courseFaq->id}")->assertNotFound();
        $this->actingAs($admin)->putJson("/api/v1/admin/faqs/{$courseFaq->id}", ['question' => 'Hijacked'])->assertNotFound();
        $this->actingAs($admin)->deleteJson("/api/v1/admin/faqs/{$courseFaq->id}")->assertNotFound();
    }

    public function test_public_endpoint_only_returns_active_global_faqs_in_order(): void
    {
        Faq::create(['question' => 'Second', 'answer' => 'A', 'order' => 1, 'is_active' => true]);
        Faq::create(['question' => 'Hidden', 'answer' => 'A', 'order' => 0, 'is_active' => false]);
        Faq::create(['question' => 'First', 'answer' => 'A', 'order' => 0, 'is_active' => true]);
        $this->courseScopedFaq();

        $response = $this->getJson('/api/v1/faqs');

        $response->assertOk();
        $questions = collect($response->json('data'))->pluck('question');
        $this->assertSame(['First', 'Second'], $questions->all());
    }

    public function test_public_endpoint_can_be_searched(): void
    {
        Faq::create(['question' => 'How do I apply?', 'answer' => 'Visit the How to Apply page.', 'is_active' => true]);
        Faq::create(['question' => 'What are the fees?', 'answer' => 'See the fee schedule.', 'is_active' => true]);

        $response = $this->getJson('/api/v1/faqs?search=apply');

        $response->assertOk();
        $questions = collect($response->json('data'))->pluck('question');
        $this->assertSame(['How do I apply?'], $questions->all());
    }

    public function test_public_endpoint_can_be_filtered_by_category(): void
    {
        $admissions = FaqCategory::create(['name' => 'Admissions', 'slug' => 'admissions']);
        $fees = FaqCategory::create(['name' => 'Fees', 'slug' => 'fees']);

        Faq::create(['question' => 'How do I apply?', 'answer' => 'A', 'category_id' => $admissions->id, 'is_active' => true]);
        Faq::create(['question' => 'What are the fees?', 'answer' => 'A', 'category_id' => $fees->id, 'is_active' => true]);

        $response = $this->getJson('/api/v1/faqs?category=admissions');

        $response->assertOk();
        $questions = collect($response->json('data'))->pluck('question');
        $this->assertSame(['How do I apply?'], $questions->all());
    }
}
