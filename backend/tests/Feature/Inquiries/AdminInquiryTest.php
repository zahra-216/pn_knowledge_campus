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

    /** Audit fix (Medium remediation) — export() previously ignored the same `search` param index() applies. */
    public function test_export_respects_the_search_filter(): void
    {
        $this->inquiry(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
        $this->inquiry(['name' => 'Amit Shah', 'email' => 'amit@example.com']);

        $response = $this->actingAs($this->userWithRole('Admissions'))->get('/api/v1/admin/inquiries/export?search=Jane');

        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Jane', $csv);
        $this->assertStringNotContainsString('Amit', $csv);
    }

    public function test_admissions_can_assign_an_inquiry_to_a_staff_member(): void
    {
        $inquiry = $this->inquiry();
        $staff = $this->userWithRole('Admissions');

        $response = $this->actingAs($staff)->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/assign", [
            'assigned_to' => $staff->id,
        ]);

        $response->assertOk();
        $this->assertSame($staff->id, $response->json('data.assigned_to.id'));
        $this->assertSame($staff->name, $response->json('data.assigned_to.name'));
    }

    public function test_an_inquiry_can_be_unassigned(): void
    {
        $inquiry = $this->inquiry();
        $staff = $this->userWithRole('Admissions');
        $inquiry->update(['assigned_to' => $staff->id]);

        $response = $this->actingAs($staff)->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/assign", [
            'assigned_to' => null,
        ]);

        $response->assertOk();
        $this->assertNull($response->json('data.assigned_to'));
    }

    public function test_marketing_cannot_assign_an_inquiry(): void
    {
        $inquiry = $this->inquiry();
        $staff = $this->userWithRole('Admissions');

        $this->actingAs($this->userWithRole('Marketing'))
            ->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/assign", ['assigned_to' => $staff->id])
            ->assertForbidden();
    }

    public function test_list_can_be_filtered_by_assigned_to(): void
    {
        $staff = $this->userWithRole('Admissions');
        $assigned = $this->inquiry(['name' => 'Assigned Visitor', 'email' => 'assigned@example.com']);
        $assigned->update(['assigned_to' => $staff->id]);
        $this->inquiry(['name' => 'Unassigned Visitor', 'email' => 'unassigned@example.com']);

        $response = $this->actingAs($staff)->getJson("/api/v1/admin/inquiries?filter[assigned_to]={$staff->id}");

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertSame(['Assigned Visitor'], $names->all());
    }

    public function test_staff_can_add_a_follow_up_note(): void
    {
        $inquiry = $this->inquiry();
        $staff = $this->userWithRole('Admissions');

        $response = $this->actingAs($staff)->postJson("/api/v1/admin/inquiries/{$inquiry->id}/notes", [
            'body' => 'Called the applicant, awaiting a callback.',
        ]);

        $response->assertCreated();
        $notes = $response->json('data.notes');
        $this->assertCount(1, $notes);
        $this->assertSame('Called the applicant, awaiting a callback.', $notes[0]['body']);
        $this->assertSame($staff->name, $notes[0]['author']['name']);
    }

    public function test_a_note_requires_a_body(): void
    {
        $inquiry = $this->inquiry();
        $staff = $this->userWithRole('Admissions');

        $this->actingAs($staff)->postJson("/api/v1/admin/inquiries/{$inquiry->id}/notes", ['body' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['body']);
    }

    public function test_marketing_cannot_add_a_note(): void
    {
        $inquiry = $this->inquiry();

        $this->actingAs($this->userWithRole('Marketing'))
            ->postJson("/api/v1/admin/inquiries/{$inquiry->id}/notes", ['body' => 'Should not be allowed.'])
            ->assertForbidden();
    }

    public function test_assignable_staff_only_lists_users_with_inquiries_manage(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $marketing = $this->userWithRole('Marketing');

        $response = $this->actingAs($admissions)->getJson('/api/v1/admin/inquiries/assignable-staff');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains($admissions->name));
        $this->assertFalse($names->contains($marketing->name));
    }

    public function test_marketing_cannot_list_assignable_staff(): void
    {
        $this->actingAs($this->userWithRole('Marketing'))
            ->getJson('/api/v1/admin/inquiries/assignable-staff')
            ->assertForbidden();
    }

    public function test_deleting_an_inquiry_cascades_its_notes(): void
    {
        $inquiry = $this->inquiry();
        $staff = $this->userWithRole('Admissions');
        $inquiry->notes()->create(['user_id' => $staff->id, 'body' => 'A note.']);

        $this->actingAs($staff)->deleteJson("/api/v1/admin/inquiries/{$inquiry->id}")->assertNoContent();

        $this->assertDatabaseMissing('inquiry_notes', ['inquiry_id' => $inquiry->id]);
    }
}
