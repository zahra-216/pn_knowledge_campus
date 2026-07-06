<?php

namespace Tests\Feature\Inquiries;

use App\Models\Inquiry;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Inquiry Management admin inbox — the piece the `inquiries` migration's
 * docblock deferred to "whichever future milestone builds the inbox".
 * SRS Permission Matrix, "Inquiry Management" row: Super Admin/
 * Administrator/Admissions = Full; Marketing = View; Content Editor =
 * no access.
 */
class AdminInquiryTest extends TestCase
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

    private function inquiry(array $overrides = []): Inquiry
    {
        return Inquiry::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Tell me more about the MBA program.',
            'status' => 'new',
            ...$overrides,
        ]);
    }

    public function test_admissions_can_list_inquiries(): void
    {
        $this->inquiry();

        $response = $this->actingAs($this->userWithRole('Admissions'))->getJson('/api/v1/admin/inquiries');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_content_editor_has_no_access(): void
    {
        $this->actingAs($this->userWithRole('Content Editor'))->getJson('/api/v1/admin/inquiries')->assertForbidden();
    }

    public function test_marketing_can_view_but_not_change_status(): void
    {
        $inquiry = $this->inquiry();
        $marketing = $this->userWithRole('Marketing');

        $this->actingAs($marketing)->getJson('/api/v1/admin/inquiries')->assertOk();
        $this->actingAs($marketing)->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/status", ['status' => 'resolved'])
            ->assertForbidden();
    }

    public function test_admissions_can_update_status(): void
    {
        $inquiry = $this->inquiry();

        $response = $this->actingAs($this->userWithRole('Admissions'))
            ->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/status", ['status' => 'resolved']);

        $response->assertOk();
        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => 'resolved']);
    }

    public function test_status_must_be_a_recognized_value(): void
    {
        $inquiry = $this->inquiry();

        $this->actingAs($this->userWithRole('Administrator'))
            ->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/status", ['status' => 'archived'])
            ->assertUnprocessable();
    }

    public function test_search_filters_by_name_email_or_message(): void
    {
        $this->inquiry(['name' => 'Alice Smith', 'email' => 'alice@example.com']);
        $this->inquiry(['name' => 'Bob Jones', 'email' => 'bob@example.com']);

        $response = $this->actingAs($this->userWithRole('Administrator'))
            ->getJson('/api/v1/admin/inquiries?search=Alice');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Alice Smith', $response->json('data.0.name'));
    }

    public function test_admissions_can_delete_spam(): void
    {
        $inquiry = $this->inquiry(['status' => 'spam']);

        $this->actingAs($this->userWithRole('Admissions'))
            ->deleteJson("/api/v1/admin/inquiries/{$inquiry->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
    }

    public function test_marketing_cannot_delete(): void
    {
        $inquiry = $this->inquiry();

        $this->actingAs($this->userWithRole('Marketing'))
            ->deleteJson("/api/v1/admin/inquiries/{$inquiry->id}")
            ->assertForbidden();
    }

    public function test_export_requires_export_permission(): void
    {
        $this->inquiry();

        $this->actingAs($this->userWithRole('Marketing'))->get('/api/v1/admin/inquiries/export')->assertForbidden();
        $this->actingAs($this->userWithRole('Admissions'))->get('/api/v1/admin/inquiries/export')->assertOk();
    }
}
