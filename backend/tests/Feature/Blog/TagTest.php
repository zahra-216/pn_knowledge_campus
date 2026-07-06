<?php

namespace Tests\Feature\Blog;

use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
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

    public function test_content_editor_can_create_and_edit_but_not_delete(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $response = $this->actingAs($editor)->postJson('/api/v1/admin/tags', ['name' => 'Admissions']);
        $response->assertCreated();
        $this->assertSame('admissions', $response->json('data.slug'));

        $id = $response->json('data.id');
        $this->actingAs($editor)->putJson("/api/v1/admin/tags/{$id}", ['name' => 'Admissions Info'])->assertOk();
        $this->actingAs($editor)->deleteJson("/api/v1/admin/tags/{$id}")->assertForbidden();
    }

    public function test_admissions_role_has_no_access_to_tags(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $this->actingAs($admissions)->getJson('/api/v1/admin/tags')->assertForbidden();
    }

    public function test_name_and_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/tags', ['name' => 'Scholarships'])->assertCreated();
        $this->actingAs($admin)->postJson('/api/v1/admin/tags', ['name' => 'Scholarships'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_index_reports_posts_count(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $tag = Tag::create(['name' => 'Events', 'slug' => 'events']);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/tags');

        $response->assertOk();
        $this->assertSame(0, collect($response->json('data'))->firstWhere('id', $tag->id)['posts_count']);
    }
}
