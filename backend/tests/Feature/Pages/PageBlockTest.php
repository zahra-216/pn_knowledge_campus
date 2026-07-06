<?php

namespace Tests\Feature\Pages;

use App\Models\Page;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBlockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_unknown_block_type_is_rejected(): void
    {
        $admin = $this->superAdmin();
        $page = Page::create(['title' => 'About', 'slug' => 'about']);

        $this->actingAs($admin)->postJson("/api/v1/admin/pages/{$page->id}/blocks", [
            'block_type' => 'carousel-of-doom',
            'data' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors(['block_type']);
    }

    public function test_hero_block_requires_a_heading(): void
    {
        $admin = $this->superAdmin();
        $page = Page::create(['title' => 'About', 'slug' => 'about']);

        $this->actingAs($admin)->postJson("/api/v1/admin/pages/{$page->id}/blocks", [
            'block_type' => 'hero',
            'data' => ['subheading' => 'Missing a heading'],
        ])->assertUnprocessable()->assertJsonValidationErrors(['data.heading']);
    }

    public function test_image_block_requires_an_existing_media_id(): void
    {
        $admin = $this->superAdmin();
        $page = Page::create(['title' => 'About', 'slug' => 'about']);

        $this->actingAs($admin)->postJson("/api/v1/admin/pages/{$page->id}/blocks", [
            'block_type' => 'image',
            'data' => ['media_id' => 999999],
        ])->assertUnprocessable()->assertJsonValidationErrors(['data.media_id']);
    }

    public function test_can_create_a_faq_block_with_multiple_items(): void
    {
        $admin = $this->superAdmin();
        $page = Page::create(['title' => 'Admissions', 'slug' => 'admissions']);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/pages/{$page->id}/blocks", [
            'block_type' => 'faq',
            'data' => ['items' => [
                ['question' => 'When do applications open?', 'answer' => 'Twice a year.'],
                ['question' => 'Is there a fee waiver?', 'answer' => 'Yes, need-based.'],
            ]],
        ]);

        $response->assertCreated();
        $this->assertCount(2, $response->json('data.data.items'));
    }

    public function test_reorder_updates_block_order(): void
    {
        $admin = $this->superAdmin();
        $page = Page::create(['title' => 'About', 'slug' => 'about']);

        $a = $page->blocks()->create(['block_type' => 'text', 'data' => ['body' => 'A'], 'order' => 0]);
        $b = $page->blocks()->create(['block_type' => 'text', 'data' => ['body' => 'B'], 'order' => 1]);

        $this->actingAs($admin)->patchJson("/api/v1/admin/pages/{$page->id}/blocks/reorder", [
            'items' => [
                ['id' => $b->id, 'order' => 0],
                ['id' => $a->id, 'order' => 1],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('page_blocks', ['id' => $b->id, 'order' => 0]);
        $this->assertDatabaseHas('page_blocks', ['id' => $a->id, 'order' => 1]);
    }

    public function test_block_route_is_scoped_to_its_page(): void
    {
        $admin = $this->superAdmin();
        $pageA = Page::create(['title' => 'A', 'slug' => 'a']);
        $pageB = Page::create(['title' => 'B', 'slug' => 'b']);

        $blockB = $pageB->blocks()->create(['block_type' => 'text', 'data' => ['body' => 'B'], 'order' => 0]);

        $this->actingAs($admin)->putJson("/api/v1/admin/pages/{$pageA->id}/blocks/{$blockB->id}", [
            'data' => ['body' => 'Hijacked'],
        ])->assertNotFound();
    }

    public function test_public_endpoint_only_returns_active_blocks_in_order(): void
    {
        $page = Page::create(['title' => 'Live', 'slug' => 'live', 'status' => 'published', 'published_at' => now()]);

        $page->blocks()->create(['block_type' => 'text', 'data' => ['body' => 'Second'], 'order' => 1, 'is_active' => true]);
        $page->blocks()->create(['block_type' => 'text', 'data' => ['body' => 'Hidden'], 'order' => 0, 'is_active' => false]);
        $page->blocks()->create(['block_type' => 'text', 'data' => ['body' => 'First'], 'order' => 0, 'is_active' => true]);

        $response = $this->getJson('/api/v1/pages/live');

        $response->assertOk();
        $bodies = collect($response->json('data.blocks'))->pluck('data.body');
        $this->assertSame(['First', 'Second'], $bodies->all());
    }
}
