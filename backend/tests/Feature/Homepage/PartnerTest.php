<?php

namespace Tests\Feature\Homepage;

use App\Models\Partner;
use App\Models\PartnerCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerTest extends TestCase
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

        $response = $this->actingAs($marketing)->postJson('/api/v1/admin/partners', ['name' => 'Accreditor']);
        $response->assertCreated();

        $id = $response->json('data.id');
        $this->actingAs($marketing)->deleteJson("/api/v1/admin/partners/{$id}")->assertForbidden();
    }

    public function test_content_editor_has_no_access_to_partners(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $this->actingAs($editor)->getJson('/api/v1/admin/partners')->assertForbidden();
    }

    public function test_public_endpoint_only_returns_active_partners_in_order(): void
    {
        Partner::create(['name' => 'Second', 'order' => 1, 'is_active' => true]);
        Partner::create(['name' => 'Hidden', 'order' => 0, 'is_active' => false]);
        Partner::create(['name' => 'First', 'order' => 0, 'is_active' => true]);

        $response = $this->getJson('/api/v1/partners');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertSame(['First', 'Second'], $names->all());
    }

    public function test_partner_can_be_assigned_a_category(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = PartnerCategory::create(['name' => 'Industry Partner', 'slug' => 'industry-partner']);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/partners', [
            'name' => 'TechCorp',
            'category_id' => $category->id,
        ]);

        $response->assertCreated();
        $this->assertSame($category->id, $response->json('data.category.id'));
        $this->assertSame('industry-partner', $response->json('data.category.slug'));
    }

    public function test_category_id_must_reference_a_real_category(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/partners', ['name' => 'TechCorp', 'category_id' => 999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_public_endpoint_can_be_filtered_by_category(): void
    {
        $industry = PartnerCategory::create(['name' => 'Industry Partner', 'slug' => 'industry-partner']);
        $academic = PartnerCategory::create(['name' => 'Academic Partner', 'slug' => 'academic-partner']);

        Partner::create(['name' => 'TechCorp', 'category_id' => $industry->id, 'is_active' => true]);
        Partner::create(['name' => 'University Alliance', 'category_id' => $academic->id, 'is_active' => true]);

        $response = $this->getJson('/api/v1/partners?category=industry-partner');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertSame(['TechCorp'], $names->all());
    }
}
