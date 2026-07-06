<?php

namespace Tests\Feature\Downloads;

use App\Models\Download;
use App\Models\DownloadCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadCategoryTest extends TestCase
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

        $response = $this->actingAs($marketing)->postJson('/api/v1/admin/download-categories', ['name' => 'Prospectus']);
        $response->assertCreated();
        $this->assertSame('prospectus', $response->json('data.slug'));

        $id = $response->json('data.id');
        $this->actingAs($marketing)->putJson("/api/v1/admin/download-categories/{$id}", ['name' => 'Prospectuses'])->assertOk();
        $this->actingAs($marketing)->deleteJson("/api/v1/admin/download-categories/{$id}")->assertForbidden();
    }

    public function test_admissions_can_create_and_edit_but_not_delete(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $response = $this->actingAs($admissions)->postJson('/api/v1/admin/download-categories', ['name' => 'Forms']);
        $response->assertCreated();

        $id = $response->json('data.id');
        $this->actingAs($admissions)->putJson("/api/v1/admin/download-categories/{$id}", ['name' => 'Application Forms'])->assertOk();
        $this->actingAs($admissions)->deleteJson("/api/v1/admin/download-categories/{$id}")->assertForbidden();
    }

    public function test_name_and_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/download-categories', ['name' => 'Forms'])->assertCreated();
        $this->actingAs($admin)->postJson('/api/v1/admin/download-categories', ['name' => 'Forms'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_deleting_a_category_leaves_its_downloads_uncategorized(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = DownloadCategory::create(['name' => 'Forms', 'slug' => 'forms']);
        $download = Download::create(['title' => 'Application Form', 'category_id' => $category->id]);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/download-categories/{$category->id}")->assertNoContent();

        $this->assertNull($download->fresh()->category_id);
    }

    public function test_public_index_lists_categories_in_order(): void
    {
        DownloadCategory::create(['name' => 'Second', 'slug' => 'second', 'order' => 1]);
        DownloadCategory::create(['name' => 'First', 'slug' => 'first', 'order' => 0]);

        $response = $this->getJson('/api/v1/download-categories');

        $response->assertOk();
        $this->assertSame(['First', 'Second'], collect($response->json('data'))->pluck('name')->all());
    }
}
