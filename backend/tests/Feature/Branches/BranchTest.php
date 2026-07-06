<?php

namespace Tests\Feature\Branches;

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_super_admin_can_create_a_branch(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/branches', [
            'name' => 'Colombo Main Campus',
            'address' => '123 Galle Road',
            'city' => 'Colombo',
            'is_head_office' => true,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('branches', ['name' => 'Colombo Main Campus', 'city' => 'Colombo']);
    }

    public function test_administrator_cannot_manage_branches(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');

        $this->actingAs($admin)->getJson('/api/v1/admin/branches')->assertForbidden();
        $this->actingAs($admin)->postJson('/api/v1/admin/branches', [
            'name' => 'Should Fail',
            'address' => 'N/A',
            'city' => 'N/A',
        ])->assertForbidden();
    }

    public function test_public_endpoint_only_returns_active_branches(): void
    {
        Branch::create(['name' => 'Active Branch', 'address' => 'A', 'city' => 'A', 'is_active' => true]);
        Branch::create(['name' => 'Inactive Branch', 'address' => 'B', 'city' => 'B', 'is_active' => false]);

        $response = $this->getJson('/api/v1/branches');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');

        $this->assertTrue($names->contains('Active Branch'));
        $this->assertFalse($names->contains('Inactive Branch'));
    }
}
