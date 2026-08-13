<?php

namespace Tests\Feature\Downloads;

use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Download;
use App\Models\Faculty;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GatedDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('public');
        Storage::fake('local');
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    private function downloadWithFile(bool $requiresInquiry): Download
    {
        $admin = $this->admin();
        $file = UploadedFile::fake()->create('prospectus.pdf', 200, 'application/pdf');

        $upload = $this->actingAs($admin)->postJson('/api/v1/admin/media', ['file' => $file]);
        $mediaId = $upload->json('data.id');

        $create = $this->actingAs($admin)->postJson('/api/v1/admin/downloads', [
            'title' => 'Undergraduate Prospectus',
            'requires_inquiry' => $requiresInquiry,
            'media_id' => $mediaId,
        ]);
        $create->assertCreated();

        return Download::find($create->json('data.id'));
    }

    public function test_public_response_withholds_file_url_for_a_gated_download(): void
    {
        $download = $this->downloadWithFile(requiresInquiry: true);

        $response = $this->getJson('/api/v1/downloads');

        $response->assertOk();
        $data = collect($response->json('data'))->firstWhere('id', $download->id);
        $this->assertTrue($data['requires_inquiry']);
        $this->assertNull($data['file_url']);
    }

    public function test_public_response_exposes_file_url_for_an_ungated_download(): void
    {
        $download = $this->downloadWithFile(requiresInquiry: false);

        $response = $this->getJson('/api/v1/downloads');

        $data = collect($response->json('data'))->firstWhere('id', $download->id);
        $this->assertNotNull($data['file_url']);
    }

    public function test_admin_response_always_has_a_working_file_url(): void
    {
        $admin = $this->admin();
        $download = $this->downloadWithFile(requiresInquiry: true);

        $response = $this->actingAs($admin)->getJson("/api/v1/admin/downloads/{$download->id}");

        $response->assertOk();
        $this->assertStringContainsString(
            "/api/v1/admin/downloads/{$download->id}/file",
            $response->json('data.file_url')
        );
    }

    public function test_requesting_a_gated_download_requires_name_and_email_and_logs_an_inquiry(): void
    {
        Notification::fake();
        $download = $this->downloadWithFile(requiresInquiry: true);

        $this->postJson("/api/v1/downloads/{$download->id}/request", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);

        $response = $this->postJson("/api/v1/downloads/{$download->id}/request", [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertOk();
        $this->assertNotNull($response->json('data.url'));
        $this->assertDatabaseHas('inquiries', ['email' => 'jane@example.com']);
        $this->assertSame(1, $download->fresh()->download_count);
    }

    public function test_requesting_an_ungated_download_does_not_require_a_form_or_log_an_inquiry(): void
    {
        $download = $this->downloadWithFile(requiresInquiry: false);

        $response = $this->postJson("/api/v1/downloads/{$download->id}/request", []);

        $response->assertOk();
        $this->assertNotNull($response->json('data.url'));
        $this->assertDatabaseCount('inquiries', 0);
        $this->assertSame(1, $download->fresh()->download_count);
    }

    public function test_the_signed_file_url_from_requesting_a_gated_download_actually_serves_the_file(): void
    {
        $download = $this->downloadWithFile(requiresInquiry: true);

        $request = $this->postJson("/api/v1/downloads/{$download->id}/request", [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $url = $request->json('data.url');
        $path = parse_url($url, PHP_URL_PATH).'?'.parse_url($url, PHP_URL_QUERY);

        $this->get($path)->assertOk();
    }

    public function test_the_gated_file_route_rejects_an_invalid_or_missing_signature(): void
    {
        $download = $this->downloadWithFile(requiresInquiry: true);

        $this->get("/api/v1/downloads/{$download->id}/file")->assertForbidden();
    }

    public function test_a_download_can_be_attached_to_and_detached_from_a_course(): void
    {
        $admin = $this->admin();
        $download = $this->downloadWithFile(requiresInquiry: false);

        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);
        $course = Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'BSc Computer Science', 'course_code' => 'CS-001', 'slug' => 'bsc-computer-science',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'Overview', 'description' => 'Description',
        ]);

        $attach = $this->actingAs($admin)->postJson("/api/v1/admin/downloads/{$download->id}/attach", [
            'attachable_type' => 'course',
            'attachable_id' => $course->id,
        ]);
        $attach->assertCreated();
        $this->assertDatabaseHas('download_attachments', [
            'download_id' => $download->id,
            'attachable_type' => 'course',
            'attachable_id' => $course->id,
        ]);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/downloads/{$download->id}/attach/course/{$course->id}")
            ->assertNoContent();
        $this->assertDatabaseCount('download_attachments', 0);
    }

    public function test_attach_rejects_an_unrecognized_attachable_type(): void
    {
        $admin = $this->admin();
        $download = $this->downloadWithFile(requiresInquiry: false);

        $this->actingAs($admin)->postJson("/api/v1/admin/downloads/{$download->id}/attach", [
            'attachable_type' => 'faculty',
            'attachable_id' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors(['attachable_type']);
    }

    public function test_toggling_requires_inquiry_moves_the_existing_file_to_the_matching_disk(): void
    {
        $admin = $this->admin();
        $download = $this->downloadWithFile(requiresInquiry: false);
        $this->assertSame('public', $download->getFirstMedia('file')->disk);

        $this->actingAs($admin)->putJson("/api/v1/admin/downloads/{$download->id}", [
            'requires_inquiry' => true,
        ])->assertOk();

        $this->assertSame('local', $download->fresh()->getFirstMedia('file')->disk);
    }
}
