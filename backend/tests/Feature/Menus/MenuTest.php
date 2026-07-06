<?php

namespace Tests\Feature\Menus;

use App\Models\Menu;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
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

    public function test_administrator_has_full_access_to_menu_builder(): void
    {
        $admin = $this->userWithRole('Administrator');

        $this->actingAs($admin)->getJson('/api/v1/admin/menus')->assertOk();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/menus', ['name' => 'utility']);
        $response->assertCreated();

        $menuId = $response->json('data.id');
        $this->actingAs($admin)->deleteJson("/api/v1/admin/menus/{$menuId}")->assertNoContent();
    }

    public function test_content_editor_and_marketing_have_no_access_to_menu_builder(): void
    {
        foreach (['Content Editor', 'Marketing', 'Admissions'] as $role) {
            $user = $this->userWithRole($role);
            $this->actingAs($user)->getJson('/api/v1/admin/menus')->assertForbidden();
        }
    }

    public function test_menu_name_must_be_unique(): void
    {
        Menu::create(['name' => 'header']);
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/menus', ['name' => 'header'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }
}
