<?php

namespace Tests\Feature\Media;

use App\Models\MediaFolder;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Development Roadmap, Milestone 1 Testing section: "media upload
 * rejects disallowed MIME types and oversized files; folder move/delete
 * blocked (409) while non-empty."
 */
class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('public');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_content_editor_can_upload_an_image_with_alt_text(): void
    {
        $user = $this->userWithRole('Content Editor');
        $file = UploadedFile::fake()->image('campus.jpg', 800, 600);

        $response = $this->actingAs($user)->postJson('/api/v1/admin/media', [
            'file' => $file,
            'alt_text' => 'The main campus building at sunset',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('media', ['id' => $response->json('data.id'), 'alt_text' => 'The main campus building at sunset']);
        $this->assertSame('The main campus building at sunset', $response->json('data.alt_text'));
    }

    public function test_image_upload_without_alt_text_is_rejected(): void
    {
        $user = $this->userWithRole('Content Editor');
        $file = UploadedFile::fake()->image('campus.jpg');

        $this->actingAs($user)->postJson('/api/v1/admin/media', ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['alt_text']);
    }

    public function test_disallowed_mime_type_is_rejected(): void
    {
        $user = $this->userWithRole('Content Editor');
        $file = UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload');

        $this->actingAs($user)->postJson('/api/v1/admin/media', ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_oversized_file_is_rejected(): void
    {
        $user = $this->userWithRole('Content Editor');
        $maxKb = (int) (config('media-library.max_file_size') / 1024);
        $file = UploadedFile::fake()->create('brochure.pdf', $maxKb + 100, 'application/pdf');

        $this->actingAs($user)->postJson('/api/v1/admin/media', ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_admissions_role_can_view_but_not_upload_media(): void
    {
        $user = $this->userWithRole('Admissions');
        $file = UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf');

        $this->actingAs($user)->getJson('/api/v1/admin/media')->assertOk();
        $this->actingAs($user)->postJson('/api/v1/admin/media', ['file' => $file])->assertForbidden();
    }

    public function test_content_editor_cannot_delete_media(): void
    {
        $user = $this->userWithRole('Content Editor');
        $file = UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf');

        $uploadResponse = $this->actingAs($user)->postJson('/api/v1/admin/media', ['file' => $file]);
        $mediaId = $uploadResponse->json('data.id');

        $this->actingAs($user)->deleteJson("/api/v1/admin/media/{$mediaId}")->assertForbidden();
    }

    public function test_folder_cannot_be_deleted_while_it_contains_files(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $folder = MediaFolder::create(['name' => 'Brochures']);
        $file = UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf');

        $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => $file,
            'folder_id' => $folder->id,
        ])->assertCreated();

        $this->actingAs($admin)->deleteJson("/api/v1/admin/media-folders/{$folder->id}")
            ->assertStatus(409);
    }

    public function test_empty_folder_can_be_deleted(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $folder = MediaFolder::create(['name' => 'Empty Folder']);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/media-folders/{$folder->id}")
            ->assertNoContent();
    }
}
