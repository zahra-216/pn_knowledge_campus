<?php

namespace Tests\Feature\News;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsCategoryTest extends TestCase
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

        $response = $this->actingAs($editor)->postJson('/api/v1/admin/news-categories', ['name' => 'Announcements']);
        $response->assertCreated();
        $this->assertSame('announcements', $response->json('data.slug'));

        $id = $response->json('data.id');
        $this->actingAs($editor)->putJson("/api/v1/admin/news-categories/{$id}", ['name' => 'Campus Announcements'])->assertOk();
        $this->actingAs($editor)->deleteJson("/api/v1/admin/news-categories/{$id}")->assertForbidden();
    }

    public function test_marketing_can_create_and_edit_but_not_delete(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $response = $this->actingAs($marketing)->postJson('/api/v1/admin/news-categories', ['name' => 'Press Releases']);
        $response->assertCreated();

        $id = $response->json('data.id');
        $this->actingAs($marketing)->deleteJson("/api/v1/admin/news-categories/{$id}")->assertForbidden();
    }

    public function test_admissions_has_no_access(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $this->actingAs($admissions)->getJson('/api/v1/admin/news-categories')->assertForbidden();
    }

    public function test_name_and_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/news-categories', ['name' => 'Announcements'])->assertCreated();
        $this->actingAs($admin)->postJson('/api/v1/admin/news-categories', ['name' => 'Announcements'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_deleting_a_category_leaves_its_articles_uncategorized(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = NewsCategory::create(['name' => 'Announcements', 'slug' => 'announcements']);

        $article = News::create([
            'category_id' => $category->id,
            'title' => 'An Article',
            'slug' => 'an-article',
            'body' => 'Body',
            'author_id' => $admin->id,
        ]);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/news-categories/{$category->id}")->assertNoContent();

        $this->assertNull($article->fresh()->category_id);
    }

    public function test_public_index_lists_categories_in_order(): void
    {
        NewsCategory::create(['name' => 'Second', 'slug' => 'second', 'order' => 1]);
        NewsCategory::create(['name' => 'First', 'slug' => 'first', 'order' => 0]);

        $response = $this->getJson('/api/v1/news-categories');

        $response->assertOk();
        $this->assertSame(['First', 'Second'], collect($response->json('data'))->pluck('name')->all());
    }
}
