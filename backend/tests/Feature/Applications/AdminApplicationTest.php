<?php

namespace Tests\Feature\Applications;

use App\Models\Application;
use App\Models\User;
use App\Notifications\ApplicationStatusNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminApplicationTest extends TestCase
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

    private function submittedApplication(array $overrides = []): Application
    {
        return Application::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'status' => 'submitted',
            'submitted_at' => now(),
            ...$overrides,
        ]);
    }

    public function test_admissions_can_list_only_non_draft_applications(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $this->submittedApplication(['first_name' => 'Submitted']);
        Application::create(['first_name' => 'Still', 'last_name' => 'Draft', 'email' => 'draft@example.com', 'status' => 'draft']);

        $response = $this->actingAs($admissions)->getJson('/api/v1/admin/applications');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('first_name');
        $this->assertSame(['Submitted'], $names->all());
    }

    public function test_content_editor_has_no_access(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $this->actingAs($editor)->getJson('/api/v1/admin/applications')->assertForbidden();
    }

    public function test_marketing_has_no_access(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $this->actingAs($marketing)->getJson('/api/v1/admin/applications')->assertForbidden();
    }

    public function test_viewing_a_draft_application_returns_not_found(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $draft = Application::create(['first_name' => 'Still', 'last_name' => 'Draft', 'email' => 'draft@example.com', 'status' => 'draft']);

        $this->actingAs($admissions)->getJson("/api/v1/admin/applications/{$draft->id}")->assertNotFound();
    }

    public function test_admissions_can_mark_an_application_under_review(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $application = $this->submittedApplication();

        $response = $this->actingAs($admissions)->patchJson("/api/v1/admin/applications/{$application->id}/under-review");

        $response->assertOk();
        $this->assertSame('under_review', $response->json('data.status'));
        $this->assertSame($admissions->id, $response->json('data.reviewed_by.id'));
    }

    public function test_cannot_mark_under_review_unless_currently_submitted(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $draft = Application::create(['first_name' => 'Still', 'last_name' => 'Draft', 'email' => 'draft@example.com', 'status' => 'draft']);

        $this->actingAs($admissions)->patchJson("/api/v1/admin/applications/{$draft->id}/under-review")->assertStatus(409);
    }

    public function test_admissions_can_approve_an_application(): void
    {
        Notification::fake();
        $admissions = $this->userWithRole('Admissions');
        $application = $this->submittedApplication();

        $response = $this->actingAs($admissions)->patchJson("/api/v1/admin/applications/{$application->id}/approve", [
            'review_notes' => 'Meets all entry requirements.',
        ]);

        $response->assertOk();
        $this->assertSame('approved', $response->json('data.status'));
        Notification::assertSentOnDemand(
            ApplicationStatusNotification::class,
            fn ($notification, $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'jane@example.com'
        );
    }

    public function test_rejecting_an_application_requires_review_notes(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $application = $this->submittedApplication();

        $this->actingAs($admissions)->patchJson("/api/v1/admin/applications/{$application->id}/reject", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['review_notes']);
    }

    public function test_admissions_can_reject_an_application_with_notes(): void
    {
        Notification::fake();
        $admissions = $this->userWithRole('Admissions');
        $application = $this->submittedApplication();

        $response = $this->actingAs($admissions)->patchJson("/api/v1/admin/applications/{$application->id}/reject", [
            'review_notes' => 'Does not meet the minimum entry requirements.',
        ]);

        $response->assertOk();
        $this->assertSame('rejected', $response->json('data.status'));
    }

    public function test_cannot_review_an_application_that_already_reached_a_final_decision(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $application = $this->submittedApplication(['status' => 'approved']);

        $this->actingAs($admissions)->patchJson("/api/v1/admin/applications/{$application->id}/approve", [])
            ->assertStatus(409);
    }

    public function test_search_matches_name_email_or_application_number(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $this->submittedApplication(['first_name' => 'Alice', 'last_name' => 'Anderson', 'email' => 'alice@example.com']);
        $this->submittedApplication(['first_name' => 'Bob', 'last_name' => 'Baker', 'email' => 'bob@example.com']);

        $response = $this->actingAs($admissions)->getJson('/api/v1/admin/applications?search=Alice');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('first_name');
        $this->assertSame(['Alice'], $names->all());
    }

    public function test_can_filter_by_status(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $this->submittedApplication(['first_name' => 'Submitted']);
        $this->submittedApplication(['first_name' => 'UnderReview', 'status' => 'under_review']);

        $response = $this->actingAs($admissions)->getJson('/api/v1/admin/applications?filter[status]=under_review');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('first_name');
        $this->assertSame(['UnderReview'], $names->all());
    }

    public function test_export_returns_a_csv_download(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $this->submittedApplication();

        $response = $this->actingAs($admissions)->get('/api/v1/admin/applications/export');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Application Number', $response->streamedContent());
    }

    public function test_marketing_cannot_export(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $this->actingAs($marketing)->get('/api/v1/admin/applications/export')->assertForbidden();
    }
}
