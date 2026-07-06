<?php

namespace Tests\Feature\Settings;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Development Roadmap, Milestone 1 Testing section: "settings bulk
 * update, public settings endpoint never exposes is_public=false keys
 * (SMTP password stays hidden)."
 */
class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_super_admin_can_view_all_settings(): void
    {
        $response = $this->actingAs($this->superAdmin())->getJson('/api/v1/admin/settings');

        $response->assertOk();
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_administrator_cannot_access_settings(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrator');

        $this->actingAs($user)->getJson('/api/v1/admin/settings')->assertForbidden();
        $this->actingAs($user)->putJson('/api/v1/admin/settings', [
            'settings' => ['campus_name' => 'Should not save'],
        ])->assertForbidden();
    }

    public function test_bulk_update_only_writes_known_keys(): void
    {
        $response = $this->actingAs($this->superAdmin())->putJson('/api/v1/admin/settings', [
            'settings' => [
                'campus_name' => 'PN Knowledge Campus',
                'not_a_real_key' => 'nope',
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['settings.not_a_real_key']);

        $this->assertDatabaseMissing('settings', ['key' => 'campus_name', 'value' => 'PN Knowledge Campus']);
    }

    public function test_bulk_update_writes_value_only_and_leaves_group_and_visibility_untouched(): void
    {
        $this->actingAs($this->superAdmin())->putJson('/api/v1/admin/settings', [
            'settings' => ['smtp_host' => 'smtp.example.com'],
        ])->assertOk();

        $row = Setting::where('key', 'smtp_host')->firstOrFail();
        $this->assertSame('smtp.example.com', $row->value);
        $this->assertSame('smtp', $row->group);
        $this->assertFalse($row->is_public);
    }

    public function test_public_settings_endpoint_never_exposes_private_keys(): void
    {
        $this->actingAs($this->superAdmin())->putJson('/api/v1/admin/settings', [
            'settings' => [
                'smtp_password' => 'super-secret',
                'campus_name' => 'PN Knowledge Campus',
            ],
        ])->assertOk();

        $response = $this->getJson('/api/v1/settings/public');

        $response->assertOk();
        $keys = collect($response->json('data'))->pluck('key');

        $this->assertTrue($keys->contains('campus_name'));
        $this->assertFalse($keys->contains('smtp_password'));
    }
}
