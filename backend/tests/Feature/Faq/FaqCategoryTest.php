<?php

namespace Tests\Feature\Faq;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqCategoryTest extends TestCase
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

    public function test_marketing_has_view_only_access(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $this->actingAs($marketing)->getJson('/api/v1/admin/faq-categories')->assertOk();
        $this->actingAs($marketing)->postJson('/api/v1/admin/faq-categories', ['name' => 'Admissions'])->assertForbidden();
    }

    public function test_admissions_can_create_and_edit_but_not_delete(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $response = $this->actingAs($admissions)->postJson('/api/v1/admin/faq-categories', ['name' => 'Admissions']);
        $response->assertCreated();
        $this->assertSame('admissions', $response->json('data.slug'));

        $id = $response->json('data.id');
        $this->actingAs($admissions)->putJson("/api/v1/admin/faq-categories/{$id}", ['name' => 'Admissions & Enrollment'])->assertOk();
        $this->actingAs($admissions)->deleteJson("/api/v1/admin/faq-categories/{$id}")->assertForbidden();
    }

    public function test_name_and_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/faq-categories', ['name' => 'Fees'])->assertCreated();
        $this->actingAs($admin)->postJson('/api/v1/admin/faq-categories', ['name' => 'Fees'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_deleting_a_category_leaves_its_faqs_uncategorized(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = FaqCategory::create(['name' => 'Fees', 'slug' => 'fees']);
        $faq = Faq::create(['question' => 'Q', 'answer' => 'A', 'category_id' => $category->id]);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/faq-categories/{$category->id}")->assertNoContent();

        $this->assertNull($faq->fresh()->category_id);
    }

    public function test_public_index_lists_categories_in_order(): void
    {
        FaqCategory::create(['name' => 'Second', 'slug' => 'second', 'order' => 1]);
        FaqCategory::create(['name' => 'First', 'slug' => 'first', 'order' => 0]);

        $response = $this->getJson('/api/v1/faq-categories');

        $response->assertOk();
        $this->assertSame(['First', 'Second'], collect($response->json('data'))->pluck('name')->all());
    }
}
