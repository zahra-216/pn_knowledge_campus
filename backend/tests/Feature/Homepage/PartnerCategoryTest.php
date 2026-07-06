<?php

namespace Tests\Feature\Homepage;

use App\Models\Partner;
use App\Models\PartnerCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerCategoryTest extends TestCase
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

    public function test_marketing_can_create_and_edit_but_not_delete(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $response = $this->actingAs($marketing)->postJson('/api/v1/admin/partner-categories', ['name' => 'Academic Partner']);
        $response->assertCreated();
        $this->assertSame('academic-partner', $response->json('data.slug'));

        $id = $response->json('data.id');
        $this->actingAs($marketing)->putJson("/api/v1/admin/partner-categories/{$id}", ['name' => 'Academic Partners'])->assertOk();
        $this->actingAs($marketing)->deleteJson("/api/v1/admin/partner-categories/{$id}")->assertForbidden();
    }

    public function test_content_editor_has_no_access(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $this->actingAs($editor)->getJson('/api/v1/admin/partner-categories')->assertForbidden();
    }

    public function test_name_and_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/partner-categories', ['name' => 'Industry Partner'])->assertCreated();
        $this->actingAs($admin)->postJson('/api/v1/admin/partner-categories', ['name' => 'Industry Partner'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_deleting_a_category_leaves_its_partners_uncategorized(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = PartnerCategory::create(['name' => 'Industry Partner', 'slug' => 'industry-partner']);
        $partner = Partner::create(['name' => 'TechCorp', 'category_id' => $category->id]);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/partner-categories/{$category->id}")->assertNoContent();

        $this->assertNull($partner->fresh()->category_id);
    }

    public function test_public_index_lists_categories_in_order(): void
    {
        PartnerCategory::create(['name' => 'Second', 'slug' => 'second', 'order' => 1]);
        PartnerCategory::create(['name' => 'First', 'slug' => 'first', 'order' => 0]);

        $response = $this->getJson('/api/v1/partner-categories');

        $response->assertOk();
        $this->assertSame(['First', 'Second'], collect($response->json('data'))->pluck('name')->all());
    }
}
