<?php

namespace Tests\Feature\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogCategoryTest extends TestCase
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

        $response = $this->actingAs($editor)->postJson('/api/v1/admin/blog-categories', ['name' => 'Campus Life']);
        $response->assertCreated();
        $this->assertSame('campus-life', $response->json('data.slug'));

        $id = $response->json('data.id');
        $this->actingAs($editor)->putJson("/api/v1/admin/blog-categories/{$id}", ['name' => 'Campus Updates'])->assertOk();
        $this->actingAs($editor)->deleteJson("/api/v1/admin/blog-categories/{$id}")->assertForbidden();
    }

    public function test_marketing_can_create_and_edit_but_not_delete(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $response = $this->actingAs($marketing)->postJson('/api/v1/admin/blog-categories', ['name' => 'Student Stories']);
        $response->assertCreated();

        $id = $response->json('data.id');
        $this->actingAs($marketing)->deleteJson("/api/v1/admin/blog-categories/{$id}")->assertForbidden();
    }

    public function test_admissions_has_no_access(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $this->actingAs($admissions)->getJson('/api/v1/admin/blog-categories')->assertForbidden();
    }

    public function test_name_and_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/blog-categories', ['name' => 'Campus Life'])->assertCreated();
        $this->actingAs($admin)->postJson('/api/v1/admin/blog-categories', ['name' => 'Campus Life'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_deleting_a_category_leaves_its_posts_uncategorized(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = BlogCategory::create(['name' => 'Campus Life', 'slug' => 'campus-life']);

        $post = BlogPost::create([
            'category_id' => $category->id,
            'title' => 'A Post',
            'slug' => 'a-post',
            'body' => 'Body',
            'author_id' => $admin->id,
        ]);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/blog-categories/{$category->id}")->assertNoContent();

        $this->assertNull($post->fresh()->category_id);
    }

    public function test_public_index_lists_categories_in_order(): void
    {
        BlogCategory::create(['name' => 'Second', 'slug' => 'second', 'order' => 1]);
        BlogCategory::create(['name' => 'First', 'slug' => 'first', 'order' => 0]);

        $response = $this->getJson('/api/v1/blog-categories');

        $response->assertOk();
        $this->assertSame(['First', 'Second'], collect($response->json('data'))->pluck('name')->all());
    }
}
