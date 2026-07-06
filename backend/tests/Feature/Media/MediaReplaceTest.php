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
 * Media Library hardening — "Replace files". See MediaUploadService::
 * replace()'s docblock: this is delete-old + create-new under the hood
 * (Spatie Media Library has no same-id replace primitive), so these
 * tests assert the *observable contract* — old row gone, new row in the
 * same folder/collection, response links the two ids — not literal id
 * stability.
 */
class MediaReplaceTest extends TestCase
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

    public function test_replacing_a_file_removes_the_old_row_and_creates_a_new_one_in_the_same_folder(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $folder = MediaFolder::create(['name' => 'Brochures']);

        $original = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->create('old-brochure.pdf', 100, 'application/pdf'),
            'folder_id' => $folder->id,
        ])->json('data');

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/media/{$original['id']}/replace", [
            'file' => UploadedFile::fake()->create('new-brochure.pdf', 100, 'application/pdf'),
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.replaced_media_id', $original['id']);

        $newId = $response->json('data.id');
        $this->assertNotSame($original['id'], $newId);

        $this->assertDatabaseMissing('media', ['id' => $original['id']]);
        $this->assertDatabaseHas('media', ['id' => $newId, 'folder_id' => $folder->id, 'file_name' => 'new-brochure.pdf']);
    }

    public function test_replacing_with_an_image_carries_over_existing_alt_text_when_none_is_given(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $original = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('old.jpg'),
            'alt_text' => 'Original description',
        ])->json('data');

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/media/{$original['id']}/replace", [
            'file' => UploadedFile::fake()->image('new.jpg'),
            'alt_text' => 'Original description',
        ]);

        $response->assertOk();
        $this->assertSame('Original description', $response->json('data.alt_text'));
    }

    public function test_replacing_with_an_image_still_requires_alt_text(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $original = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ])->json('data');

        $this->actingAs($admin)->postJson("/api/v1/admin/media/{$original['id']}/replace", [
            'file' => UploadedFile::fake()->image('new.jpg'),
        ])->assertUnprocessable()->assertJsonValidationErrors(['alt_text']);
    }

    public function test_content_editor_can_replace_but_admissions_cannot(): void
    {
        $editor = $this->userWithRole('Content Editor');
        $admissions = $this->userWithRole('Admissions');

        $original = $this->actingAs($editor)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ])->json('data');

        $this->actingAs($admissions)->postJson("/api/v1/admin/media/{$original['id']}/replace", [
            'file' => UploadedFile::fake()->create('doc2.pdf', 100, 'application/pdf'),
        ])->assertForbidden();

        $this->actingAs($editor)->postJson("/api/v1/admin/media/{$original['id']}/replace", [
            'file' => UploadedFile::fake()->create('doc2.pdf', 100, 'application/pdf'),
        ])->assertOk();
    }

    public function test_uploaded_image_captures_width_and_height(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('campus.jpg', 800, 600),
            'alt_text' => 'Campus photo',
        ]);

        $response->assertCreated();
        $this->assertSame(800, $response->json('data.width'));
        $this->assertSame(600, $response->json('data.height'));

        $media = Media::findOrFail($response->json('data.id'));
        $this->assertSame(800, $media->getCustomProperty('width'));
    }
}
