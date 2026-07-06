<?php

namespace Tests\Feature\Homepage;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_marketing_can_view_and_edit_homepage_content_without_settings_access(): void
    {
        $marketing = $this->userWithRole('Marketing');

        // Confirms this really is a separate gate, not a side door into
        // the Super-Admin-only Settings module.
        $this->actingAs($marketing)->getJson('/api/v1/admin/settings')->assertForbidden();

        $this->actingAs($marketing)->getJson('/api/v1/admin/homepage-content')->assertOk();

        $response = $this->actingAs($marketing)->putJson('/api/v1/admin/homepage-content', [
            'content' => ['welcome_heading' => 'About Our Campus'],
        ]);

        $response->assertOk();
        $this->assertSame('About Our Campus', $response->json('data.welcome_heading'));
    }

    public function test_content_editor_has_no_access(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $this->actingAs($editor)->getJson('/api/v1/admin/homepage-content')->assertForbidden();
    }

    public function test_settings_keys_outside_the_homepage_group_are_rejected(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->putJson('/api/v1/admin/homepage-content', [
            'content' => ['smtp_password' => 'hunter2'],
        ])->assertUnprocessable()->assertJsonValidationErrors(['content.smtp_password']);
    }

    public function test_json_typed_keys_round_trip_as_arrays(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $items = json_encode([['icon' => 'star', 'title' => 'Expert Faculty', 'text' => 'Learn from the best.']]);

        $response = $this->actingAs($admin)->putJson('/api/v1/admin/homepage-content', [
            'content' => ['why_choose_us_items' => $items],
        ]);

        $response->assertOk();
        $this->assertSame('Expert Faculty', $response->json('data.why_choose_us_items.0.title'));
    }
}
