<?php

namespace Tests\Feature\SocialLinks;

use App\Models\SocialLink;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Audit fix (Medium remediation) — SocialLinkController had zero test
 * coverage despite every public page's footer depending on it. Mirrors
 * BranchTest's own conventions (same Settings-module permission gate,
 * same "admin CRUD + public active-only index" shape).
 */
class SocialLinkTest extends TestCase
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

    public function test_super_admin_can_create_a_social_link(): void
    {
        $response = $this->actingAs($this->superAdmin())->postJson('/api/v1/admin/social-links', [
            'platform' => 'facebook',
            'url' => 'https://facebook.com/pnkc',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('social_links', ['platform' => 'facebook', 'url' => 'https://facebook.com/pnkc']);
    }

    public function test_administrator_cannot_manage_social_links(): void
    {
        $administrator = User::factory()->create();
        $administrator->assignRole('Administrator');

        $this->actingAs($administrator)->getJson('/api/v1/admin/social-links')->assertForbidden();
        $this->actingAs($administrator)->postJson('/api/v1/admin/social-links', [
            'platform' => 'facebook',
            'url' => 'https://facebook.com/pnkc',
        ])->assertForbidden();
    }

    public function test_url_must_be_a_valid_url(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/v1/admin/social-links', [
            'platform' => 'facebook',
            'url' => 'not-a-url',
        ])->assertUnprocessable()->assertJsonValidationErrors(['url']);
    }

    public function test_a_social_link_can_be_updated_and_deleted(): void
    {
        $admin = $this->superAdmin();
        $link = SocialLink::create(['platform' => 'facebook', 'url' => 'https://facebook.com/pnkc']);

        $this->actingAs($admin)->putJson("/api/v1/admin/social-links/{$link->id}", [
            'url' => 'https://facebook.com/pnkc-official',
        ])->assertOk();
        $this->assertSame('https://facebook.com/pnkc-official', $link->fresh()->url);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/social-links/{$link->id}")->assertNoContent();
        $this->assertDatabaseMissing('social_links', ['id' => $link->id]);
    }

    public function test_public_endpoint_only_returns_active_links_in_order(): void
    {
        SocialLink::create(['platform' => 'instagram', 'url' => 'https://instagram.com/pnkc', 'order' => 1, 'is_active' => true]);
        SocialLink::create(['platform' => 'twitter', 'url' => 'https://twitter.com/pnkc', 'order' => 0, 'is_active' => false]);
        SocialLink::create(['platform' => 'facebook', 'url' => 'https://facebook.com/pnkc', 'order' => 0, 'is_active' => true]);

        $response = $this->getJson('/api/v1/social-links');

        $response->assertOk();
        $platforms = collect($response->json('data'))->pluck('platform');
        $this->assertSame(['facebook', 'instagram'], $platforms->all());
    }
}
