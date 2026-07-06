<?php

namespace Tests\Feature\Applications;

use App\Models\Application;
use App\Models\User;
use App\Notifications\ApplicationReceivedNotification;
use App\Notifications\NewApplicationNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        // Application documents live on the 'local' (private) disk, not
        // 'public' — see Application::registerMediaCollections()'s
        // docblock. Faking it too keeps uploaded test files out of the
        // real storage/app/private directory.
        Storage::fake('local');
    }

    private function startDraft(array $overrides = []): array
    {
        $response = $this->postJson('/api/v1/applications', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            ...$overrides,
        ]);
        $response->assertCreated();

        return $response->json('data');
    }

    public function test_a_visitor_can_start_a_draft_application(): void
    {
        $data = $this->startDraft();

        $this->assertSame('draft', $data['status']);
        $this->assertNotEmpty($data['application_number']);
        $this->assertStringStartsWith('PNKC-', $data['application_number']);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->postJson('/api/v1/applications', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
    }

    public function test_a_visitor_can_look_up_their_draft_with_matching_email(): void
    {
        $data = $this->startDraft();

        $response = $this->postJson('/api/v1/applications/lookup', [
            'application_number' => $data['application_number'],
            'email' => 'jane@example.com',
        ]);

        $response->assertOk();
        $this->assertSame($data['application_number'], $response->json('data.application_number'));
    }

    public function test_lookup_fails_with_the_wrong_email(): void
    {
        $data = $this->startDraft();

        $this->postJson('/api/v1/applications/lookup', [
            'application_number' => $data['application_number'],
            'email' => 'someone-else@example.com',
        ])->assertNotFound();
    }

    public function test_lookup_fails_for_a_nonexistent_application_number(): void
    {
        $this->postJson('/api/v1/applications/lookup', [
            'application_number' => 'PNKC-2026-999999',
            'email' => 'jane@example.com',
        ])->assertNotFound();
    }

    public function test_a_visitor_can_update_their_draft(): void
    {
        $data = $this->startDraft();

        $response = $this->putJson("/api/v1/applications/{$data['application_number']}", [
            'email' => 'jane@example.com',
            'phone' => '+1234567890',
            'nationality' => 'Sri Lankan',
        ]);

        $response->assertOk();
        $this->assertSame('+1234567890', $response->json('data.phone'));
    }

    public function test_a_visitor_can_change_their_email_via_new_email(): void
    {
        $data = $this->startDraft();

        $this->putJson("/api/v1/applications/{$data['application_number']}", [
            'email' => 'jane@example.com',
            'new_email' => 'jane.doe@example.com',
        ])->assertOk();

        // The OLD email no longer owns it; the new one does.
        $this->postJson('/api/v1/applications/lookup', [
            'application_number' => $data['application_number'],
            'email' => 'jane@example.com',
        ])->assertNotFound();

        $this->postJson('/api/v1/applications/lookup', [
            'application_number' => $data['application_number'],
            'email' => 'jane.doe@example.com',
        ])->assertOk();
    }

    public function test_update_fails_with_the_wrong_email(): void
    {
        $data = $this->startDraft();

        $this->putJson("/api/v1/applications/{$data['application_number']}", [
            'email' => 'wrong@example.com',
            'phone' => '+1234567890',
        ])->assertNotFound();
    }

    public function test_a_visitor_can_upload_and_delete_a_document(): void
    {
        $data = $this->startDraft();
        $file = UploadedFile::fake()->create('transcript.pdf', 200, 'application/pdf');

        $upload = $this->postJson("/api/v1/applications/{$data['application_number']}/documents", [
            'email' => 'jane@example.com',
            'label' => 'Transcript',
            'file' => $file,
        ]);
        $upload->assertCreated();
        $this->assertSame('Transcript', $upload->json('data.label'));
        $documentId = $upload->json('data.id');

        $this->deleteJson("/api/v1/applications/{$data['application_number']}/documents/{$documentId}", [
            'email' => 'jane@example.com',
        ])->assertNoContent();
    }

    /**
     * Audit fix (Critical remediation) — the document's returned URL
     * must point at the authenticated download route, never a raw
     * public-disk storage URL (that was the entire vulnerability: a
     * passport/transcript reachable by anyone who obtained the link).
     */
    public function test_document_url_points_to_the_authenticated_download_route_not_a_public_storage_path(): void
    {
        $data = $this->startDraft();
        $file = UploadedFile::fake()->create('transcript.pdf', 200, 'application/pdf');

        $upload = $this->postJson("/api/v1/applications/{$data['application_number']}/documents", [
            'email' => 'jane@example.com',
            'label' => 'Transcript',
            'file' => $file,
        ]);

        $url = $upload->json('data.url');
        $this->assertStringContainsString("/api/v1/applications/{$data['application_number']}/documents/", $url);
        $this->assertStringEndsWith('/download', $url);
        $this->assertStringNotContainsString('/storage/', $url);
    }

    public function test_the_owner_can_download_their_document_via_the_email_ownership_check(): void
    {
        $data = $this->startDraft();
        $file = UploadedFile::fake()->create('transcript.pdf', 200, 'application/pdf');
        $documentId = $this->postJson("/api/v1/applications/{$data['application_number']}/documents", [
            'email' => 'jane@example.com',
            'label' => 'Transcript',
            'file' => $file,
        ])->json('data.id');

        $this->get("/api/v1/applications/{$data['application_number']}/documents/{$documentId}/download?email=jane@example.com")
            ->assertOk();
    }

    public function test_downloading_a_document_with_the_wrong_email_is_rejected(): void
    {
        $data = $this->startDraft();
        $file = UploadedFile::fake()->create('transcript.pdf', 200, 'application/pdf');
        $documentId = $this->postJson("/api/v1/applications/{$data['application_number']}/documents", [
            'email' => 'jane@example.com',
            'label' => 'Transcript',
            'file' => $file,
        ])->json('data.id');

        $this->get("/api/v1/applications/{$data['application_number']}/documents/{$documentId}/download?email=stranger@example.com")
            ->assertNotFound();
    }

    public function test_downloading_a_document_with_no_email_and_no_staff_session_is_rejected(): void
    {
        $data = $this->startDraft();
        $file = UploadedFile::fake()->create('transcript.pdf', 200, 'application/pdf');
        $documentId = $this->postJson("/api/v1/applications/{$data['application_number']}/documents", [
            'email' => 'jane@example.com',
            'label' => 'Transcript',
            'file' => $file,
        ])->json('data.id');

        $this->getJson("/api/v1/applications/{$data['application_number']}/documents/{$documentId}/download")
            ->assertUnprocessable();
    }

    public function test_staff_with_applications_view_can_download_without_an_email(): void
    {
        $this->seed(RoleSeeder::class);
        $staff = User::factory()->create();
        $staff->assignRole('Admissions');

        $data = $this->startDraft();
        $file = UploadedFile::fake()->create('transcript.pdf', 200, 'application/pdf');
        $documentId = $this->postJson("/api/v1/applications/{$data['application_number']}/documents", [
            'email' => 'jane@example.com',
            'label' => 'Transcript',
            'file' => $file,
        ])->json('data.id');

        $this->actingAs($staff)
            ->get("/api/v1/applications/{$data['application_number']}/documents/{$documentId}/download")
            ->assertOk();
    }

    public function test_staff_without_applications_permission_still_needs_the_email_check(): void
    {
        $this->seed(RoleSeeder::class);
        $staff = User::factory()->create();
        $staff->assignRole('Marketing');

        $data = $this->startDraft();
        $file = UploadedFile::fake()->create('transcript.pdf', 200, 'application/pdf');
        $documentId = $this->postJson("/api/v1/applications/{$data['application_number']}/documents", [
            'email' => 'jane@example.com',
            'label' => 'Transcript',
            'file' => $file,
        ])->json('data.id');

        $this->actingAs($staff)
            ->getJson("/api/v1/applications/{$data['application_number']}/documents/{$documentId}/download")
            ->assertUnprocessable();
    }

    public function test_document_upload_rejects_disallowed_file_types(): void
    {
        $data = $this->startDraft();
        $file = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');

        $this->postJson("/api/v1/applications/{$data['application_number']}/documents", [
            'email' => 'jane@example.com',
            'label' => 'Transcript',
            'file' => $file,
        ])->assertUnprocessable()->assertJsonValidationErrors(['file']);
    }

    public function test_deleting_a_document_is_scoped_to_its_own_application(): void
    {
        $applicationA = $this->startDraft(['email' => 'a@example.com']);
        $applicationB = $this->startDraft(['email' => 'b@example.com']);

        $file = UploadedFile::fake()->create('id.pdf', 100, 'application/pdf');
        $upload = $this->postJson("/api/v1/applications/{$applicationB['application_number']}/documents", [
            'email' => 'b@example.com',
            'label' => 'ID',
            'file' => $file,
        ]);
        $documentId = $upload->json('data.id');

        // Application A (owned by a different visitor) tries to delete Application B's document.
        $this->deleteJson("/api/v1/applications/{$applicationA['application_number']}/documents/{$documentId}", [
            'email' => 'a@example.com',
        ])->assertNotFound();
    }

    public function test_submit_requires_at_least_one_document(): void
    {
        $data = $this->startDraft();

        $this->postJson("/api/v1/applications/{$data['application_number']}/submit", [
            'email' => 'jane@example.com',
        ])->assertUnprocessable();
    }

    public function test_a_visitor_can_submit_after_uploading_a_document(): void
    {
        Notification::fake();
        $this->seed(RoleSeeder::class);
        $admissionsStaff = User::factory()->create();
        $admissionsStaff->assignRole('Admissions');

        $data = $this->startDraft();
        $file = UploadedFile::fake()->create('transcript.pdf', 200, 'application/pdf');
        $this->postJson("/api/v1/applications/{$data['application_number']}/documents", [
            'email' => 'jane@example.com',
            'label' => 'Transcript',
            'file' => $file,
        ])->assertCreated();

        $response = $this->postJson("/api/v1/applications/{$data['application_number']}/submit", [
            'email' => 'jane@example.com',
        ]);

        $response->assertOk();
        $this->assertSame('submitted', $response->json('data.status'));
        $this->assertNotNull($response->json('data.submitted_at'));

        Notification::assertSentOnDemand(
            ApplicationReceivedNotification::class,
            fn ($notification, $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'jane@example.com'
        );
        Notification::assertSentTo($admissionsStaff, NewApplicationNotification::class);
    }

    public function test_cannot_edit_or_resubmit_after_submission(): void
    {
        $data = $this->startDraft();
        $file = UploadedFile::fake()->create('transcript.pdf', 200, 'application/pdf');
        $this->postJson("/api/v1/applications/{$data['application_number']}/documents", [
            'email' => 'jane@example.com', 'label' => 'Transcript', 'file' => $file,
        ]);
        $this->postJson("/api/v1/applications/{$data['application_number']}/submit", ['email' => 'jane@example.com'])->assertOk();

        $this->putJson("/api/v1/applications/{$data['application_number']}", [
            'email' => 'jane@example.com',
            'phone' => '+1111111111',
        ])->assertStatus(409);

        $this->postJson("/api/v1/applications/{$data['application_number']}/submit", [
            'email' => 'jane@example.com',
        ])->assertStatus(409);
    }

    public function test_application_number_is_never_a_plain_incrementing_id_in_the_url(): void
    {
        $data = $this->startDraft();

        $this->assertDatabaseHas('applications', ['application_number' => $data['application_number']]);
        $this->assertArrayNotHasKey('id', $data);
    }
}
