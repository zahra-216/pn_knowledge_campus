<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Custom role creation (SRS FR-29: "allowing Super Admins to create
 * custom roles"). The five baseline roles seeded by RoleSeeder can have
 * their permissions edited but never be renamed or deleted (see
 * RoleController's docblock for why).
 */
class RoleManagementTest extends TestCase
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

    public function test_super_admin_can_create_a_custom_role_with_permissions(): void
    {
        $response = $this->actingAs($this->superAdmin())->postJson('/api/v1/admin/roles', [
            'name' => 'Regional Coordinator',
            'permissions' => ['courses.view', 'inquiries.view'],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'Regional Coordinator');
        $this->assertEqualsCanonicalizing(['courses.view', 'inquiries.view'], $response->json('data.permissions'));
    }

    public function test_administrator_has_no_access_to_role_management(): void
    {
        $administrator = User::factory()->create();
        $administrator->assignRole('Administrator');

        $this->actingAs($administrator)->getJson('/api/v1/admin/roles')->assertForbidden();
    }

    public function test_a_baseline_role_cannot_be_renamed(): void
    {
        $marketing = Role::where('name', 'Marketing')->firstOrFail();

        $this->actingAs($this->superAdmin())->putJson("/api/v1/admin/roles/{$marketing->id}", [
            'name' => 'Growth Team',
        ])->assertUnprocessable();

        $this->assertDatabaseHas('roles', ['id' => $marketing->id, 'name' => 'Marketing']);
    }

    public function test_a_baseline_roles_permissions_can_still_be_edited(): void
    {
        $marketing = Role::where('name', 'Marketing')->firstOrFail();

        $response = $this->actingAs($this->superAdmin())->putJson("/api/v1/admin/roles/{$marketing->id}", [
            'permissions' => ['dashboard.view', 'courses.view'],
        ]);

        $response->assertOk();
        $this->assertEqualsCanonicalizing(['dashboard.view', 'courses.view'], $response->json('data.permissions'));
    }

    public function test_a_baseline_role_cannot_be_deleted(): void
    {
        $marketing = Role::where('name', 'Marketing')->firstOrFail();

        $this->actingAs($this->superAdmin())->deleteJson("/api/v1/admin/roles/{$marketing->id}")
            ->assertUnprocessable();

        $this->assertDatabaseHas('roles', ['id' => $marketing->id]);
    }

    public function test_a_custom_role_with_no_users_can_be_deleted(): void
    {
        $superAdmin = $this->superAdmin();
        $role = Role::create(['name' => 'Temp Role', 'guard_name' => 'sanctum']);

        $this->actingAs($superAdmin)->deleteJson("/api/v1/admin/roles/{$role->id}")->assertNoContent();
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_a_role_with_users_assigned_cannot_be_deleted(): void
    {
        $superAdmin = $this->superAdmin();
        $role = Role::create(['name' => 'Temp Role', 'guard_name' => 'sanctum']);
        User::factory()->create()->assignRole($role);

        $this->actingAs($superAdmin)->deleteJson("/api/v1/admin/roles/{$role->id}")
            ->assertUnprocessable();
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_permissions_endpoint_groups_keys_by_module(): void
    {
        $response = $this->actingAs($this->superAdmin())->getJson('/api/v1/admin/permissions');

        $response->assertOk();
        $this->assertContains('dashboard.view', $response->json('data.dashboard'));
        $this->assertContains('courses.view', $response->json('data.courses'));
    }
}
