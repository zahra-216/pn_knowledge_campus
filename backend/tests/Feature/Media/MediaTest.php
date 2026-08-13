<?php

namespace Tests\Feature\Media;

use App\Models\Media;
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

    /** An unattached upload (still owned by the internal MediaLibrary singleton) deletes freely. */
    public function test_an_unattached_media_item_can_be_deleted(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $file = UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf');
        $mediaId = $this->actingAs($admin)->postJson('/api/v1/admin/media', ['file' => $file])->json('data.id');

        $this->actingAs($admin)->deleteJson("/api/v1/admin/media/{$mediaId}")->assertNoContent();
    }

    /**
     * Audit fix (Medium remediation) — deleting a Media item previously
     * never checked whether it was still in use, silently breaking
     * whatever Course/Faculty/Page it was serving as an image for.
     */
    public function test_a_media_item_attached_to_content_cannot_be_deleted(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $bannerId = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('banner.jpg'),
            'alt_text' => 'Faculty banner',
        ])->json('data.id');

        $this->actingAs($admin)->postJson('/api/v1/admin/faculties', [
            'name' => 'Faculty of Business',
            'banner_media_id' => $bannerId,
        ])->assertCreated();

        // moveKeepingCustomFields() re-parents onto a *new* Media row
        // (same convention ApplicationController's docblock documents
        // for its own move) rather than mutating the upload's original id.
        $attachedId = Media::where('collection_name', 'banner')->firstOrFail()->id;

        $response = $this->actingAs($admin)->deleteJson("/api/v1/admin/media/{$attachedId}");

        $response->assertStatus(409);
        $this->assertSame('has_dependent_records', $response->json('conflict.type'));
        $this->assertSame('faculty', $response->json('conflict.related_resource'));
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

    /** Audit fix (Medium remediation) — MediaFolderController::store()/update() had zero test coverage. */
    public function test_content_editor_can_create_a_folder(): void
    {
        $user = $this->userWithRole('Content Editor');

        $response = $this->actingAs($user)->postJson('/api/v1/admin/media-folders', ['name' => 'Brochures']);

        $response->assertCreated();
        $this->assertDatabaseHas('media_folders', ['name' => 'Brochures']);
    }

    public function test_admissions_cannot_create_a_folder(): void
    {
        $user = $this->userWithRole('Admissions');

        $this->actingAs($user)->postJson('/api/v1/admin/media-folders', ['name' => 'Brochures'])->assertForbidden();
    }

    public function test_a_folder_can_be_renamed_and_reparented(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $parent = MediaFolder::create(['name' => 'Marketing']);
        $folder = MediaFolder::create(['name' => 'Brochures']);

        $response = $this->actingAs($admin)->putJson("/api/v1/admin/media-folders/{$folder->id}", [
            'name' => 'Prospectuses',
            'parent_id' => $parent->id,
        ]);

        $response->assertOk();
        $folder->refresh();
        $this->assertSame('Prospectuses', $folder->name);
        $this->assertSame($parent->id, $folder->parent_id);
    }

    public function test_a_folder_cannot_become_its_own_parent(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $folder = MediaFolder::create(['name' => 'Brochures']);

        $this->actingAs($admin)->putJson("/api/v1/admin/media-folders/{$folder->id}", [
            'name' => 'Brochures',
            'parent_id' => $folder->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['parent_id']);
    }

    public function test_folder_name_is_required(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/media-folders', [])
            ->assertUnprocessable()->assertJsonValidationErrors(['name']);
    }
}
