<?php

namespace Tests\Feature\Pages;

use App\Models\Page;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
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

    public function test_administrator_has_full_access_including_delete(): void
    {
        $admin = $this->userWithRole('Administrator');

        $this->actingAs($admin)->getJson('/api/v1/admin/pages')->assertOk();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/pages', ['title' => 'About Us']);
        $response->assertCreated();
        $this->assertSame('about-us', $response->json('data.slug'));

        $pageId = $response->json('data.id');
        $this->actingAs($admin)->deleteJson("/api/v1/admin/pages/{$pageId}")->assertNoContent();
    }

    /** Audit fix (Medium remediation) — Pages previously had no soft-delete at all. */
    public function test_deleting_a_page_soft_deletes_it(): void
    {
        $admin = $this->userWithRole('Administrator');
        $page = Page::create(['title' => 'About', 'slug' => 'about']);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/pages/{$page->id}")->assertNoContent();

        $this->assertSoftDeleted('pages', ['id' => $page->id]);
        $this->assertDatabaseHas('pages', ['id' => $page->id]);
    }

    public function test_content_editor_can_create_and_edit_but_not_delete(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $response = $this->actingAs($editor)->postJson('/api/v1/admin/pages', ['title' => 'Vision']);
        $response->assertCreated();

        $pageId = $response->json('data.id');
        $this->actingAs($editor)->putJson("/api/v1/admin/pages/{$pageId}", ['title' => 'Our Vision'])->assertOk();
        $this->actingAs($editor)->deleteJson("/api/v1/admin/pages/{$pageId}")->assertForbidden();
    }

    public function test_marketing_has_view_only_access(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $this->actingAs($marketing)->getJson('/api/v1/admin/pages')->assertOk();
        $this->actingAs($marketing)->postJson('/api/v1/admin/pages', ['title' => 'Career'])->assertForbidden();
    }

    public function test_admissions_has_no_access_to_page_builder(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $this->actingAs($admissions)->getJson('/api/v1/admin/pages')->assertForbidden();
    }

    public function test_slug_must_be_unique(): void
    {
        Page::create(['title' => 'About', 'slug' => 'about']);
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/pages', ['title' => 'About Again', 'slug' => 'about'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_content_editor_cannot_publish(): void
    {
        $editor = $this->userWithRole('Content Editor');
        $page = Page::create(['title' => 'Draft Page', 'slug' => 'draft-page']);

        $this->actingAs($editor)->patchJson("/api/v1/admin/pages/{$page->id}/publish")->assertForbidden();
    }

    public function test_administrator_can_publish_a_draft_page(): void
    {
        $admin = $this->userWithRole('Administrator');
        $page = Page::create(['title' => 'Draft Page', 'slug' => 'draft-page']);

        $response = $this->actingAs($admin)->patchJson("/api/v1/admin/pages/{$page->id}/publish");

        $response->assertOk();
        $this->assertSame('published', $response->json('data.status'));
        $this->assertNotNull($response->json('data.published_at'));
    }

    public function test_public_endpoint_only_returns_published_pages(): void
    {
        Page::create(['title' => 'Draft', 'slug' => 'draft']);
        Page::create(['title' => 'Live', 'slug' => 'live', 'status' => 'published', 'published_at' => now()]);

        $this->getJson('/api/v1/pages/draft')->assertNotFound();
        $this->getJson('/api/v1/pages/live')->assertOk();
    }
}
