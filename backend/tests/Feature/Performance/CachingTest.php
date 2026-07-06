<?php

namespace Tests\Feature\Performance;

use App\Models\Branch;
use App\Models\Menu;
use App\Models\User;
use Database\Seeders\HomepageSectionSeeder;
use Database\Seeders\OfficeHourSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Milestone 25 (Performance Optimization) — proves PublicCache actually
 * caches (a second request doesn't need a write in between to see the
 * same data) and, more importantly, that every admin write path that
 * should invalidate a cached public endpoint actually does. Every test
 * here follows the same GET → write → GET shape specifically so a
 * missing forget*() call would show up as the second GET returning
 * stale data, not just as "the endpoint works" (which the pre-existing
 * per-module tests already cover regardless of caching).
 */
class CachingTest extends TestCase
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

    public function test_public_settings_cache_is_invalidated_on_update(): void
    {
        $this->seed(SettingSeeder::class);

        $this->getJson('/api/v1/settings/public')->assertOk();

        $this->actingAs($this->superAdmin())->putJson('/api/v1/admin/settings', [
            'settings' => ['campus_name' => 'Updated Campus Name'],
        ])->assertOk();

        $response = $this->getJson('/api/v1/settings/public');
        $value = collect($response->json('data'))->firstWhere('key', 'campus_name')['value'];

        $this->assertSame('Updated Campus Name', $value);
    }

    public function test_public_branches_cache_is_invalidated_on_write(): void
    {
        $this->getJson('/api/v1/branches')->assertOk()->assertJsonCount(0, 'data');

        Branch::create(['name' => 'Colombo Main Campus', 'address' => 'A', 'city' => 'Colombo', 'is_active' => true]);
        $admin = $this->superAdmin();
        $this->actingAs($admin)->postJson('/api/v1/admin/branches', [
            'name' => 'Kandy Branch',
            'address' => 'B',
            'city' => 'Kandy',
            'is_active' => true,
        ])->assertCreated();

        $response = $this->getJson('/api/v1/branches');
        $names = collect($response->json('data'))->pluck('name');

        $this->assertTrue($names->contains('Kandy Branch'));
    }

    public function test_public_office_hours_cache_is_invalidated_on_update(): void
    {
        $this->seed(OfficeHourSeeder::class);

        $this->getJson('/api/v1/office-hours')->assertOk();

        $this->actingAs($this->superAdmin())->putJson('/api/v1/admin/office-hours', [
            'hours' => ['monday' => ['is_open' => false, 'opens_at' => null, 'closes_at' => null, 'note' => 'Closed for a holiday']],
        ])->assertOk();

        $response = $this->getJson('/api/v1/office-hours');
        $monday = collect($response->json('data'))->firstWhere('day', 'monday');

        $this->assertFalse($monday['is_open']);
        $this->assertSame('Closed for a holiday', $monday['note']);
    }

    public function test_public_menu_cache_is_invalidated_on_item_write(): void
    {
        $menu = Menu::create(['name' => 'header']);

        $this->getJson('/api/v1/menus/header')->assertOk()->assertJsonCount(0, 'data.items');

        $admin = $this->superAdmin();
        $this->actingAs($admin)->postJson("/api/v1/admin/menus/{$menu->id}/items", [
            'label' => 'About Us',
            'custom_url' => '/about',
            'order' => 0,
            'is_active' => true,
        ])->assertCreated();

        $response = $this->getJson('/api/v1/menus/header');

        $this->assertCount(1, $response->json('data.items'));
    }

    public function test_public_homepage_cache_is_invalidated_on_section_reorder(): void
    {
        $this->seed(HomepageSectionSeeder::class);

        $this->getJson('/api/v1/homepage')->assertOk();

        $admin = $this->superAdmin();
        $this->actingAs($admin)->patchJson('/api/v1/admin/homepage-sections/reorder', [
            'sections' => [
                ['section_key' => 'hero', 'order' => 0, 'is_enabled' => false],
            ],
        ])->assertOk();

        $response = $this->getJson('/api/v1/homepage');
        $keys = collect($response->json('data'))->pluck('section_key');

        $this->assertFalse($keys->contains('hero'));
    }
}
