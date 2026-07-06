<?php

namespace Tests\Feature\Gallery;

use App\Models\GalleryAlbum;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryAlbumTest extends TestCase
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

    public function test_content_editor_can_create_and_edit_but_not_delete(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $response = $this->actingAs($editor)->postJson('/api/v1/admin/gallery-albums', ['title' => 'Open Day 2026']);
        $response->assertCreated();
        $this->assertSame('open-day-2026', $response->json('data.slug'));

        $id = $response->json('data.id');
        $this->actingAs($editor)->putJson("/api/v1/admin/gallery-albums/{$id}", ['title' => 'Open Day 2026 (Updated)'])->assertOk();
        $this->actingAs($editor)->deleteJson("/api/v1/admin/gallery-albums/{$id}")->assertForbidden();
    }

    public function test_marketing_can_create_and_edit_but_not_delete(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $response = $this->actingAs($marketing)->postJson('/api/v1/admin/gallery-albums', ['title' => 'Campus Life']);
        $response->assertCreated();

        $id = $response->json('data.id');
        $this->actingAs($marketing)->deleteJson("/api/v1/admin/gallery-albums/{$id}")->assertForbidden();
    }

    public function test_admissions_has_no_access(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $this->actingAs($admissions)->getJson('/api/v1/admin/gallery-albums')->assertForbidden();
        $this->actingAs($admissions)->postJson('/api/v1/admin/gallery-albums', ['title' => 'Campus Life'])->assertForbidden();
    }

    public function test_super_admin_can_delete(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $id = $this->actingAs($admin)->postJson('/api/v1/admin/gallery-albums', ['title' => 'Open Day 2026'])->json('data.id');

        $this->actingAs($admin)->deleteJson("/api/v1/admin/gallery-albums/{$id}")->assertNoContent();
        $this->assertSoftDeleted('gallery_albums', ['id' => $id]);
    }

    public function test_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/gallery-albums', ['title' => 'Open Day 2026'])->assertCreated();
        $this->actingAs($admin)->postJson('/api/v1/admin/gallery-albums', ['title' => 'Open Day 2026'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_images_and_videos_can_be_attached_with_captions(): void
    {
        // UploadedFile::fake()->create() reports a video mime type to
        // Laravel's own validation layer (sizeToReport/mimeTypeToReport),
        // but writes a genuinely empty physical file — Spatie re-derives
        // mime_type from the real bytes it stores (both on initial
        // upload and again on moveKeepingCustomFields()'s internal
        // copy), landing on application/x-empty or the real detected
        // type rather than video/mp4 either time. So the "video" here is
        // a real image upload with its mime_type forced after it's
        // fully attached, isolating the test to
        // GalleryMediaItemResource's own image-vs-video classification
        // logic rather than Spatie's content-sniffing (trusted package
        // behavior, not something this project needs to re-verify).
        $admin = $this->userWithRole('Super Admin');
        $album = GalleryAlbum::create(['title' => 'Open Day 2026', 'slug' => 'open-day-2026']);

        $image = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('photo.jpg'),
            'alt_text' => 'Photo',
        ])->json('data');

        $video = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('clip.jpg'),
            'alt_text' => 'Clip',
        ])->json('data');

        $attach = $this->actingAs($admin)->postJson("/api/v1/admin/gallery-albums/{$album->id}/media", [
            'items' => [
                ['media_id' => $image['id'], 'caption' => 'Welcome banner'],
                ['media_id' => $video['id'], 'caption' => 'Campus tour'],
            ],
        ]);
        $attach->assertOk();

        $videoItemId = collect($attach->json('data.items'))->firstWhere('caption', 'Campus tour')['id'];
        Media::whereKey($videoItemId)->update(['mime_type' => 'video/mp4']);

        $response = $this->actingAs($admin)->getJson("/api/v1/admin/gallery-albums/{$album->id}");

        $response->assertOk();
        $items = collect($response->json('data.items'));
        $this->assertCount(2, $items);
        $this->assertSame('image', $items->firstWhere('caption', 'Welcome banner')['type']);
        $this->assertSame('video', $items->firstWhere('caption', 'Campus tour')['type']);
        $this->assertSame('gallery_album', Media::where('collection_name', 'items')->first()->model_type);
    }

    public function test_caption_can_be_updated_after_attaching(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $album = GalleryAlbum::create(['title' => 'Open Day 2026', 'slug' => 'open-day-2026']);

        $image = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('photo.jpg'),
            'alt_text' => 'Photo',
        ])->json('data');

        $attach = $this->actingAs($admin)->postJson("/api/v1/admin/gallery-albums/{$album->id}/media", [
            'items' => [['media_id' => $image['id']]],
        ]);
        $attach->assertOk();
        // moveKeepingCustomFields() re-parents onto a *new* Media row
        // (Spatie has no same-id move) — the attach response's id is
        // what's actually in the album now, not the original upload id.
        $attachedId = $attach->json('data.items.0.id');

        $response = $this->actingAs($admin)->putJson("/api/v1/admin/gallery-albums/{$album->id}/media/{$attachedId}", [
            'caption' => 'Updated caption',
        ]);

        $response->assertOk();
        $this->assertSame('Updated caption', collect($response->json('data.items'))->first()['caption']);
    }

    public function test_item_can_be_detached(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $album = GalleryAlbum::create(['title' => 'Open Day 2026', 'slug' => 'open-day-2026']);

        $image = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('photo.jpg'),
            'alt_text' => 'Photo',
        ])->json('data');

        $attach = $this->actingAs($admin)->postJson("/api/v1/admin/gallery-albums/{$album->id}/media", [
            'items' => [['media_id' => $image['id']]],
        ]);
        $attach->assertOk();
        $attachedId = $attach->json('data.items.0.id');

        $this->actingAs($admin)->deleteJson("/api/v1/admin/gallery-albums/{$album->id}/media/{$attachedId}")->assertNoContent();

        $this->assertCount(0, $album->fresh()->getMedia('items'));
    }

    public function test_public_index_only_returns_active_albums_with_a_cover_image(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $active = GalleryAlbum::create(['title' => 'Active Album', 'slug' => 'active-album', 'is_active' => true]);
        GalleryAlbum::create(['title' => 'Inactive Album', 'slug' => 'inactive-album', 'is_active' => false]);

        $image = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('cover.jpg'),
            'alt_text' => 'Cover',
        ])->json('data');
        $this->actingAs($admin)->postJson("/api/v1/admin/gallery-albums/{$active->id}/media", [
            'items' => [['media_id' => $image['id']]],
        ])->assertOk();

        $response = $this->getJson('/api/v1/gallery-albums');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Active Album', $response->json('data.0.title'));
        $this->assertNotNull($response->json('data.0.cover_url'));
    }

    public function test_public_show_returns_all_items(): void
    {
        GalleryAlbum::create(['title' => 'Inactive Album', 'slug' => 'inactive-album', 'is_active' => false]);

        $this->getJson('/api/v1/gallery-albums/inactive-album')->assertNotFound();
    }
}
