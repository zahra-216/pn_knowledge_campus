<?php

namespace Tests\Feature\Homepage;

use App\Models\User;
use Database\Seeders\HomepageSectionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageSectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(HomepageSectionSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_marketing_can_view_and_reorder_sections(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $this->actingAs($marketing)->getJson('/api/v1/admin/homepage-sections')->assertOk();

        $response = $this->actingAs($marketing)->patchJson('/api/v1/admin/homepage-sections/reorder', [
            'sections' => [
                ['section_key' => 'hero', 'order' => 1, 'is_enabled' => false],
                ['section_key' => 'welcome', 'order' => 0, 'is_enabled' => true],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('homepage_sections', ['section_key' => 'hero', 'order' => 1, 'is_enabled' => false]);
        $this->assertDatabaseHas('homepage_sections', ['section_key' => 'welcome', 'order' => 0, 'is_enabled' => true]);
    }

    public function test_content_editor_has_no_access_to_homepage_builder(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $this->actingAs($editor)->getJson('/api/v1/admin/homepage-sections')->assertForbidden();
    }

    public function test_unknown_section_key_is_rejected(): void
    {
        $admin = $this->userWithRole('Administrator');

        $this->actingAs($admin)->patchJson('/api/v1/admin/homepage-sections/reorder', [
            'sections' => [
                ['section_key' => 'made-up-section', 'order' => 0, 'is_enabled' => true],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors(['sections.0.section_key']);
    }
}
