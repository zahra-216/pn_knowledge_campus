<?php

namespace Tests\Feature\OfficeHours;

use App\Models\OfficeHour;
use App\Models\User;
use Database\Seeders\OfficeHourSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeHourTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(OfficeHourSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_seeder_creates_exactly_seven_days_in_week_order(): void
    {
        $this->assertSame(7, OfficeHour::count());
        $this->assertSame(OfficeHour::DAYS, OfficeHour::orderBy('order')->pluck('day')->all());
    }

    public function test_super_admin_can_view_and_update_office_hours(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->getJson('/api/v1/admin/office-hours')->assertOk();

        $response = $this->actingAs($admin)->putJson('/api/v1/admin/office-hours', [
            'hours' => [
                'monday' => ['is_open' => true, 'opens_at' => '09:00', 'closes_at' => '18:00'],
                'saturday' => ['is_open' => true, 'opens_at' => '10:00', 'closes_at' => '13:00', 'note' => 'Half day'],
            ],
        ]);

        $response->assertOk();

        $monday = OfficeHour::where('day', 'monday')->firstOrFail();
        $this->assertSame('09:00', $monday->opens_at->format('H:i'));

        $saturday = OfficeHour::where('day', 'saturday')->firstOrFail();
        $this->assertTrue($saturday->is_open);
        $this->assertSame('Half day', $saturday->note);
    }

    public function test_administrator_cannot_update_office_hours(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');

        $this->actingAs($admin)->putJson('/api/v1/admin/office-hours', [
            'hours' => ['monday' => ['is_open' => false]],
        ])->assertForbidden();
    }

    public function test_closing_time_must_be_after_opening_time(): void
    {
        $response = $this->actingAs($this->superAdmin())->putJson('/api/v1/admin/office-hours', [
            'hours' => ['monday' => ['opens_at' => '17:00', 'closes_at' => '09:00']],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['hours.monday.closes_at']);
    }

    public function test_unrecognized_day_is_rejected(): void
    {
        $response = $this->actingAs($this->superAdmin())->putJson('/api/v1/admin/office-hours', [
            'hours' => ['someday' => ['is_open' => true]],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['hours.someday']);
    }

    public function test_public_endpoint_lists_all_days_without_auth(): void
    {
        $response = $this->getJson('/api/v1/office-hours');

        $response->assertOk();
        $this->assertCount(7, $response->json('data'));
    }
}
