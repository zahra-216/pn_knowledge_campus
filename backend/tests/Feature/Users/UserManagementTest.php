<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Users, Roles & Permissions module (SRS FR-29) — Super Admin only,
 * per the Permission Matrix's "Users, Roles & Permissions" row.
 */
class UserManagementTest extends TestCase
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

    public function test_super_admin_can_create_a_user_with_a_role(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');

        $response = $this->actingAs($superAdmin)->postJson('/api/v1/admin/users', [
            'name' => 'New Editor',
            'email' => 'editor@example.com',
            'password' => 'A-Strong-Password9',
            'password_confirmation' => 'A-Strong-Password9',
            'role' => 'Content Editor',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.role', 'Content Editor');
        $this->assertDatabaseHas('users', ['email' => 'editor@example.com']);
    }

    public function test_administrator_has_no_access_to_user_management(): void
    {
        $administrator = $this->userWithRole('Administrator');

        $this->actingAs($administrator)->getJson('/api/v1/admin/users')->assertForbidden();
        $this->actingAs($administrator)->postJson('/api/v1/admin/users', [
            'name' => 'Nope', 'email' => 'nope@example.com', 'password' => 'Whatever-12', 'password_confirmation' => 'Whatever-12', 'role' => 'Marketing',
        ])->assertForbidden();
    }

    public function test_role_must_reference_a_real_role(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');

        $this->actingAs($superAdmin)->postJson('/api/v1/admin/users', [
            'name' => 'Bad Role',
            'email' => 'badrole@example.com',
            'password' => 'A-Strong-Password9',
            'password_confirmation' => 'A-Strong-Password9',
            'role' => 'Not A Real Role',
        ])->assertUnprocessable()->assertJsonValidationErrors(['role']);
    }

    public function test_a_user_cannot_delete_their_own_account(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');

        $this->actingAs($superAdmin)->deleteJson("/api/v1/admin/users/{$superAdmin->id}")
            ->assertUnprocessable();
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_deleting_one_of_two_super_admins_is_allowed(): void
    {
        $actor = $this->userWithRole('Super Admin');
        $other = $this->userWithRole('Super Admin');

        $this->actingAs($actor)->deleteJson("/api/v1/admin/users/{$other->id}")->assertNoContent();
    }

    public function test_the_sole_remaining_super_admin_cannot_reassign_their_own_role_away(): void
    {
        $soleSuperAdmin = $this->userWithRole('Super Admin');

        $response = $this->actingAs($soleSuperAdmin)->putJson("/api/v1/admin/users/{$soleSuperAdmin->id}", [
            'role' => 'Administrator',
        ]);

        $response->assertUnprocessable();
        $this->assertTrue($soleSuperAdmin->fresh()->hasRole('Super Admin'));
    }

    public function test_reassigning_one_of_two_super_admins_away_is_allowed(): void
    {
        $actor = $this->userWithRole('Super Admin');
        $other = $this->userWithRole('Super Admin');

        $response = $this->actingAs($actor)->putJson("/api/v1/admin/users/{$other->id}", [
            'role' => 'Administrator',
        ]);

        $response->assertOk();
        $this->assertTrue($other->fresh()->hasRole('Administrator'));
    }

    public function test_super_admin_cannot_deactivate_their_own_account(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');

        $this->actingAs($superAdmin)->putJson("/api/v1/admin/users/{$superAdmin->id}", [
            'is_active' => false,
        ])->assertUnprocessable();
    }

    public function test_email_must_be_unique(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($superAdmin)->postJson('/api/v1/admin/users', [
            'name' => 'Dup', 'email' => 'taken@example.com', 'password' => 'A-Strong-Password9', 'password_confirmation' => 'A-Strong-Password9', 'role' => 'Marketing',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    /** Audit fix (Medium remediation) — "revoke all sessions" incident-response action. */
    public function test_super_admin_can_revoke_all_of_a_users_sessions(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $staff = $this->userWithRole('Marketing');
        $staff->createToken('device-one');
        $staff->createToken('device-two');
        $this->assertSame(2, $staff->tokens()->count());

        $this->actingAs($superAdmin)->postJson("/api/v1/admin/users/{$staff->id}/revoke-tokens")->assertOk();

        $this->assertSame(0, $staff->fresh()->tokens()->count());
    }

    public function test_administrator_cannot_revoke_sessions(): void
    {
        $administrator = $this->userWithRole('Administrator');
        $staff = $this->userWithRole('Marketing');
        $staff->createToken('device-one');

        $this->actingAs($administrator)->postJson("/api/v1/admin/users/{$staff->id}/revoke-tokens")->assertForbidden();
        $this->assertSame(1, $staff->tokens()->count());
    }
}
